<?php
    function getPrompt($id_user,$string,$conn)
    {
        $search = '';
        if (isset($string) && trim($string) !== '') {
            $search = $conn->real_escape_string($string);
        }

        $sql = "SELECT 
        p.prompt_id, a.username, a.avatar, p.short_description, 
        pd.content, p.love_count, p.save_count, p.comment_count,
        CASE WHEN l.account_id IS NULL THEN 0 ELSE 1 END AS is_loved
    FROM prompt p
    JOIN account a ON a.account_id = p.account_id
    JOIN promptdetail pd ON p.prompt_id = pd.prompt_id
    LEFT JOIN love l ON l.prompt_id = p.prompt_id AND l.account_id = '$id_user'
    WHERE 
        p.short_description LIKE '%$search%' 
        OR pd.content LIKE '%$search%' 
        OR a.username LIKE '%$search%'
    ORDER BY p.prompt_id ASC";
        $result = $conn->query($sql);
        $prompts = [];
        while ($row = $result->fetch_assoc()) {
            $id = $row['prompt_id'];
            if (!isset($prompts[$id])) {
                $prompts[$id] = [
                    'id' => $id,
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
   function lovePrompt($id_user, $id_prompt, $conn) {
    $sql = "SELECT * FROM love WHERE prompt_id='$id_prompt' AND account_id='$id_user'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $deleteSql = "DELETE FROM love WHERE prompt_id='$id_prompt' AND account_id='$id_user'";
        if ($conn->query($deleteSql) === TRUE) {
            $conn->query("UPDATE prompt SET love_count = love_count - 1 WHERE prompt_id='$id_prompt'");
            return "Bạn đã bỏ tim bài viết";
        } else {
            return "Lỗi: " . $conn->error;
        }
    } else {
        $love_at = date('Y-m-d H:i:s');
        $insertSql = "INSERT INTO love (prompt_id, account_id, status, love_at)
                      VALUES ('$id_prompt', '$id_user', 'OPEN', '$love_at')";
        if ($conn->query($insertSql) === TRUE) {
            $conn->query("UPDATE prompt SET love_count = love_count + 1 WHERE prompt_id='$id_prompt'");
            return "Bạn đã tim bài viết";
        } else {
            return "Lỗi: " . $conn->error;
        }
    }
}
?>