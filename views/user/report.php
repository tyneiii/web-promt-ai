<?php
    session_start();
    include_once __DIR__ . '/../../config.php';

    if (!isset($_SESSION['id_user'])) {
        die("Bạn phải đăng nhập để báo cáo.");
    }

    if (!isset($_POST['id'], $_POST['reason'])) {
        die("Thiếu dữ liệu báo cáo.");
    }

    $prompt_id  = (int)$_POST['id'];
    $account_id = $_SESSION['id_user'];
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

    echo "Đã gửi báo cáo!";
?>