<?php
include_once __DIR__ . '/../../config.php';

// Đồng bộ bài waiting → Thông báo admin
$conn->query("
    INSERT INTO admin_notifications (type, prompt_id, message, is_read, created_at)
    SELECT 
        'waiting' AS type,
        prompt_id,
        CONCAT('Có bài viết chờ duyệt (#', prompt_id, ')') AS message,
        0 AS is_read,
        NOW()
    FROM prompt
    WHERE status = 'waiting'
");

// Đồng bộ bài report → Thông báo admin
$conn->query("
    INSERT INTO admin_notifications (type, prompt_id, message, is_read, created_at)
    SELECT 
        'report' AS type,
        prompt_id,
        CONCAT('Có bài viết bị báo cáo (#', prompt_id, ')') AS message,
        0 AS is_read,
        NOW()
    FROM prompt
    WHERE status = 'report'
");

echo "Đã đồng bộ toàn bộ thông báo từ bài viết waiting + report!";
?>
