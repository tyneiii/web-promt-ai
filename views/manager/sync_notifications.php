<?php
include_once __DIR__ . "/../../config.php";

echo "<pre>";
echo "=== RESET USER_PAYOUT + SYNC LOVE_MONTHLY ===\n\n";

/* ============================================================
   1) XÓA toàn bộ user_payout TRỪ tháng 2025-11 (dữ liệu thật)
   ============================================================ */
echo "Đang xoá dữ liệu user_payout cũ...\n";

<<<<<<< HEAD
=======
// Đồng bộ bài waiting -> Thông báo admin
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6
$conn->query("
    DELETE FROM user_payout
    WHERE month_year <> '2025-11'
");

<<<<<<< HEAD
echo "Đã xoá xong user_payout!\n\n";


/* ============================================================
   2) RANDOM lại user_payout cho toàn bộ năm 2024 → 2025-10
   ============================================================ */
$start = new DateTime("2024-01-01");
$end   = new DateTime("2025-11-01"); // dừng trước tháng 11

// Lấy toàn bộ user
$users = $conn->query("SELECT account_id FROM account");

if ($users->num_rows == 0) {
    echo "Không có user nào!\n";
    exit;
}

echo "Đang random dữ liệu user_payout...\n";

$insert = $conn->prepare("
    INSERT INTO user_payout (account_id, month_year, love_in_month, money_received, status)
    VALUES (?, ?, ?, ?, 'paid')
");

while ($start < $end) {
    $month = $start->format("Y-m");

    echo " - Tháng $month...\n";

    foreach ($users as $u) {
        $userId = $u['account_id'];

        // RANDOM love >= 0
        $love = rand(0, 20);

        // Chỉ trả tiền nếu love >= 5
        $money = ($love >= 5) ? $love * (rand(50, 150) / 100) : 0;

        $insert->bind_param("isid", $userId, $month, $love, $money);
        $insert->execute();
    }

    $start->modify("+1 month");
}

echo "Random user_payout xong!\n\n";


/* ============================================================
   3) XÓA love_monthly cũ và SYNC theo user_payout mới random
   ============================================================ */
echo "Đang xoá love_monthly cũ (trừ tháng 2025-11)...\n";

$conn->query("
    DELETE FROM love_monthly
    WHERE month_year <> '2025-11'
");

echo "Đã xoá xong love_monthly!\n\n";

// Lấy lại dữ liệu user_payout (trừ tháng thật)
$result = $conn->query("
    SELECT account_id, month_year, love_in_month
    FROM user_payout
    WHERE month_year <> '2025-11'
    ORDER BY month_year ASC
");

$insert2 = $conn->prepare("
    INSERT INTO love_monthly (account_id, month_year, love_count)
    VALUES (?, ?, ?)
");

echo "Đang sync love_monthly...\n";
while ($row = $result->fetch_assoc()) {
    $insert2->bind_param(
        "iss",
        $row['account_id'],
        $row['month_year'],
        $row['love_in_month']
    );
    $insert2->execute();

    echo " ✔ {$row['month_year']} - User {$row['account_id']} - Love {$row['love_in_month']}\n";
}

echo "\nHoàn tất! 🎉";
echo "</pre>";
?>
=======
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
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6
