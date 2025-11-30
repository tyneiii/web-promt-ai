<?php
include_once __DIR__ . '/layout/header.php';
include_once __DIR__ . '/../../Controller/user/prompt.php';
include_once __DIR__ . '/../../Controller/user/notifications.php';

$account_id = $_SESSION['account_id'] ?? 0;
if (!$account_id) {
    header("Location: login/login.php");
    exit;
}

$notifications = getNotifications($account_id, $conn, 20);  // Lấy 20
markAsRead($account_id, $conn);  // Tự động mark all khi vào trang
?>

<div class="main-content">
    <h2>Thông báo của bạn</h2>
    <?php if (empty($notifications)): ?>
        <p>Chưa có thông báo nào.</p>
    <?php else: ?>
        <?php foreach ($notifications as $notif): ?>
            <div class="notification-item <?= $notif['isRead'] ? 'read' : 'unread' ?>">
                <a href="detail_post.php?id=<?= $notif['prompt_id'] ?>">
                    <?= htmlspecialchars($notif['message']) ?> từ <?= htmlspecialchars($notif['sender_username']) ?> • <?= date('H:i d/m/Y H:i', strtotime($notif['created_at'])) ?>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/layout/footer.php'; ?>