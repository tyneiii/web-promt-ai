<?php
session_start();
include_once __DIR__ . '/../../config.php';  
include_once __DIR__ . '/../../controller/user/notifications.php';

header('Content-Type: application/json');
if (!isset($_SESSION['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$account_id = $_SESSION['account_id'];
$action = $_POST['action'] ?? '';

if ($action === 'mark_all') {
    markAsRead($account_id, $conn);
    echo json_encode(['success' => true, 'unread_count' => 0]);  // Trả về count mới
} elseif ($action === 'mark_single') {
    $notif_id = (int)$_POST['id'];
    markAsRead($account_id, $conn, $notif_id);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>