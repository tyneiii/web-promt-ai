<?php 
function getPrompt($account_id, $searchString, $conn)
{
    // Làm sạch chuỗi tìm kiếm
    $search = '';
    if (isset($searchString) && trim($searchString) !== '') {
        $search = $conn->real_escape_string(trim($searchString));
    }

    $sql = "SELECT 
            p.prompt_id, 
            a.username, 
            a.avatar, 
            p.short_description, 
            pd.content, 
            p.love_count, 
            p.save_count, 
            p.comment_count,
            CASE WHEN l.account_id IS NULL THEN 0 ELSE 1 END AS is_loved
        FROM prompt p
        JOIN account a ON a.account_id = p.account_id
        JOIN promptdetail pd ON p.prompt_id = pd.prompt_id
        LEFT JOIN love l ON l.prompt_id = p.prompt_id AND l.account_id = '$account_id'
        WHERE 
            p.short_description LIKE '%$search%' 
            OR pd.content LIKE '%$search%' 
            OR a.username LIKE '%$search%'
        ORDER BY p.prompt_id ASC
    ";
    $result=$conn->query($sql);
    $prompts = [];
    while ($row = $result->fetch_assoc()) {
        $id = $row['prompt_id'];
        if (!isset($prompts[$id])) {
            $prompts[$id] = [
                'prompt_id' => $id,
                'username' => $row['username'],
                'avatar' => $row['avatar'],
                'description' => $row['short_description'],
                'details' => [],
                'love_count' => $row['love_count'],
                'comment_count' => $row['comment_count'],
                'save_count' => $row['save_count'],
                'is_loved' => $row['is_loved'] == 1,
            ];
        }
        $prompts[$id]['details'][] = $row['content'];
    }

    return $prompts;
}
function lovePrompt($account_id, $prompt_id, $conn) {
    $checkSql = "SELECT * FROM love WHERE prompt_id='$prompt_id' AND account_id='$account_id'";
    $result = $conn->query($checkSql);

    if ($result && $result->num_rows > 0) {
        // Đã thả tim → bỏ tim
        $deleteSql = "DELETE FROM love WHERE prompt_id='$prompt_id' AND account_id='$account_id'";
        if ($conn->query($deleteSql) === TRUE) {
            $conn->query("UPDATE prompt SET love_count = love_count - 1 WHERE prompt_id='$prompt_id'");
            return "Bạn đã bỏ tim bài viết";
        } else {
            return "Lỗi khi bỏ tim: " . $conn->error;
        }
    } else {
        // Chưa thả tim → thêm tim
        $love_at = date('Y-m-d H:i:s');
        $insertSql = "INSERT INTO love (prompt_id, account_id, status, love_at)
                      VALUES ('$prompt_id', '$account_id', 'OPEN', '$love_at')";
        if ($conn->query($insertSql) === TRUE) {
            $conn->query("UPDATE prompt SET love_count = love_count + 1 WHERE prompt_id='$prompt_id'");
            return "Bạn đã tim bài viết";
        } else {
            return "Lỗi khi tim bài viết: " . $conn->error;
        }
    }
}

function getReportedPrompts($conn, $search) {
    $sql = "SELECT prompt.prompt_id, prompt.title, prompt.status, report.reason
            FROM prompt
            JOIN report ON prompt.prompt_id = report.prompt_id
            WHERE prompt.prompt_id = ? OR report.reason LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_search = "%" . $search . "%";
    $stmt->bind_param("is", $search, $like_search);
    $stmt->execute();
    return $stmt->get_result();
}
function getAlldPrompts($conn, $search, $status) {
    $sql = "SELECT prompt_id, title, short_description, status 
            FROM prompt
            WHERE (prompt_id = ? OR title LIKE ? OR short_description LIKE ?) AND status LIKE ? ";
    $stmt = $conn->prepare($sql);
    $like_search = "%" . $search . "%";
    $status_like = empty($status) ? "%" : $status;
    $stmt->bind_param("isss", $search, $like_search,$like_search,$status_like);
    $stmt->execute();
    return $stmt->get_result();
}
?>