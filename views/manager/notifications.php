<?php
include_once __DIR__ . '/../../config.php';
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông báo quản trị</title>
    <link rel="stylesheet" href="../../public/css/manager/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<div class="container">

    <?php include_once __DIR__ . '/layout/sidebar.php'; ?>

    <div class="main">

        <h2>Thông báo quản trị</h2>

        <?php
        $notifs = $conn->query("
            SELECT * FROM admin_notifications
            ORDER BY created_at DESC
        ")->fetch_all(MYSQLI_ASSOC);

        $conn->query("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
        ?>

        <?php if(empty($notifs)): ?>
            <p>Không có thông báo nào.</p>
        <?php else: ?>
            <?php foreach($notifs as $n): ?>
                <div class="notification-item" style="padding:12px; border-bottom:1px solid #555;">
                    <a href="prompt_detail.php?id=<?= $n['prompt_id'] ?>" style="color:white;">
                        <?= htmlspecialchars($n['message']) ?> • 
                        <?= date("H:i d/m/Y", strtotime($n['created_at'])) ?>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</div>
</body>
</html>
