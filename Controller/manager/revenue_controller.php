<?php
// Controller logic for revenue page
include_once __DIR__ . '/../../config.php';
include_once __DIR__ . '/../../views/manager/revenue_helpers.php';
include_once __DIR__ . '/../../helpers/helper.php';

$payoutMessage = null;

// Handle payout action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_payout') {
    $current_month = date("Y-m");
    checkAndCreateLoveMonthly($conn, $current_month);
    if (checkPayoutExists($conn, $current_month)) {
        $payoutMessage = "Tháng $current_month đã chia tiền trước đó, không thể chia lại.";
    } else {
        $revData = getCurrentMonthRevenue($conn);
        $usersData = getEligibleUsers($conn, $current_month);
        $users = $usersData['users'];
        $totalLove = $usersData['totalLove'];
        if ($totalLove == 0 || count($users) == 0) {
            $payoutMessage = "Không có user nào đủ điều kiện (tổng love >= 5), không thể chia tiền.";
        } else {
            $moneyPerLove = $revData['userPool'] / $totalLove;
            processPayout($conn, $current_month, $users, $moneyPerLove);
            saveRevenueHistory($conn, $current_month, $revData, count($users), $moneyPerLove);
            resetClickCounter($conn, $revData['totalRevenue']);
            $payoutMessage = "Đã chia tiền thành công cho tháng $current_month. Tổng user nhận tiền: "
                . count($users) . " | Money per love: " . number_format($moneyPerLove, 4) . " USD";
        }
    }
}

// Handle CSV export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv_year'])) {
    $exportYear = $_POST['export_csv_year'];
    exportCSVByYear($conn, $exportYear);
}

// Load data for page
$availableYears = [];
$yearQuery = $conn->query("SELECT DISTINCT LEFT(month_year,4) as y FROM user_payout ORDER BY y ASC");
while ($row = $yearQuery->fetch_assoc()) {
    $availableYears[] = $row['y'];
}
$selectedYear = $_GET['payout_year'] ?? date('Y');

$months = [];
$stmtMonths = $conn->prepare("SELECT DISTINCT month_year FROM user_payout WHERE LEFT(month_year, 4) = ? ORDER BY month_year ASC");
$stmtMonths->bind_param("s", $selectedYear);
$stmtMonths->execute();
$resultMonths = $stmtMonths->get_result();
while ($row = $resultMonths->fetch_assoc()) {
    $months[] = $row['month_year'];
}
$stmtMonths->close();
$selectedMonth = $_GET['payout_month'] ?? (count($months) ? $months[0] : date('Y-m'));

$years = [];
$yearRes = $conn->query("SELECT DISTINCT LEFT(month_year,4) AS y FROM revenue_history ORDER BY y ASC");
while ($row = $yearRes->fetch_assoc()) {
    $years[] = $row['y'];
}
$chartYear = $_GET['chart_year'] ?? (count($years) ? end($years) : date('Y'));

$stmtChart = $conn->prepare("SELECT month_year, click_revenue, user_pool, total_revenue FROM revenue_history WHERE LEFT(month_year, 4) = ? ORDER BY month_year ASC");
$stmtChart->bind_param("s", $chartYear);
$stmtChart->execute();
$chartResult = $stmtChart->get_result();

$labels = [];
$clickRevenueData = [];
$userPoolData = [];
$totalRevenueData = [];
while ($row = $chartResult->fetch_assoc()) {
    $parts = explode('-', $row['month_year']);
    $labels[] = $parts[1] . '/' . $parts[0];
    $clickRevenueData[] = (float)$row['click_revenue'];
    $userPoolData[] = (float)$row['user_pool'];
    $totalRevenueData[] = (float)$row['total_revenue'];
}
$stmtChart->close();
