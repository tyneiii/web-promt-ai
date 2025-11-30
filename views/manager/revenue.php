    <?php
<<<<<<< HEAD
        include_once __DIR__ . '/../../config.php';
        include_once __DIR__ . '/../../helpers/helper.php';
        /* ==========================
   XỬ LÝ NÚT CHIA TIỀN THÁNG NÀY
    ========================== */
=======
    include_once __DIR__ . '/../../config.php';
    include_once __DIR__ . '/../../helpers/helper.php';
    // XỬ LÝ NÚT CHIA TIỀN THÁNG NÀY
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6
    $payoutMessage = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_payout') {
        $current_month = date("Y-m");
<<<<<<< HEAD
        /* ====== AUTO TÍNH LOVE THÁNG HIỆN TẠI TỪ TIM THẬT ====== */

    /* 1. Kiểm tra xem tháng hiện tại đã có dữ liệu chưa */
    $checkLove = $conn->prepare("
        SELECT COUNT(*) 
        FROM love_monthly 
        WHERE month_year = ?
    ");
    $checkLove->bind_param("s", $current_month);
    $checkLove->execute();
    $checkLove->bind_result($loveExists);
    $checkLove->fetch();
    $checkLove->close();

    /* 2. Nếu chưa có, TẠO MỚI love_monthly CHỈ CHO THÁNG NÀY bằng timestamp */
    if ($loveExists == 0) {

        // Tính love theo lịch sử TIM THẬT trong tháng hiện tại
        $insertLove = $conn->prepare("
            INSERT INTO love_monthly (account_id, month_year, love_count)
            SELECT account_id, ?, COUNT(*)
            FROM love
            WHERE DATE_FORMAT(love_at, '%Y-%m') = ?
            GROUP BY account_id
        ");

        $insertLove->bind_param("ss", $current_month, $current_month);
        $insertLove->execute();
        $insertLove->close();
    }
    
    // 1. Kiểm tra đã chia tiền tháng này chưa
    $check = $conn->prepare("
=======

        // 1. Kiểm tra đã chia tiền tháng này chưa
        $check = $conn->prepare("
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6
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
            $payoutMessage = "Tháng $current_month đã chia tiền trước đó, không thể chia lại.";
        } else {
            // 2. Lấy doanh thu tháng hiện tại từ revenuemetrics
            $res = $conn->query("SELECT current_month_clicks FROM revenuemetrics WHERE metric_id = 1");
            $data = $res->fetch_assoc();

            $clicks = $data['current_month_clicks'] ?? 0;
            $clickRevenue = $clicks * 0.1;
            $fixedRevenue = 200;
            $totalRevenue = $clickRevenue + $fixedRevenue;

            $userPool  = $totalRevenue * 0.6;
            $adminKeep = $totalRevenue * 0.4;

<<<<<<< HEAD
        // 3. Lấy love của user cho tháng hiện tại (love_monthly)
        $sqlUsers = $conn->prepare("
            SELECT lm.account_id, a.username, lm.love_count
            FROM love_monthly lm
            JOIN account a ON a.account_id = lm.account_id
            WHERE lm.month_year = ?
            HAVING lm.love_count >= 5
        ");
        $sqlUsers->bind_param("s", $current_month);
        $sqlUsers->execute();
        $rs = $sqlUsers->get_result();
=======
            // 3. Lấy danh sách user đủ điều kiện (tổng love >= 5)
            $sqlUsers = "
            SELECT 
                a.account_id,
                SUM(p.love_count) AS total_love
            FROM account a
            JOIN prompt p ON p.account_id = a.account_id
            GROUP BY a.account_id
            HAVING total_love >= 5
        ";
            $rs = $conn->query($sqlUsers);
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6

            $users = [];
            $totalLove = 0;

<<<<<<< HEAD
        while ($row = $rs->fetch_assoc()) {
            $row['love_count'] = (int)$row['love_count'];
            $totalLove += $row['love_count'];
            $users[] = $row;
        }
=======
            while ($row = $rs->fetch_assoc()) {
                $row['total_love'] = (int)$row['total_love'];
                $totalLove += $row['total_love'];
                $users[] = $row;
            }
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6

            if ($totalLove == 0 || count($users) == 0) {
                $payoutMessage = "Không có user nào đủ điều kiện (tổng love >= 5), không thể chia tiền.";
            } else {
                $moneyPerLove = $userPool / $totalLove;

                // 4. Insert vào user_payout
                $insert = $conn->prepare("
                INSERT INTO user_payout (account_id, month_year, love_in_month, money_received, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");

<<<<<<< HEAD
            foreach ($users as $u) {
                $money = $u['love_count'] * $moneyPerLove;

                $insert->bind_param(
                    "isid",
                    $u['account_id'],
                    $current_month,
                    $u['love_count'], // đúng love của tháng
                    $money
                );
                $insert->execute();
            }
=======
                foreach ($users as $u) {
                    $money = $u['total_love'] * $moneyPerLove;

                    $insert->bind_param(
                        "isid",
                        $u['account_id'],
                        $current_month,
                        $u['total_love'],
                        $money
                    );
                    $insert->execute();
                }
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6

                $insert->close();

<<<<<<< HEAD
            // 5. Lưu vào revenue_history
            $saveRev = $conn->prepare("
                INSERT INTO revenue_history (
                    month_year,
                    total_clicks,
                    click_revenue,
                    fixed_revenue,
                    total_revenue,
                    admin_keep,
                    user_pool,
                    eligible_users,
                    money_per_user
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $eligibleUsers = count($users);
            $saveRev->bind_param(
                "sidddddid",
                $current_month,
                $clicks,
                $clickRevenue,
                $fixedRevenue,
                $totalRevenue,
                $adminKeep,
                $userPool,
                $eligibleUsers,
                $moneyPerLove
            );
            $saveRev->execute();
            $saveRev->close();
            /* ====== RESET CLICK THÁNG HIỆN TẠI ====== */
            $resetClick = $conn->prepare("
                UPDATE revenuemetrics
                SET 
                    current_month_clicks = 0,
                    last_month_revenue = ?,   -- lưu doanh thu tháng này
                    update_at = NOW()
                WHERE metric_id = 1
            ");
            $resetClick->bind_param("d", $totalRevenue);
            $resetClick->execute();
            $resetClick->close();
            /* ====== HẾT RESET ====== */
=======
                // 5. Lưu vào revenue_history
                $saveRev = $conn->prepare("
                INSERT INTO revenue_history (month_year, click_revenue, user_pool, total_revenue)
                VALUES (?, ?, ?, ?)
            ");
                $saveRev->bind_param("sddd", $current_month, $clickRevenue, $userPool, $totalRevenue);
                $saveRev->execute();
                $saveRev->close();
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6

                $payoutMessage = "Đã chia tiền thành công cho tháng $current_month. Tổng user nhận tiền: "
                    . count($users)
                    . " | Money per love: "
                    . number_format($moneyPerLove, 4) . " USD";
            }
        }
    }
<<<<<<< HEAD
}
        /* ===== XUẤT CSV THEO NĂM ĐƯỢC CHỌN ===== */
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_csv_year'])) {

            $exportYear = $_POST['export_csv_year'];

            // Lấy doanh thu theo năm
            $stmtExport = $conn->prepare("
                SELECT *
                FROM revenue_history
                WHERE LEFT(month_year, 4) = ?
                ORDER BY month_year ASC
            ");
            $stmtExport->bind_param("s", $exportYear);
            $stmtExport->execute();
            $result = $stmtExport->get_result();
            
            // Header tải về file CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=revenue_'.$exportYear.'.csv');

            // Mở output
            $output = fopen('php://output', 'w');

            // Ghi dòng header CSV
            fputcsv($output, [
                'id',
                'month_year',
                'total_clicks',
                'click_revenue',
                'fixed_revenue',
                'total_revenue',
                'admin_keep',
                'user_pool',
                'eligible_users',
                'money_per_user',
                'created_at'
            ]);

            // Ghi dữ liệu từng dòng
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, $row);
            }

            fclose($output);
            exit;
        }
        /* ====== LẤY DANH SÁCH THÁNG THEO NĂM ĐANG CHỌN (DÙNG CHO DROPDOWN) ====== */
        $availableYears = [];
        $yearQuery = $conn->query("SELECT DISTINCT LEFT(month_year,4) as y FROM user_payout ORDER BY y ASC");
        while ($row = $yearQuery->fetch_assoc()) {
            $availableYears[] = $row['y'];
        }
        $selectedYear = $_GET['payout_year'] ?? date('Y');

        $months = [];

        $stmtMonths = $conn->prepare("
            SELECT DISTINCT month_year
            FROM user_payout
            WHERE LEFT(month_year, 4) = ?
            ORDER BY month_year ASC
        ");
        $stmtMonths->bind_param("s", $selectedYear);
        $stmtMonths->execute();
        $resultMonths = $stmtMonths->get_result();

        while ($row = $resultMonths->fetch_assoc()) {
            $months[] = $row['month_year'];
        }
        $stmtMonths->close();

        /* Tháng đang chọn (mặc định là tháng mới nhất) */
        $selectedMonth = $_GET['payout_month'] ?? (count($months) ? $months[0] : date('Y-m'));
=======
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6


    /*  LẤY DANH SÁCH NĂM CÓ TRONG revenue_history  */
    $years = [];
    $yearRes = $conn->query("SELECT DISTINCT LEFT(month_year,4) AS y FROM revenue_history ORDER BY y ASC");
    while ($row = $yearRes->fetch_assoc()) {
        $years[] = $row['y'];
    }

<<<<<<< HEAD
        /* Năm đang chọn (mặc định là năm mới nhất trong DB) */
        $chartYear = $_GET['chart_year'] ?? (count($years) ? end($years) : date('Y'));
=======
    /* Năm đang chọn (mặc định là năm mới nhất trong DB) */
    $selectedYear = $_GET['year'] ?? (count($years) ? end($years) : date('Y'));
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6

    /*  LẤY DỮ LIỆU BIỂU ĐỒ THEO NĂM  */
    $stmtChart = $conn->prepare("
            SELECT month_year, click_revenue, user_pool, total_revenue
            FROM revenue_history
            WHERE LEFT(month_year,4) = ?
            ORDER BY month_year ASC
        ");
<<<<<<< HEAD
        $stmtChart->bind_param("s", $chartYear);
        $stmtChart->execute();
        $chartResult = $stmtChart->get_result();
=======
    $stmtChart->bind_param("s", $selectedYear);
    $stmtChart->execute();
    $chartResult = $stmtChart->get_result();
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6

    $labels = [];
    $clickRevenueData = [];
    $userPoolData = [];
    $totalRevenueData = [];

    while ($row = $chartResult->fetch_assoc()) {
        // month_year dạng 2025-01 -> label chỉ hiển thị "01", "02", ...
        $parts = explode('-', $row['month_year']);
        $labels[] = $parts[1] . '/' . $parts[0];   // "01/2025"
        $clickRevenueData[] = (float)$row['click_revenue'];
        $userPoolData[] = (float)$row['user_pool'];
        $totalRevenueData[] = (float)$row['total_revenue'];
    }
    $stmtChart->close();

    /* ====== phần tính toán doanh thu hiện tại, chia tiền user... 
        (giữ nguyên như bạn đã làm trước đó) 
        ===== */
    ?>


    <!DOCTYPE html>
    <html lang="vi">

    <head>
        <meta charset="UTF-8">
        <title>Doanh thu</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="../../public/css/manager/sidebar.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <style>
            .revenue-wrapper {
                padding: 25px;
                color: white;
            }

            fieldset.revenue-fieldset {
                border: 2px solid #ff4d4d;
                border-radius: 12px;
                padding: 20px;
            }

            fieldset.revenue-fieldset legend {
                padding: 0 8px;
                font-size: 22px;
                color: #ff4d4d;
                font-weight: bold;
            }

            .chart-box {
                height: 320px;
                display: flex;
                flex-direction: column;
            }

            .chart-box h3 {
                margin-bottom: 10px;
            }

            .year-filter label {
                margin-right: 8px;
            }

            .year-filter select {
                background: #111;
                color: #fff;
                border-radius: 6px;
                padding: 4px 8px;
                border: 1px solid #555;
            }

            .chart-scroll-wrapper {
                flex: 1;
                overflow-x: auto;
                overflow-y: hidden;
            }

            .chart-scroll-wrapper canvas {
                min-width: 900px;
                height: 100% !important;
            }

            .stat-box {
                background: #1c1c1c;
                padding: 16px;
                border-radius: 10px;
                margin-bottom: 16px;
                border: 1px solid #333;
            }

            .stat-box h3 {
                margin: 0 0 10px 0;
                color: #ffb74d;
                font-size: 20px;
            }

            .stat-box p {
                margin: 5px 0;
                font-size: 16px;
            }

            .stat-highlight {
                color: #00e676;
                font-weight: bold;
            }

            .two-col {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            @media (max-width: 760px) {
                .two-col {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>

    <body>
        <div class="container">

            <?php include_once __DIR__ . '/layout/sidebar.php'; ?>

            <div class="main">

                <?php
                /* ====== LẤY DOANH THU THEO THÁNG ĐANG CHỌN ====== */
                $rev = $conn->prepare("
                    SELECT 
                        total_clicks,
                        click_revenue,
                        fixed_revenue,
                        total_revenue,
                        admin_keep,
                        user_pool
                    FROM revenue_history
                    WHERE month_year = ?
                    LIMIT 1
                ");
                $rev->bind_param("s", $selectedMonth);
                $rev->execute();
                $revData = $rev->get_result()->fetch_assoc();
                $rev->close();

                /* Xử lý nếu không tìm thấy dữ liệu */
                $clicks        = $revData['total_clicks']    ?? 0;
                $clickRevenue  = $revData['click_revenue']   ?? 0;
                $fixedRevenue  = $revData['fixed_revenue']   ?? 0;
                $totalRevenue  = $revData['total_revenue']   ?? 0;
                $adminKeep     = $revData['admin_keep']      ?? 0;
                $userPool      = $revData['user_pool']       ?? 0;


                // Lấy love theo từng user cho THÁNG ĐANG CHỌN (love_monthly)
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

                $eligibleUsers = count($userStats);          // để hiển thị
                $userPool = $totalRevenue * 0.6;             // như bạn đang làm
                $adminKeep = $totalRevenue * 0.4;

                $moneyPerLove = $totalLove > 0 ? $userPool / $totalLove : 0;  // tiền / 1 tim

                $eligibleUsersQuery = $conn->query("
                    SELECT a.account_id, a.username, a.email, SUM(p.love_count) AS total_love
                    FROM account a
                    JOIN prompt p ON p.account_id = a.account_id
                    WHERE p.love_count >= 5
                    GROUP BY a.account_id, a.username, a.email
                ");
                $userPayoutQuery = $conn->prepare("
                    SELECT 
                        lm.account_id,
                        acc.username,
                        acc.email,
                        lm.love_count AS love_in_month,
                        (lm.love_count * ?) AS money_received,
                        bi.bank_name,
                        bi.bank_account_number
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
                // Lấy dữ liệu biểu đồ doanh thu theo tháng
                $chartQuery = $conn->query("
                    SELECT month_year, total_revenue 
                    FROM revenue_history 
                    ORDER BY month_year ASC
                ");

                $chartMonths = [];
                $revenues = [];

                while ($row = $chartQuery->fetch_assoc()) {
                    $chartMonths[] = $row['month_year'];
                    $revenues[] = $row['total_revenue'];
                }

                ?>
                <?php if ($payoutMessage): ?>
                    <div style="
                        margin: 15px 25px;
                        padding: 10px 15px;
                        border-radius: 8px;
                        background: #222;
                        border: 1px solid #555;
                        color: #fff;
                    ">
                        <?= $payoutMessage ?>
                    </div>
                <?php endif; ?>

                <div style="margin: 0 25px 10px 25px;">
                    <form method="post"
                        onsubmit="return confirm('Bạn chắc chắn muốn CHIA TIỀN cho tháng hiện tại? Hành động này không thể hoàn tác.');">
                        <input type="hidden" name="action" value="run_payout">
                        <button type="submit" style="
                            background: #ff4d4d;
                            color: white;
                            border: none;
                            padding: 10px 18px;
                            border-radius: 6px;
                            cursor: pointer;
                            font-weight: bold;
                        ">
                            💸 Chia tiền tháng này
                        </button>
                    </form>
                </div>

                <div class="revenue-wrapper">
                    <fieldset class="revenue-fieldset">
                        <legend>💰 Thống kê doanh thu tháng</legend>

                        <div class="two-col">

                            <div class="stat-box">
                                <h3><i class="fa-solid fa-chart-line"></i> Doanh thu</h3>

                                <p><strong>Tổng click:</strong> <?= $clicks ?></p>
                                <p><strong>Doanh thu click:</strong> <?= number_format($clickRevenue, 2) ?> USD</p>
                                <p><strong>Doanh thu cố định:</strong> 200 USD</p>
                                <p class="stat-highlight"><strong>Tổng doanh thu:</strong> <?= number_format($totalRevenue, 2) ?> USD</p>
                            </div>


                            <div class="stat-box chart-box">
                                <div class="chart-header">
                                    <h3>
<<<<<<< HEAD
                                        <i class="fa-solid fa-chart-column"></i> 
                                        Biểu đồ doanh thu theo năm <?= htmlspecialchars($chartYear) ?>
=======
                                        <i class="fa-solid fa-chart-column"></i>
                                        Biểu đồ doanh thu theo năm <?= htmlspecialchars($selectedYear) ?>
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6
                                    </h3>

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
                                    <button type="submit" style="
                                        background:#007bff;
                                        color:#fff;
                                        border:none;
                                        padding:8px 14px;
                                        border-radius:6px;
                                        cursor:pointer;
                                        margin-left:10px;
                                    ">
                                        Xuất CSV năm <?= $chartYear ?>
                                    </button>
                                </form>
                            </div>


                            <div class="stat-box">
                                <h3><i class="fa-solid fa-user-group"></i> Chia tiền User</h3>
                                <p><strong>User đủ điều kiện:</strong> <?= $eligibleUsers ?></p>
                                <p><strong>User Pool (60%):</strong> <?= number_format($userPool, 2) ?> USD</p>
                                <p><strong>Admin giữ (40%):</strong> <?= number_format($adminKeep, 2) ?> USD</p>
                                <p class="stat-highlight">
                                    <strong>Tiền mỗi 1 tim:</strong> <?= number_format($moneyPerLove, 4) ?> USD
                                </p>

                            </div>

                            <div class="stat-box" style="margin-top:25px;">
                                <h3><i class="fa-solid fa-users"></i> Danh sách user nhận tiền trong tháng</h3>

                                <?php if ($eligibleUsersQuery->num_rows == 0): ?>
                                    <p>Không có user nào đủ điều kiện nhận tiền.</p>
                                <?php else: ?>
                                <form method="get" style="margin-bottom: 15px;">
                                    <label>Chọn năm:</label>
                                    <select name="payout_year" onchange="this.form.submit()"
                                        style="background:#111;color:#fff;padding:5px;border-radius:6px;border:1px solid #444;">
                                        <?php foreach ($availableYears as $y): ?>
                                            <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>>
                                                <?= $y ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <label style="margin-left:15px;">Chọn tháng:</label>
                                    <select name="payout_month" onchange="this.form.submit()"
                                        style="background:#111;color:#fff;padding:5px;border-radius:6px;border:1px solid #444;">
                                        <?php foreach ($months as $m): ?>
                                            <option value="<?= $m ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                                                <?= $m ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>

                                    <table style="width:100%; border-collapse: collapse; margin-top:15px;">
                                        <thead>
<<<<<<< HEAD
                                        <tr style="background:#333; color:white;">
                                            <th style="padding:10px; border:1px solid #444;">User ID</th>
                                            <th style="padding:10px; border:1px solid #444;">Username</th>
                                            <th style="padding:10px; border:1px solid #444;">Email</th>
                                            <th style="padding:10px; border:1px solid #444;">Love</th>
                                            <th style="padding:10px; border:1px solid #444;">Tiền nhận (USD)</th>
                                            <th style="padding:10px; border:1px solid #444;">Bank Name</th>
                                            <th   th style="padding:10px; border:1px solid #444;">Bank Account</th>
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
=======
                                            <tr style="background:#333; color:white;">
                                                <th style="padding:10px; border:1px solid #444;">User ID</th>
                                                <th style="padding:10px; border:1px solid #444;">Username</th>
                                                <th style="padding:10px; border:1px solid #444;">Email</th>
                                                <th style="padding:10px; border:1px solid #444;">Love</th>
                                                <th style="padding:10px; border:1px solid #444;">Tiền nhận (USD)</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($userStats as $row):
                                                $moneyForUser = $row['total_love'] * $moneyPerLove;
                                            ?>
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6
                                                <tr>
                                                    <td colspan="5" style="text-align:center; padding:12px; color:#ccc;">
                                                        Không có user nào nhận tiền trong tháng này.
                                                    </td>
                                                </tr>
<<<<<<< HEAD
                                            <?php endif; ?>
=======
                                            <?php endforeach; ?>
>>>>>>> 8664f4e65a2988078fb8de6e68f9661c2b5321a6
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
                    datasets: [{
                            type: 'bar',
                            label: 'Doanh thu click',
                            data: clickRevenueData,
                            backgroundColor: 'rgba(255, 0, 0, 0.7)', // cột đỏ
                            borderColor: 'rgba(180, 0, 0, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            type: 'bar',
                            label: 'User Pool (60%)',
                            data: userPoolData,
                            backgroundColor: 'rgba(200, 200, 200, 0.8)', // cột xám
                            borderColor: 'rgba(150, 150, 150, 1)',
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            type: 'line',
                            label: 'Tổng doanh thu',
                            data: totalRevenueData,
                            borderColor: 'rgba(0, 122, 255, 1)', // đường xanh
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
                            grid: {
                                color: "#444"
                            },
                            ticks: {
                                color: "white"
                            },
                            title: {
                                display: true,
                                text: 'USD (Cột)',
                                color: '#fff'
                            }
                        },
                        y1: {
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                color: "white"
                            },
                            title: {
                                display: true,
                                text: 'USD (Đường)',
                                color: '#fff'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
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
                            labels: {
                                color: "white"
                            }
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