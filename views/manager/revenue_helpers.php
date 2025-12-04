
<?php
// Helper functions for revenue page
include_once __DIR__ . '/../../helpers/helper.php';

function checkAndCreateLoveMonthly($conn, $month) {
    $checkLove = $conn->prepare("SELECT COUNT(*) FROM love_monthly WHERE month_year = ?");
    $checkLove->bind_param("s", $month);
    $checkLove->execute();
    $loveExists = 0;
    $checkLove->bind_result($loveExists);
    $checkLove->fetch();
    $checkLove->close();

    if ($loveExists == 0) {
        $insertLove = $conn->prepare("
            INSERT INTO love_monthly (account_id, month_year, love_count)
            SELECT account_id, ?, COUNT(*)
            FROM love
            WHERE DATE_FORMAT(love_at, '%Y-%m') = ?
            GROUP BY account_id
        ");
        $insertLove->bind_param("ss", $month, $month);
        $insertLove->execute();
        $insertLove->close();
    }
}

function checkPayoutExists($conn, $month) {
    $check = $conn->prepare("SELECT COUNT(*) FROM user_payout WHERE month_year = ?");
    $check->bind_param("s", $month);
    $check->execute();
    $exists = 0;
    $check->bind_result($exists);
    $check->fetch();
    $check->close();
    return $exists > 0;
}

function getCurrentMonthRevenue($conn) {
    $res = $conn->query("SELECT current_month_clicks FROM revenuemetrics WHERE metric_id = 1");
    $data = $res->fetch_assoc();
    $clicks = $data['current_month_clicks'] ?? 0;
    $clickRevenue = $clicks * 0.1;
    $fixedRevenue = 200;
    $totalRevenue = $clickRevenue + $fixedRevenue;
    return [
        'clicks' => $clicks,
        'clickRevenue' => $clickRevenue,
        'fixedRevenue' => $fixedRevenue,
        'totalRevenue' => $totalRevenue,
        'userPool' => $totalRevenue * 0.6,
        'adminKeep' => $totalRevenue * 0.4
    ];
}

function getEligibleUsers($conn, $month) {
    $sqlUsers = $conn->prepare("
        SELECT lm.account_id, a.username, lm.love_count
        FROM love_monthly lm
        JOIN account a ON a.account_id = lm.account_id
        WHERE lm.month_year = ?
        HAVING lm.love_count >= 5
    ");
    $sqlUsers->bind_param("s", $month);
    $sqlUsers->execute();
    $rs = $sqlUsers->get_result();
    $users = [];
    $totalLove = 0;
    while ($row = $rs->fetch_assoc()) {
        $row['love_count'] = (int)$row['love_count'];
        $totalLove += $row['love_count'];
        $users[] = $row;
    }
    return ['users' => $users, 'totalLove' => $totalLove];
}

function processPayout($conn, $month, $users, $moneyPerLove) {
    $insert = $conn->prepare("
        INSERT INTO user_payout (account_id, month_year, love_in_month, money_received, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");
    foreach ($users as $u) {
        $money = $u['love_count'] * $moneyPerLove;
        $insert->bind_param("isid", $u['account_id'], $month, $u['love_count'], $money);
        $insert->execute();
    }
    $insert->close();
}

function saveRevenueHistory($conn, $month, $revData, $eligibleUsers, $moneyPerLove) {
    $saveRev = $conn->prepare("
        INSERT INTO revenue_history (
            month_year, total_clicks, click_revenue, fixed_revenue, total_revenue,
            admin_keep, user_pool, eligible_users, money_per_user
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $saveRev->bind_param(
        "sidddddid",
        $month, $revData['clicks'], $revData['clickRevenue'], $revData['fixedRevenue'],
        $revData['totalRevenue'], $revData['adminKeep'], $revData['userPool'],
        $eligibleUsers, $moneyPerLove
    );
    $saveRev->execute();
    $saveRev->close();
}

function resetClickCounter($conn, $totalRevenue) {
    $resetClick = $conn->prepare("
        UPDATE revenuemetrics
        SET current_month_clicks = 0, last_month_revenue = ?, update_at = NOW()
        WHERE metric_id = 1
    ");
    $resetClick->bind_param("d", $totalRevenue);
    $resetClick->execute();
    $resetClick->close();
}

function exportCSVByYear($conn, $year) {
    $stmtExport = $conn->prepare("
        SELECT * FROM revenue_history
        WHERE LEFT(month_year, 4) = ?
        ORDER BY month_year ASC
    ");
    $stmtExport->bind_param("s", $year);
    $stmtExport->execute();
    $result = $stmtExport->get_result();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=revenue_' . $year . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, [
        'id', 'month_year', 'total_clicks', 'click_revenue', 'fixed_revenue',
        'total_revenue', 'admin_keep', 'user_pool', 'eligible_users', 'money_per_user', 'created_at'
    ]);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}
