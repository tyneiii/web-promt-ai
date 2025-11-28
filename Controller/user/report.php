<?php
function getReports($conn, $prompt_id)
{
    $sql = "SELECT a.username, a.avatar, r.reason, r.created_at
            FROM report r
            JOIN account a ON a.account_id = r.account_id
            WHERE r.prompt_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $prompt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}
