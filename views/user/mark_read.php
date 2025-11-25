<?php
session_start();
include_once __DIR__ . '/../../config.php';  
include_once __DIR__ . '/../../Controller/user/notifications.php';

header('Content-Type: application/json');
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$id_user = $_SESSION['id_user'];
$action = $_POST['action'] ?? '';

if ($action === 'mark_all') {
    markAsRead($id_user, $conn);
    echo json_encode(['success' => true, 'unread_count' => 0]);  // Trả về count mới
} elseif ($action === 'mark_single') {
    $notif_id = (int)$_POST['id'];
    markAsRead($id_user, $conn, $notif_id);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>