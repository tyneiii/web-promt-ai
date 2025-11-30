<?php
    include_once __DIR__ . "/config.php";

    // 1. Lấy tháng hiện tại 
    $current_month = date("Y-m");

    // Kiểm tra tháng này đã chia tiền chưa
    $check = $conn->prepare("
        SELECT COUNT(*) 
        FROM user_payout 
        WHERE month_year = ?
    ");
    $check->bind_param("s", $current_month);
    $check->execute();
    $check->bind_result($exists);
    $check->fetch();
    $check->close();

    if ($exists > 0) {
        die("Tháng $current_month đã chạy chia tiền trước đó.");
    }

    // 2. TÍNH TỔNG DOANH THU

    // Số click trong bảng revenuemetrics
    $res = $conn->query("SELECT current_month_clicks FROM revenuemetrics WHERE metric_id = 1");
    $data = $res->fetch_assoc();

    $clicks = $data['current_month_clicks'] ?? 0;
    $clickRevenue = $clicks * 0.1;
    $fixedRevenue = 200;

    $totalRevenue = $clickRevenue + $fixedRevenue;

    // Chia tỷ lệ
    $userPool = $totalRevenue * 0.6;
    $adminKeep = $totalRevenue * 0.4;


    // 3. LẤY DANH SÁCH USER ĐỦ ĐIỀU KIỆN NHẬN TIỀN
    // User đủ điều kiện = tổng love >= 5
    $sql = "
        SELECT 
            a.account_id,
            SUM(p.love_count) AS total_love
        FROM account a
        JOIN prompt p ON p.account_id = a.account_id
        GROUP BY a.account_id
        HAVING total_love >= 5
    ";
    $rs = $conn->query($sql);

    $users = [];
    $totalLove = 0;

    while ($row = $rs->fetch_assoc()) {
        $row['total_love'] = (int)$row['total_love'];
        $totalLove += $row['total_love'];
        $users[] = $row;
    }

    if ($totalLove == 0) {
        die("Không có user đủ điều kiện nhận tiền.");
    }

    $moneyPerLove = $userPool / $totalLove;


    // 4. INSERT VÀO BẢNG user_payout 
    $insert = $conn->prepare("
        INSERT INTO user_payout (account_id, month_year, love_in_month, money_received, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");

    foreach ($users as $u) {
        $money = $u['total_love'] * $moneyPerLove;

        $insert->bind_param("isid",
            $u['account_id'],
            $current_month,
            $u['total_love'],
            $money
        );
        $insert->execute();
    }

    $insert->close();


    // 5. LƯU VÀO revenue_history 

    $saveRev = $conn->prepare("
        INSERT INTO revenue_history (month_year, click_revenue, user_pool, total_revenue)
        VALUES (?, ?, ?, ?)
    ");
    $saveRev->bind_param("sddd", $current_month, $clickRevenue, $userPool, $totalRevenue);
    $saveRev->execute();
    $saveRev->close();

    echo "✅ Đã chia tiền thành công cho tháng $current_month\n";
    echo "Tổng user nhận tiền: " . count($users) . "\n";
    echo "User pool: $userPool USD\n";
    echo "Money per love: $moneyPerLove USD\n";
?>