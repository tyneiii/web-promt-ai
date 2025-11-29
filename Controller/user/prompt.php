<?php

function getPrompts($account_id, $searchString, $tag_id, $conn) {
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
        $love_at = date('Y-m-d');
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
        
        // Tạo notification cho owner (nếu không phải chính mình)
        $ownerSql = "SELECT account_id FROM prompt WHERE prompt_id = ?";
        $ownerStmt = $conn->prepare($ownerSql);
        $ownerStmt->bind_param("i", $prompt_id);
        $ownerStmt->execute();
        $owner = $ownerStmt->get_result()->fetch_assoc();
        if ($owner && $owner['account_id'] != $account_id) {
            // Lấy username sender để message đẹp
            $senderSql = "SELECT username FROM account WHERE account_id = ?";
            $senderStmt = $conn->prepare($senderSql);
            $senderStmt->bind_param("i", $account_id);
            $senderStmt->execute();
            $sender = $senderStmt->get_result()->fetch_assoc();
            $sender_name = $sender['username'] ?? 'Người dùng';
            createNotification($owner['account_id'], $account_id, $prompt_id, $sender_name . ' đã thích bài viết của bạn', $conn);
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
        // Tạo notification (optional)
        $ownerSql = "SELECT account_id FROM prompt WHERE prompt_id = ?";
        $ownerStmt = $conn->prepare($ownerSql);
        $ownerStmt->bind_param("i", $prompt_id);
        $ownerStmt->execute();
        $owner = $ownerStmt->get_result()->fetch_assoc();
        if ($owner && $owner['account_id'] != $account_id) {
            $senderSql = "SELECT username FROM account WHERE account_id = ?";
            $senderStmt = $conn->prepare($senderSql);
            $senderStmt->bind_param("i", $account_id);
            $senderStmt->execute();
            $sender = $senderStmt->get_result()->fetch_assoc();
            $sender_name = $sender['username'] ?? 'Người dùng';
            createNotification($owner['account_id'], $account_id, $prompt_id, $sender_name . ' đã lưu bài viết của bạn', $conn);
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
// Thêm vào prompt.php
function getUserComments($account_id, $conn) {
    $account_id = (int)$account_id;
    if ($account_id <= 0) {
        return [];
    }

    $sql = "
        SELECT 
            c.comment_id, c.prompt_id, c.content, c.created_at,
            p.title, p.short_description AS prompt_desc,
            -- Người bình luận (commenter: user hiện tại)
            a_c.username AS commenter_username, a_c.avatar AS commenter_avatar,
            -- Người đăng bài (author)
            a_p.username AS author_username, a_p.avatar AS author_avatar
        FROM comment c
        JOIN prompt p ON c.prompt_id = p.prompt_id
        -- Join commenter (user hiện tại)
        JOIN account a_c ON c.account_id = a_c.account_id
        -- Join author (người đăng bài)
        JOIN account a_p ON p.account_id = a_p.account_id
        WHERE c.account_id = ?
        ORDER BY c.created_at DESC
        LIMIT 50
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'comment_id' => $row['comment_id'],
            'prompt_id' => $row['prompt_id'],
            'content' => $row['content'],
            'created_at' => $row['created_at'],
            'title' => $row['title'] ?? $row['prompt_desc'] ?? 'Bài viết không có tiêu đề',
            // Người bình luận
            'username' => $row['commenter_username'],  // Giữ tên cũ cho tương thích
            'avatar' => $row['commenter_avatar'] ?? 'default-avatar.png',
            // Người đăng bài (mới thêm)
            'author_username' => $row['author_username'],
            'author_avatar' => $row['author_avatar'] ?? 'default-avatar.png'
        ];
    }
    return $comments;
}

function getAlldPrompts($conn, $search, $status, $search_columns, $rows_per_page, $offset)
{
    $search = trim($search ?? '');
    $status = trim($status ?? '');
    $allowed_columns = ['prompt_id', 'title', 'short_description'];
    $rows_per_page = max(1, (int) $rows_per_page);
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
    $status_bind_type = "s";
    $status_param = $status_like;
    $count_sql = "SELECT COUNT(*) AS total 
                  FROM prompt
                  WHERE ($search_where) 
                  AND status LIKE ?";

    $count_stmt = $conn->prepare($count_sql);
    if (!$count_stmt) {
        error_log("COUNT Prepare failed: " . $conn->error);
        return ['total' => 0, 'data' => false];
    }
    $count_params = array_merge($params, [$status_param]);
    $count_bind_types = $bind_types . $status_bind_type;
    $count_stmt->bind_param($count_bind_types, ...$count_params);
    $count_stmt->execute();
    $total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
    $select_sql = "SELECT prompt_id, title, short_description, status
                   FROM prompt
                   WHERE ($search_where) 
                   AND status LIKE ?
                   LIMIT ? OFFSET ?";
    $select_bind_types = $count_bind_types . "ii"; 
    $select_params = array_merge($params, [$status_param, $rows_per_page, $offset]);
    $select_stmt = $conn->prepare($select_sql);
    if (!$select_stmt) {
        error_log("SELECT Prepare failed: " . $conn->error);
        return ['total' => $total_rows, 'data' => false];
    }
    $select_stmt->bind_param($select_bind_types, ...$select_params);
    $select_stmt->execute();
    $prompts_data = $select_stmt->get_result();
    $select_stmt->close();
    return [
        'total' => $total_rows,
        'prompts' => $prompts_data
    ];
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
function getHotPrompts($conn, $limit = 5) {
    $sql = "
        SELECT 
            p.prompt_id,
            COALESCE(p.title, p.short_description, '') AS description,
            p.love_count
        FROM prompt p
        WHERE p.status = 'public'
        ORDER BY p.love_count DESC, p.create_at DESC
        LIMIT ?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for hot prompts: " . $conn->error);
        return [];
    }
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $hot_prompts = [];
    while ($row = $result->fetch_assoc()) {
        $hot_prompts[] = [
            'prompt_id' => $row['prompt_id'],
            'description' => $row['description'],
            'love_count' => (int)$row['love_count']
        ];
    }
    return $hot_prompts;
}
function getPromptDetails($conn, $prompt_id) {
    $sql = "SELECT p.title, p.short_description, p.image, pd.content, p.status,
            a.username, a.avatar  
            FROM prompt p
            JOIN promptdetail pd ON p.prompt_id = pd.prompt_id
            JOIN account a ON p.account_id = a.account_id 
            WHERE p.prompt_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prompt_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result;
}

function createNotification($reciever_id, $sender_id, $prompt_id, $message, $conn) {
    if ($reciever_id == $sender_id) return;  // Không notify chính mình

    $sql = "INSERT INTO notification (reciever_id, sender_id, prompt_id, message, created_at, isRead) 
            VALUES (?, ?, ?, ?, NOW(), 0)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for createNotification: " . $conn->error);
        return;
    }
    $stmt->bind_param("iiis", $reciever_id, $sender_id, $prompt_id, $message);
    $stmt->execute();
    $stmt->close();
}

function updateStatus($conn, $prompt_id, $action, $comment) {
    $sql = "";
    $target_status = null;
    $is_delete_action = false;
    $is_reject = false;

    if ($action == "approve" || $action == "unreport") {
        $target_status = "public";
        $sql = "UPDATE prompt SET status = ? WHERE prompt_id = ?";
    } 
    else if ($action == "delete") {
        $is_delete_action = true;
        $sql = "DELETE FROM prompt WHERE prompt_id = ?";
    } 
    else if ($action == "reject") {
        $is_reject = true;
        $target_status = "reject";
        $sql = "UPDATE prompt SET status = ?, reason = ? WHERE prompt_id = ?";
    }
    else {
        return [
            'success' => false,
            'message' => "Hành động không hợp lệ: {$action}."
        ];
    }


    /* ======= EXECUTE SQL ======== */
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return ['success' => false, 'message' => "Lỗi hệ thống (501)"];
    }

    if ($is_delete_action) {
        $stmt->bind_param("i", $prompt_id);
    } 
    else if ($is_reject) {
        $stmt->bind_param("ssi", $target_status, $comment, $prompt_id);
    }
    else {
        $stmt->bind_param("si", $target_status, $prompt_id);
    }

    $execute_success = $stmt->execute();
    $rows_affected = $stmt->affected_rows;
    $stmt->close();

    if (!$execute_success) {
        return ['success' => false, 'message' => "Lỗi khi thực thi truy vấn."];
    }


    /* ===============================
       🎯 XOÁ THÔNG BÁO ADMIN TƯƠNG ỨNG
    =============================== */

    if ($action == "approve" || $action == "reject") {
        // Xoá thông báo chờ duyệt
        $conn->query("
            DELETE FROM admin_notifications 
            WHERE prompt_id = $prompt_id AND type = 'waiting'
        ");
    }

    if ($action == "unreport") {
        // Xóa thông báo báo cáo
        $conn->query("
            DELETE FROM admin_notifications 
            WHERE prompt_id = $prompt_id AND type = 'report'
        ");
    }

    if ($action == "delete") {
        // Xóa toàn bộ thông báo
        $conn->query("
            DELETE FROM admin_notifications 
            WHERE prompt_id = $prompt_id
        ");
    }


    /* ===============================
       TRẢ VỀ KẾT QUẢ
    =============================== */
    if ($rows_affected > 0) {
        return [
            'success' => true,
            'message' => "Xử lý thành công!"
        ];
    } else {
        return [
            'success' => true, 
            'message' => "Không có thay đổi nào."
        ];
    }
}

function getFollowingUsers($user_id, $conn) {
    $sql = "
        SELECT u.account_id, u.username, u.avatar
        FROM follow f
        INNER JOIN account u ON f.following_id = u.account_id
        WHERE f.follower_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}