<?php
session_start();
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/prompt.php';  // Import function getUserComments

if (!isset($_SESSION['account_id'])) {
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit;
}

$account_id = (int)$_SESSION['account_id'];
$comments = getUserComments($account_id, $conn);

header('Content-Type: application/json');
echo json_encode($comments);
?>