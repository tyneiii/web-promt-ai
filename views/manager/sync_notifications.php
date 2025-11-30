<?php
include_once __DIR__ . "/../../config.php";

echo "<pre>";
echo "=== RESET USER_PAYOUT + SYNC LOVE_MONTHLY ===\n\n";

/* ============================================================
   1) XÓA toàn bộ user_payout TRỪ tháng 2025-11 (dữ liệu thật)
   ============================================================ */
echo "Đang xoá dữ liệu user_payout cũ...\n";

// Đồng bộ bài waiting -> Thông báo admin
$conn->query("
    DELETE FROM user_payout
    WHERE month_year <> '2025-11'
");

// Đồng bộ bài report -> Thông báo admin
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
