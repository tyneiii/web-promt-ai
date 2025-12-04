<?php
// Include controller and helpers
include_once __DIR__ . '/../../Controller/manager/revenue_controller.php';
include_once __DIR__ . '/revenue_helpers.php';
// ...existing code...
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

/* ========== HANDLE CSV EXPORT ========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv_year'])) {
    $exportYear = $_POST['export_csv_year'];
    exportCSVByYear($conn, $exportYear);
}

/* ========== LOAD DATA FOR PAGE ========== */
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

/* ========== END DATA LOADING ========== */
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <link rel="icon" href="../../public/img/T1.png" type="image/png" sizes="180x180">
    <meta charset="UTF-8">
    <title>Doanh thu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../public/css/manager/sidebar.css">
    <link rel="stylesheet" href="../../public/css/manager/revenue.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container">
        <?php include_once __DIR__ . '/layout/sidebar.php'; ?>

        <div class="main">
            <!-- ========== PREPARE PAGE DATA ========== -->
            <?php
            /* Get revenue for selected month */
            $rev = $conn->prepare("
                SELECT total_clicks, click_revenue, fixed_revenue, total_revenue, admin_keep, user_pool
                FROM revenue_history
                WHERE month_year = ?
                LIMIT 1
            ");
            $rev->bind_param("s", $selectedMonth);
            $rev->execute();
            $revData = $rev->get_result()->fetch_assoc();
            $rev->close();

            $clicks = $revData['total_clicks'] ?? 0;
            $clickRevenue = $revData['click_revenue'] ?? 0;
            $fixedRevenue = $revData['fixed_revenue'] ?? 0;
            $totalRevenue = $revData['total_revenue'] ?? 0;
            $adminKeep = $revData['admin_keep'] ?? 0;
            $userPool = $revData['user_pool'] ?? 0;

            /* Get love stats for selected month */
            $monthlyLoveStmt = $conn->prepare("
                SELECT lm.account_id, a.username, a.email, lm.love_count
                FROM love_monthly lm
                JOIN account a ON a.account_id = lm.account_id
                WHERE lm.month_year = ?
                HAVING lm.love_count >= 5
            ");
            $monthlyLoveStmt->bind_param("s", $selectedMonth);
            $monthlyLoveStmt->execute();
            $userStatsRs = $monthlyLoveStmt->get_result();

            $userStats = [];
            $totalLove = 0;

            while ($row = $userStatsRs->fetch_assoc()) {
                $row['love_count'] = (int)$row['love_count'];
                $userStats[] = $row;
                $totalLove += $row['love_count'];
            }

            $eligibleUsers = count($userStats);
            $userPool = $totalRevenue * 0.6;
            $adminKeep = $totalRevenue * 0.4;
            $moneyPerLove = $totalLove > 0 ? $userPool / $totalLove : 0;

            /* Get eligible users and payout info */
            $eligibleUsersQuery = $conn->query("
                SELECT a.account_id, a.username, a.email, SUM(p.love_count) AS total_love
                FROM account a
                JOIN prompt p ON p.account_id = a.account_id
                WHERE p.love_count >= 5
                GROUP BY a.account_id, a.username, a.email
            ");

            /* Get user payout info */
            $userPayoutQuery = $conn->prepare("
                SELECT 
                    lm.account_id, acc.username, acc.email, lm.love_count AS love_in_month,
                    (lm.love_count * ?) AS money_received, bi.bank_name, bi.bank_account_number
                FROM love_monthly lm
                JOIN account acc ON acc.account_id = lm.account_id
                LEFT JOIN userpayoutinfo bi ON bi.account_id = lm.account_id
                WHERE lm.month_year = ?
                HAVING lm.love_count >= 5
                ORDER BY money_received DESC
            ");
            $userPayoutQuery->bind_param("ds", $moneyPerLove, $selectedMonth);
            $userPayoutQuery->execute();
            $userPayoutResult = $userPayoutQuery->get_result();
            ?>
            <!-- ========== END PREPARE PAGE DATA ========== -->

            <!-- ========== MESSAGES & PAYOUT BUTTON ========== -->
            <?php if ($payoutMessage): ?>
                <div style="margin: 15px 25px; padding: 10px 15px; border-radius: 8px; background: #222; border: 1px solid #555; color: #fff;">
                    <?= $payoutMessage ?>
                </div>
            <?php endif; ?>

            <div style="margin: 0 25px 10px 25px;">
                <form method="post" onsubmit="return confirm('Bạn chắc chắn muốn CHIA TIỀN cho tháng hiện tại? Hành động này không thể hoàn tác.');">
                    <input type="hidden" name="action" value="run_payout">
                    <button type="submit" style="background: #ff4d4d; color: white; border: none; padding: 10px 18px; border-radius: 6px; cursor: pointer; font-weight: bold;">
                        💸 Chia tiền tháng này
                    </button>
                </form>
            </div>
            <!-- ========== END MESSAGES & PAYOUT BUTTON ========== -->

            <!-- ========== REVENUE DASHBOARD ========== -->
            <div class="revenue-wrapper">
                <fieldset class="revenue-fieldset">
                    <legend>💰 Thống kê doanh thu tháng</legend>

                    <div class="two-col">
                        <!-- Revenue Stats Box -->
                        <div class="stat-box">
                            <h3><i class="fa-solid fa-chart-line"></i> Doanh thu</h3>
                            <p><strong>Tổng click:</strong> <?= $clicks ?></p>
                            <p><strong>Doanh thu click:</strong> <?= number_format($clickRevenue, 2) ?> USD</p>
                            <p><strong>Doanh thu cố định:</strong> 200 USD</p>
                            <p class="stat-highlight"><strong>Tổng doanh thu:</strong> <?= number_format($totalRevenue, 2) ?> USD</p>
                        </div>

                        <!-- Revenue Chart Box -->
                        <div class="stat-box chart-box">
                            <div class="chart-header">
                                <h3><i class="fa-solid fa-chart-column"></i> Biểu đồ doanh thu theo năm <?= htmlspecialchars($chartYear) ?></h3>
                                <form method="get" class="year-filter">
                                    <label for="year-select">Năm:</label>
                                    <select id="year-select" name="chart_year" onchange="this.form.submit()">
                                        <?php foreach ($years as $y): ?>
                                            <option value="<?= $y ?>" <?= $y == $chartYear ? 'selected' : '' ?>>
                                                <?= $y ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>

                            <div class="chart-scroll-wrapper">
                                <canvas id="revenueChart"></canvas>
                            </div>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="export_csv_year" value="<?= $chartYear ?>">
                                <button type="submit" style="background:#007bff; color:#fff; border:none; padding:8px 14px; border-radius:6px; cursor:pointer; margin-left:10px;">
                                    Xuất CSV năm <?= $chartYear ?>
                                </button>
                            </form>
                        </div>

                        <div class="stat-box">
                            <h3><i class="fa-solid fa-user-group"></i> Chia tiền User</h3>
                            <p><strong>User đủ điều kiện:</strong> <?= $eligibleUsers ?></p>
                            <p><strong>User Pool (60%):</strong> <?= number_format($userPool, 2) ?> USD</p>
                            <p><strong>Admin giữ (40%):</strong> <?= number_format($adminKeep, 2) ?> USD</p>
                            <p class="stat-highlight"><strong>Tiền mỗi 1 tim:</strong> <?= number_format($moneyPerLove, 4) ?> USD</p>
                        </div>

                        <!-- User Payout List -->
                        <div class="stat-box" style="margin-top:25px;">
                            <h3><i class="fa-solid fa-users"></i> Danh sách user nhận tiền trong tháng</h3>

                            <?php if ($eligibleUsersQuery->num_rows == 0): ?>
                                <p>Không có user nào đủ điều kiện nhận tiền.</p>
                            <?php else: ?>
                                <form method="get" style="margin-bottom: 15px;">
                                    <label>Chọn năm:</label>
                                    <select name="payout_year" onchange="this.form.submit()" style="background:#111;color:#fff;padding:5px;border-radius:6px;border:1px solid #444;">
                                        <?php foreach ($availableYears as $y): ?>
                                            <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>>
                                                <?= $y ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <label style="margin-left:15px;">Chọn tháng:</label>
                                    <select name="payout_month" onchange="this.form.submit()" style="background:#111;color:#fff;padding:5px;border-radius:6px;border:1px solid #444;">
                                        <?php foreach ($months as $m): ?>
                                            <option value="<?= $m ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                                                <?= $m ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>

                                <table style="width:100%; border-collapse: collapse; margin-top:15px;">
                                    <thead>
                                        <tr style="background:#333; color:white;">
                                            <th style="padding:10px; border:1px solid #444;">User ID</th>
                                            <th style="padding:10px; border:1px solid #444;">Username</th>
                                            <th style="padding:10px; border:1px solid #444;">Email</th>
                                            <th style="padding:10px; border:1px solid #444;">Love</th>
                                            <th style="padding:10px; border:1px solid #444;">Tiền nhận (USD)</th>
                                            <th style="padding:10px; border:1px solid #444;">Bank Name</th>
                                            <th style="padding:10px; border:1px solid #444;">Bank Account</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php if ($userPayoutResult->num_rows > 0): ?>
                                            <?php while ($row = $userPayoutResult->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?= $row['account_id'] ?></td>
                                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                                    <td><?= $row['love_in_month'] ?></td>
                                                    <td style="color:#00e676;"><?= number_format($row['money_received'], 2) ?> USD</td>
                                                    <td><?= htmlspecialchars($row['bank_name'] ?? 'Chưa Có') ?></td>
                                                    <td><?= htmlspecialchars($row['bank_account_number'] ?? 'Chưa Có') ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" style="text-align:center; padding:12px; color:#ccc;">
                                                    Không có user nào nhận tiền trong tháng này.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    <script>
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const labels = <?= json_encode($labels) ?>;
        const clickRevenueData = <?= json_encode($clickRevenueData) ?>;
        const userPoolData = <?= json_encode($userPoolData) ?>;
        const totalRevenueData = <?= json_encode($totalRevenueData) ?>;

        const revenueChart = new Chart(ctx, {
            data: {
                labels: labels,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Doanh thu click',
                        data: clickRevenueData,
                        backgroundColor: 'rgba(255, 0, 0, 0.7)',
                        borderColor: 'rgba(180, 0, 0, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        type: 'bar',
                        label: 'User Pool (60%)',
                        data: userPoolData,
                        backgroundColor: 'rgba(200, 200, 200, 0.8)',
                        borderColor: 'rgba(150, 150, 150, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        type: 'line',
                        label: 'Tổng doanh thu',
                        data: totalRevenueData,
                        borderColor: 'rgba(0, 122, 255, 1)',
                        backgroundColor: 'rgba(0, 122, 255, 0.2)',
                        borderWidth: 3,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(0, 122, 255, 1)',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                stacked: false,
                scales: {
                    y: {
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: "#444" },
                        ticks: { color: "white" },
                        title: {
                            display: true,
                            text: 'USD (Cột)',
                            color: '#fff'
                        }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: { color: "white" },
                        title: {
                            display: true,
                            text: 'USD (Đường)',
                            color: '#fff'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: "white",
                            autoSkip: false,
                            maxRotation: 60,
                            minRotation: 60
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: "white" }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': ' + ctx.parsed.y.toFixed(2) + ' USD';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>