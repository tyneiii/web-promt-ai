<?php

function getPrompt($account_id, $searchString, $tag_id, $conn) {
    $account_id = (int)$account_id;
    $search = trim($searchString ?? '');
    $tag_id = (int)$tag_id;

    // Tạo SQL cơ bản
    $prompt_sql = "
        SELECT 
            p.prompt_id,
            p.account_id,
            COALESCE(p.title, p.short_description, '') AS description,
            p.love_count,
            p.comment_count,
            p.save_count,
            a.username,
            a.avatar,
            CASE WHEN l.account_id IS NULL THEN 0 ELSE 1 END AS is_loved
        FROM prompt p
        LEFT JOIN account a ON a.account_id = p.account_id
        LEFT JOIN love l ON l.prompt_id = p.prompt_id AND l.account_id = ?
    ";

    // Nếu lọc theo tag
    if ($tag_id > 0) {
        $prompt_sql .= " 
            JOIN prompttag pt ON pt.prompt_id = p.prompt_id 
            AND pt.tag_id = ?
        ";
    }

    $prompt_sql .= " WHERE p.status = 'public' ";

    // Nếu có search
    if (!empty($search)) {
        $prompt_sql .= " 
            AND (p.title LIKE ? 
            OR p.short_description LIKE ?
            OR a.username LIKE ?)
        ";
    }

    $prompt_sql .= " 
        ORDER BY p.create_at DESC
        LIMIT 50
    ";

    // ===== BUILD BIND PARAM =====
    $types = "i";     // 1: account_id cho love check
    $params = [$account_id];

    if ($tag_id > 0) {
        $types .= "i";
        $params[] = $tag_id;
    }

    if (!empty($search)) {
        $types .= "sss";
        $like = "%$search%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    // Chuẩn bị statement
    $prompt_stmt = $conn->prepare($prompt_sql);
    if (!$prompt_stmt) {
        error_log("Prepare failed: ".$conn->error);
        return [];
    }

    // bind_param động
    $prompt_stmt->bind_param($types, ...$params);
    $prompt_stmt->execute();
    $prompt_result = $prompt_stmt->get_result();

    // ===== Build kết quả =====
    $prompts = [];
    while ($row = $prompt_result->fetch_assoc()) {
        $prompt_id = $row['prompt_id'];

        $prompts[$prompt_id] = [
            'prompt_id' => $prompt_id,
            'username' => $row['username'],
            'avatar' => $row['avatar'] ?? 'default-avatar.png',
            'description' => $row['description'],
            'love_count' => (int)$row['love_count'],
            'comment_count' => (int)$row['comment_count'],
            'save_count' => (int)$row['save_count'],
            'is_loved' => $row['is_loved'] == 1,
            'details' => [],
            'tags' => []
        ];

        // ===== LẤY TAG =====
        $tag_sql = "
            SELECT t.tag_id, t.tag_name 
            FROM prompttag pt
            JOIN tag t ON t.tag_id = pt.tag_id
            WHERE pt.prompt_id = ?
        ";

        $tag_stmt = $conn->prepare($tag_sql);
        $tag_stmt->bind_param("i", $prompt_id);
        $tag_stmt->execute();
        $tags_res = $tag_stmt->get_result();

        while ($tag_row = $tags_res->fetch_assoc()) {
            $prompts[$prompt_id]['tags'][] = [
                'id' => $tag_row['tag_id'],
                'name' => $tag_row['tag_name']
            ];

        }

        // ===== LẤY DETAILS =====
        $detail_sql = "
            SELECT content 
            FROM promptdetail 
            WHERE prompt_id = ?
            ORDER BY component_order ASC
        ";
        $detail_stmt = $conn->prepare($detail_sql);
        $detail_stmt->bind_param("i", $prompt_id);
        $detail_stmt->execute();
        $dres = $detail_stmt->get_result();

        while ($d = $dres->fetch_assoc()) {
            $prompts[$prompt_id]['details'][] = $d['content'];
        }
    }

    return array_values($prompts);
}


function lovePrompt($account_id, $prompt_id, $conn) {
    // Validate inputs
    $account_id = (int)$account_id;
    $prompt_id = (int)$prompt_id;
    if ($account_id <= 0 || $prompt_id <= 0) {
        return "Dữ liệu không hợp lệ";
    }
    
    // Check exists with prepared
    $checkSql = "SELECT love_id FROM love WHERE prompt_id = ? AND account_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        return "Lỗi chuẩn bị query check: " . $conn->error;
    }
    $checkStmt->bind_param("ii", $prompt_id, $account_id);
    if (!$checkStmt->execute()) {
        return "Lỗi execute check: " . $checkStmt->error;
    }
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Đã thả tim → bỏ tim
        $deleteSql = "DELETE FROM love WHERE prompt_id = ? AND account_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        if (!$deleteStmt) {
            return "Lỗi chuẩn bị query delete: " . $conn->error;
        }
        $deleteStmt->bind_param("ii", $prompt_id, $account_id);  // FIXED: Add bind_param here
        if (!$deleteStmt->execute()) {
            return "Lỗi execute delete: " . $deleteStmt->error;
        }
        $deleteStmt->close();
        
        // Decrement count
        $updateSql = "UPDATE prompt SET love_count = GREATEST(love_count - 1, 0) WHERE prompt_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt) {
            $updateStmt->bind_param("i", $prompt_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
        return "Bạn đã bỏ tim bài viết";
    } else {
        // Chưa thả tim → thêm tim
        $love_at = date('Y-m-d');  // DATE format to match DB schema
        $insertSql = "INSERT INTO love (prompt_id, account_id, status, love_at) VALUES (?, ?, 'OPEN', ?)";
        $insertStmt = $conn->prepare($insertSql);
        if (!$insertStmt) {
            return "Lỗi chuẩn bị query insert: " . $conn->error;
        }
        $insertStmt->bind_param("iis", $prompt_id, $account_id, $love_at);
        if (!$insertStmt->execute()) {
            return "Lỗi execute insert: " . $insertStmt->error;
        }
        $insertStmt->close();
        
        // Increment count
        $updateSql = "UPDATE prompt SET love_count = love_count + 1 WHERE prompt_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt) {
            $updateStmt->bind_param("i", $prompt_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
        return "Bạn đã tim bài viết";
    }
}
function savePrompt($account_id, $prompt_id, $conn) {
    // Validate inputs
    $account_id = (int)$account_id;
    $prompt_id = (int)$prompt_id;
    if ($account_id <= 0 || $prompt_id <= 0) {
        return "Dữ liệu không hợp lệ";
    }
    
    // Check exists with prepared
    $checkSql = "SELECT save_id FROM save WHERE prompt_id = ? AND account_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        return "Lỗi chuẩn bị query check: " . $conn->error;
    }
    $checkStmt->bind_param("ii", $prompt_id, $account_id);
    if (!$checkStmt->execute()) {
        return "Lỗi execute check: " . $checkStmt->error;
    }
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Đã save → bỏ save
        $deleteSql = "DELETE FROM save WHERE prompt_id = ? AND account_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        if (!$deleteStmt) {
            return "Lỗi chuẩn bị query delete: " . $conn->error;
        }
        $deleteStmt->bind_param("ii", $prompt_id, $account_id);
        if (!$deleteStmt->execute()) {
            return "Lỗi execute delete: " . $deleteStmt->error;
        }
        $deleteStmt->close();
        
        // Decrement count
        $updateSql = "UPDATE prompt SET save_count = GREATEST(save_count - 1, 0) WHERE prompt_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt) {
            $updateStmt->bind_param("i", $prompt_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
        return "Bạn đã bỏ lưu bài viết";
    } else {
        // Chưa save → thêm save
        $insertSql = "INSERT INTO save (prompt_id, account_id) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        if (!$insertStmt) {
            return "Lỗi chuẩn bị query insert: " . $conn->error;
        }
        $insertStmt->bind_param("ii", $prompt_id, $account_id);
        if (!$insertStmt->execute()) {
            return "Lỗi execute insert: " . $insertStmt->error;
        }
        $insertStmt->close();
        
        // Increment count
        $updateSql = "UPDATE prompt SET save_count = save_count + 1 WHERE prompt_id = ?";
        $updateStmt = $conn->prepare($updateSql);
        if ($updateStmt) {
            $updateStmt->bind_param("i", $prompt_id);
            $updateStmt->execute();
            $updateStmt->close();
        }
        return "Bạn đã lưu bài viết thành công";
    }
}
function commentPrompt($account_id, $prompt_id, $content, $conn) {

}

function getAwaitingPrompts($conn, $search) {
    $sql = "SELECT prompt_id, title, short_description, status
            FROM prompt
            WHERE (prompt_id = ? OR title LIKE ? OR short_description LIKE ?) AND status = 'waiting'";
    $stmt = $conn->prepare($sql);
    $like_search = "%" . $search . "%";
    $stmt->bind_param("iss", $search, $like_search, $like_search);
    $stmt->execute();
    return $stmt->get_result();
}

function getReportedPrompts($conn, $search) {
    $sql = "SELECT p.prompt_id, p.title, p.status, r.reason
            FROM prompt p
            JOIN report r ON p.prompt_id = r.prompt_id
            WHERE p.prompt_id = ? OR r.reason LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_search = "%" . $search . "%";
    $stmt->bind_param("is", $search, $like_search);
    $stmt->execute();
    return $stmt->get_result();
}

function getAlldPrompts($conn, $search, $status, $search_columns)
{
    $search = trim($search ?? '');
    $status = trim($status ?? ''); 
    $allowed_columns = ['prompt_id', 'title', 'short_description'];
    if (empty($search_columns)) {
        $search_columns = $allowed_columns;
    }
    $column_conditions = [];
    $bind_types = "";
    $params = [];
    $like_search = "%" . $search . "%";
    $status_like = empty($status) ? "%" : $status;
    if (!empty($search)) {
        foreach ($search_columns as $column) {
            if (in_array($column, $allowed_columns)) {
                $column_conditions[] = "$column LIKE ?";
                $bind_types .= "s";
                $params[] = $like_search;
            }
        }
    } else {
        $column_conditions[] = "1=1"; 
    }
    
    $search_where = implode(" OR ", $column_conditions);
    $sql = "SELECT prompt_id, title, short_description, status
            FROM prompt
            WHERE ($search_where) 
            AND status LIKE ?";
    $bind_types .= "s";
    $params[] = $status_like;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    $stmt->bind_param($bind_types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}

function changestatus($conn, $prompt_id, $status){
    $sql = "UPDATE prompt SET status = ? WHERE prompt_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("SQL Prepare Failed (changestatus): " . $conn->error . " | Query: " . $sql);
        return [
            'success' => false,
            'message' => "Lỗi hệ thống (Mã 501). Không thể chuẩn bị truy vấn."
        ];
    }
    $stmt->bind_param("si", $status, $prompt_id);
    $execute_success = $stmt->execute();
    $rows_affected = $stmt->affected_rows;
    $stmt->close();
    if (!$execute_success) {
        error_log("SQL Execute Failed (changestatus): " . $stmt->error . " | Prompt ID: " . $prompt_id);
        return [
            'success' => false,
            'message' => "Có lỗi nghiêm trọng xảy ra trong quá trình cập nhật trạng thái."
        ];
    }
    if ($rows_affected > 0) {
        return [
            'success' => true,
            'message' => "Cập nhật thành công bài đăng có ID={$prompt_id}."
        ];
    } else {
        return [
            'success' => true, 
            'message' => "Thao tác trên bài đăng có ID={$prompt_id}, không có thay đổi nào."
        ];
    }
}
?>