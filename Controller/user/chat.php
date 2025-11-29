<?php
function getIDChat($conn, $account_id) {
    $sql = "SELECT chat_id FROM chat WHERE account_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return "Lỗi chuẩn bị truy vấn kiểm tra: " . $conn->error;
    }
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['chat_id']; 
    }
    $sql = "INSERT INTO chat (`account_id`) VALUES (?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        return "Lỗi chuẩn bị truy vấn INSERT: " . $conn->error;
    }
    $stmt->bind_param("i", $account_id);
    if ($stmt->execute()) {
        $new_id = $conn->insert_id;
        return $new_id; 
    } else {
        return "Lỗi tạo đoạn chat";
    }
}

function getMessages($conn, $chat_id, $Limit, $oldest_id = null) {
    $where = "chat_id = ?";
    if ($oldest_id !== null) {
        $where .= " AND chat_detail_id < ?";
    }
    $sql = "SELECT chat_detail_id, sender_id, message, sent_at 
            FROM chat_detail
            WHERE $where 
            ORDER BY chat_detail_id DESC
            LIMIT $Limit";
    $stmt = $conn->prepare($sql);
    if ($oldest_id !== null){
        $stmt->bind_param("ii", $chat_id, $oldest_id); 
    }
    else{
        $stmt->bind_param("i", $chat_id); 
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    return array_reverse($messages); 
}
function saveMessage($conn, $chat_id, $sender_id, $message): bool|string {
    $sql = "INSERT INTO chat_detail (chat_id, sender_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $error = "Lỗi chuẩn bị truy vấn saveMessage: " . $conn->error;
        error_log($error);
        return $error;
    }
    $stmt->bind_param("iis", $chat_id, $sender_id, $message); 
    
    if ($stmt->execute()) {
        $stmt->close();
        return true; 
    } else {
        $error = "Lỗi thực thi truy vấn saveMessage: " . $stmt->error;
        error_log($error);
        $stmt->close();
        return $error; 
    }
}
function getChatList($conn, $account_id, $role): array
{
    $sql = "";
    $params = [];
    $types = "";
    if ($role === 1) {
        $sql = "SELECT c.chat_id, a.account_id AS partner_id,a.fullname AS partner_fullname, a.username AS username, a.avatar AS partner_avatar,
                (SELECT message FROM chat_detail cd WHERE cd.chat_id = c.chat_id ORDER BY sent_at DESC LIMIT 1) AS last_message,
                (SELECT sent_at FROM chat_detail cd WHERE cd.chat_id = c.chat_id ORDER BY sent_at DESC LIMIT 1) AS last_time
            FROM chat c
            JOIN account a ON a.account_id = c.account_id 
            ORDER BY last_time DESC ";
        $params = [];
        $types = ""; 
    } else {
        $sql = "SELECT c.chat_id, a.account_id AS partner_id,a.fullname AS partner_fullname, a.username AS username, a.avatar AS partner_avatar,
                (SELECT message FROM chat_detail cd WHERE cd.chat_id = c.chat_id ORDER BY sent_at DESC LIMIT 1) AS last_message,
                (SELECT sent_at FROM chat_detail cd WHERE cd.chat_id = c.chat_id ORDER BY sent_at DESC LIMIT 1) AS last_time
            FROM chat c
            JOIN account a ON a.role_id = 1
            WHERE c.account_id = ? 
            LIMIT 1 ";
        $params = [$account_id];
        $types = "i";
    }
    if (empty($sql)) {
        return [];
    }
    $stmt = $conn->prepare($sql);
    if (!empty($types) && !empty($params)) {
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'p' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    }
    if (!$stmt->execute()) {
        error_log("Lỗi thực thi truy vấn chat list: " . $stmt->error);
        return [];
    }
    $result = $stmt->get_result();
    $chat_list = [];
    while ($row = $result->fetch_assoc()) {
        $chat_list[] = $row;
    }
    $result->free(); 
    $stmt->close(); 
    return $chat_list;
}

function getInfoUserFromChat($conn, int $chat_id, $role)
{
    if($role === 1){
    $sql= "SELECT a.* 
           FROM chat c 
           JOIN account a ON a.account_id = c.account_id 
           WHERE c.chat_id = $chat_id";}
    else{
        $sql= "SELECT a.* 
           FROM account a 
           WHERE a.role_id = 1";
    }
    $result = $conn->query($sql);
    return $result->fetch_assoc();
}
?>