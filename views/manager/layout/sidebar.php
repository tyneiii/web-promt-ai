<?php
include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../controller/user/prompt.php';
include_once __DIR__ . '/../../../controller/account.php';
include_once __DIR__ . '/../../../helpers/admin_notification.php';
$notif = getAdminNotificationCount($conn);
// Đếm số bài chờ duyệt
$waiting_count = $conn->query("
    SELECT COUNT(*) AS total 
    FROM prompt 
    WHERE status = 'waiting'
")->fetch_assoc()['total'];

// Đếm số bài bị báo cáo
$report_count = $conn->query("
    SELECT COUNT(*) AS total 
    FROM prompt 
    WHERE status = 'report'
")->fetch_assoc()['total'];

$admin_notif_total = $waiting_count + $report_count;

$currentPage = basename($_SERVER['PHP_SELF']);
function isActive($page)
{
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>

<div class="sidebar">
    <h2>
        <form action="../user/home.php">
            <input type="submit" value="Về trang chủ">
        </form>
    </h2>

    <ul>
        <li>
            <a href="../manager/account.php" class="menu-link <?= isActive('account.php') ?>">
                <i class="fa-solid fa-users"></i> Quản lý tài khoản
            </a>
        </li>

        <li>
            <a href="../manager/post.php" class="menu-link <?= isActive('post.php') ?>">
                <i class="fa-solid fa-file-lines"></i> Quản lý bài đăng
                <?php if ($admin_notif_total > 0): ?>
                    <span class="badge-notif"><?= $notif['total'] ?></span>
                <?php endif; ?>
            </a>
        </li>

        <li>
            <a href="../manager/notifications.php" class="menu-link <?= isActive('notifications.php') ?>">
                <i class="fa-solid fa-bell"></i> Thông báo
                <?php if ($admin_notif_total > 0): ?>
                    <span class="badge-notif"><?= $admin_notif_total ?></span>
                <?php endif; ?>
            </a>
        </li>

        <li>
            <a href="../manager/revenue.php" class="menu-link <?= isActive('revenue.php') ?>">
                <i class="fa-solid fa-money-bill"></i> Doanh thu
            </a>
        </li>
    </ul>
    <script>
        function updateAdminNotifs() {
            fetch("../../public/ajax/admin_notif_api.php")
                .then(res => res.json())
                .then(data => {
                    let badges = document.querySelectorAll(".badge-notif");

                    badges.forEach(badge => {
                        if (data.total > 0) {
                            badge.textContent = data.total;
                            badge.style.display = "inline-block";
                        } else {
                            badge.style.display = "none";
                        }
                    });
                })
                .catch(err => console.error("Admin notif error:", err));
        }

        // cập nhật mỗi 5 giây
        setInterval(updateAdminNotifs, 5000);

        // chạy ngay khi load trang
        updateAdminNotifs();
    </script>

</div>