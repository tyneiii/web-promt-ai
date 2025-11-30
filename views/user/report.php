<?php
session_start();
include_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['account_id'])) {
    die("Bạn phải đăng nhập để báo cáo.");
}

if (!isset($_POST['id'], $_POST['reason'])) {
    die("Thiếu dữ liệu báo cáo.");
}

$prompt_id  = (int)$_POST['id'];
$account_id = $_SESSION['account_id'];
$reason     = trim($_POST['reason']);

// Lưu vào bảng report
$stmt = $conn->prepare("
        INSERT INTO report(prompt_id, account_id, reason, created_at)
        VALUES(?, ?, ?, NOW())
    ");
$stmt->bind_param("iis", $prompt_id, $account_id, $reason);
$stmt->execute();

// Cập nhật status bài viết
$update = $conn->prepare("UPDATE prompt SET status = 'report' WHERE prompt_id = ?");
$update->bind_param("i", $prompt_id);
$update->execute();
$conn->query("
        INSERT INTO admin_notifications (type, prompt_id, message)
        VALUES ('report', $prompt_id, 'Có bài viết bị báo cáo (#$prompt_id)')
    ");


echo "Đã gửi báo cáo!";
