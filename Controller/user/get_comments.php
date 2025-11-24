<?php
session_start();
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/prompt.php';  // Import function getUserComments

if (!isset($_SESSION['id_user'])) {
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit;
}

$account_id = (int)$_SESSION['id_user'];
$comments = getUserComments($account_id, $conn);

header('Content-Type: application/json');
echo json_encode($comments);
?>