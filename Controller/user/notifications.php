<?php

function getNotifications($reciever_id, $conn, $limit = 10, $unread_only = false) {
    $reciever_id = (int)$reciever_id;
    if ($reciever_id <= 0) return [];
    
    // Build SQL động
    $where = $unread_only ? "WHERE n.reciever_id = ? AND n.isRead = 0" : "WHERE n.reciever_id = ?";
    $sql = "SELECT n.*, 
            COALESCE(p.title, p.short_description, '') AS prompt_desc, 
            a.username AS sender_username 
            FROM notification n 
            LEFT JOIN prompt p ON n.prompt_id = p.prompt_id 
            LEFT JOIN account a ON n.sender_id = a.account_id 
            $where 
            ORDER BY n.created_at DESC 
            LIMIT ?";
    
    // Build bind params
    $types = "i";  // reciever_id
    $params = [$reciever_id];
    $types .= "i";  // limit
    $params[] = $limit;
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for getNotifications: " . $conn->error);
        return [];
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'notification_id' => $row['notification_id'],  
            'reciever_id' => $row['reciever_id'],
            'sender_id' => $row['sender_id'],
            'prompt_id' => $row['prompt_id'],
            'message' => $row['message'],
            'isRead' => (int)$row['isRead'],  
            'created_at' => $row['created_at'],
            'prompt_desc' => $row['prompt_desc'],
            'sender_username' => $row['sender_username'] ?? 'Hệ thống'  
        ];
    }
    return $notifications;
}

function getUnreadCount($reciever_id, $conn) {
    $reciever_id = (int)$reciever_id;
    if ($reciever_id <= 0) return 0;
    
    $sql = "SELECT COUNT(*) as count FROM notification WHERE reciever_id = ? AND isRead = 0";  
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for getUnreadCount: " . $conn->error);
        return 0;
    }
    $stmt->bind_param("i", $reciever_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return (int)$result['count'];
}

function markAsRead($reciever_id, $conn, $notification_id = null) {
    $reciever_id = (int)$reciever_id;
    if ($reciever_id <= 0) return false;
    
    if ($notification_id) {
        $notification_id = (int)$notification_id;
        $sql = "UPDATE notification SET isRead = 1 WHERE notification_id = ? AND reciever_id = ?";  
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed for markAsRead single: " . $conn->error);
            return false;
        }
        $stmt->bind_param("ii", $notification_id, $reciever_id);
    } else {
        $sql = "UPDATE notification SET isRead = 1 WHERE reciever_id = ?";  
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed for markAsRead all: " . $conn->error);
            return false;
        }
        $stmt->bind_param("i", $reciever_id);
    }
    
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
?>