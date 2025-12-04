<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (isset($_POST['out-btn'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    header("Location: home.php");
    exit();
}

include_once __DIR__ . '/../../../config.php';
include_once __DIR__ . '/../../../controller/user/notifications.php';

$tag = isset($_GET['tag']) ? (int)$_GET['tag'] : 0;
$view_status = $_GET['view_status'] ?? 'unread';
$tag_query = "SELECT tag_id, tag_name FROM tag ORDER BY tag_name ASC";
$tag_result = mysqli_query($conn, $tag_query);
$tags = mysqli_fetch_all($tag_result, MYSQLI_ASSOC);
$notifications = [];
$unread_count = 0;
if (isset($_SESSION['account_id']) && !empty($_SESSION['account_id'])) {
    $account_id = $_SESSION['account_id'];
    $unread_count = getUnreadCount($account_id, $conn);
    $notifications = getNotifications($account_id, $conn, 5); // 5 mới nhất
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <link rel="icon" href="../../public/img/T1.png" type="image/png" sizes="180x180">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prompt AI Share</title>
    <link rel="stylesheet" href="../../public/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ... (Phần CSS giữ nguyên) ... */
        body {
            padding-top: 80px !important;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 160px !important;
            }
        }

        #sticky-ad-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: transparent;
            text-align: center;
            z-index: 1000;
        }

        #ad-wrapper,
        .ad-wrapper {
            position: relative;
            display: inline-block;
        }

        #sticky-ad-banner img {
            height: 100px;
            width: 750px;
            max-width: 200vw;
            border-radius: 6px;
        }

        #close-ad-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 22px;
            font-weight: bold;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            padding: 3px 7px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10;
        }

        #close-ad-btn:hover {
            color: #fff;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            color: #666;
            font-size: 20px;
            margin-right: 10px;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            font-size: 10px;
            padding: 2px 5px;
            min-width: 16px;
            text-align: center;
            font-weight: bold;
        }

        /* Modal mới */
        .notification-modal {
            display: none;
            /* Ẩn mặc định */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            /* Backdrop mờ */
            z-index: 9999;
            /* Cao nhất */
            overflow-y: auto;
            /* Scroll nếu nhiều notif */
        }

        .notification-modal.show {
            display: flex !important;
            /* Mở bằng class show */
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 16px;
            font-weight: bold;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
        }

        .notification-item {
            display: block;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-decoration: none;
            color: #333;
        }

        .notification-item.unread {
            background: #f8f9fa;
            font-weight: bold;
        }

        .notification-item.read {
            font-weight: normal;
        }

        .notification-item:hover {
            background: #e9ecef;
        }

        .notification-time {
            color: #999;
            font-size: 12px;
            display: block;
        }

        .modal-footer {
            padding: 10px 15px;
            border-top: 1px solid #eee;
            text-align: center;
        }

        .modal-footer a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }

        #mark-all-read {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 14px;
            text-decoration: underline;
            margin-right: 10px;
        }

        /* Mobile: Modal full width */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                max-height: 90vh;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <div class="navbar-left">
            <a href="home.php" class="logo" style="text-decoration: none;" title="Trang chủ">Prompt AI</a>
        </div>
        <div class="navbar-center">
            <form action="home.php" method="get" class="navbar-center search-group">
                <input type="text" name="search" class="search-bar"
                    placeholder="Tìm kiếm prompt..."
                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                <input type="hidden" name="view_status" value="<?= $view_status ?>">
                <input type="hidden" name="tag" value="<?= $tag ?>">
                <button type="submit" class="search-btn">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
        <div class="navbar-right">
            <form action="home.php" method="get" class="filter-group">
                <?php if (isset($_GET['search'])): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search']) ?>">
                <?php endif; ?>
                <?php if (isset($_SESSION['account_id'])): ?>
                    <select name="view_status" onchange="this.form.submit()">
                        <option value="unread" <?= $view_status == 'unread' ? 'selected' : '' ?>>Chưa xem</option>
                        <option value="all" <?= $view_status == 'all' ? 'selected' : '' ?>>Tất cả</option>
                        <option value="seen" <?= $view_status == 'seen' ? 'selected' : '' ?>>Đã xem</option>
                    </select>
                <?php endif; ?>
                <select name="tag" onchange="this.form.submit()">
                    <option value="">Chủ đề</option>
                    <?php foreach ($tags as $t): ?>
                        <option value="<?= $t['tag_id'] ?>" <?= ($tag == $t['tag_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['tag_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php if (isset($_SESSION['account_id']) && !empty($_SESSION['account_id'])): ?>
                <div class="notification-bell" id="notification-bell" title="Thông báo">
                    <i class="fa-regular fa-bell icon"></i>
                    <?php if ($unread_count > 0): ?>
                        <span class="notification-badge"><?= $unread_count > 99 ? '99+' : $unread_count ?></span>
                    <?php endif; ?>
                </div>
                <div id="notification-modal" class="notification-modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <span>Thông báo <span id="unread-in-dropdown">(<?= $unread_count ?> mới)</span></span>
                            <button class="close-modal" id="close-modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <?php if (empty($notifications)): ?>
                                <p style="text-align: center; padding: 20px; color: #666;">Chưa có thông báo nào.</p>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <a href="detail_post.php?id=<?= $notif['prompt_id'] ?>" class="notification-item <?= $notif['isRead'] ? 'read' : 'unread' ?>" data-notification-id="<?= $notif['notification_id'] ?>">
                                        <div><?= htmlspecialchars($notif['message']) ?></div>
                                        <div class="notification-time">từ <?= htmlspecialchars($notif['sender_username']) ?> • <?= date('H:i d/m', strtotime($notif['created_at'])) ?></div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button id="mark-all-read">Đánh dấu đã đọc tất cả</button>
                            <a href="notifications.php">Xem tất cả</a>
                        </div>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="dropbtn">
                        <img src="<?= htmlspecialchars($_SESSION['avatar'] ?? '../../public/img/default-avatar.png') ?>"
                            alt="Ảnh đại diện"
                            class="avatar-image">
                    </button>
                    <div class="dropdown-content">
                        <form action="../user/profile.php" method="post">
                            <input type="submit" value="Xem trang cá nhân">
                        </form>
                        <form action="" method="post">
                            <input type="submit" name="out-btn" value="Thoát">
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <a href="../../views/login/login.php" class="login-btn"><i class="fa-solid fa-right-to-bracket"></i> Đăng nhập</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 1): ?>
                <div>
                    <form action="../manager/account.php" method="post">
                        <button type="submit" title="Đến trang quản lý" class="gear-btn">
                            <i class="fa-solid fa-gears"></i>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bell = document.getElementById('notification-bell');
            const modal = document.getElementById('notification-modal');
            const closeBtn = document.getElementById('close-modal');
            const markAllBtn = document.getElementById('mark-all-read');
            
            // Nếu không có chuông/modal (người dùng chưa đăng nhập), thoát sớm
            if (!bell || !modal) {
                return;
            }

            // Mở modal khi click bell
            bell.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                modal.classList.add('show');
                document.body.style.overflow = 'hidden'; // Ngăn scroll body khi modal mở
            });
            
            // Đóng modal khi click X
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    modal.classList.remove('show');
                    document.body.style.overflow = 'auto'; // Khôi phục scroll
                });
            }
            
            // Đóng khi click backdrop (ngoài content)
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = 'auto';
                }
            });
            
            // Mark all read (AJAX, update UI)
            if (markAllBtn) {
                markAllBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetch('mark_read.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'action=mark_all'
                    }).then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    }).then(data => {
                        if (data.success) {
                            // Update UI ngay
                            document.querySelector('.notification-badge')?.remove();
                            document.getElementById('unread-in-dropdown').textContent = '(0 mới)';
                            document.querySelectorAll('.notification-item.unread').forEach(item => {
                                item.classList.remove('unread');
                                item.classList.add('read');
                            });
                            modal.classList.remove('show');
                            document.body.style.overflow = 'auto';
                        } else {
                            alert('Lỗi đánh dấu đã đọc: ' + (data.message || 'Unknown'));
                        }
                    }).catch(err => {
                        console.error('Error marking read:', err);
                        alert('Lỗi kết nối: ' + err.message);
                    });
                });
            }
            
            // Mark single khi click item 
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    const notifId = this.dataset.notificationId;
                    if (notifId) {
                        fetch('mark_read.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=mark_single&id=${notifId}`
                        }).then(response => response.json()).then(data => {
                            if (data.success) {
                                this.classList.remove('unread');
                                this.classList.add('read');
                            }
                        }).catch(err => console.error('Error marking single:', err));
                    }
                });
            });
        });
    </script>
</body>
</html>