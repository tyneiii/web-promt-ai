<?php
    include_once __DIR__ . '/../../config.php';
    $token = $_GET['token'] ?? '';
    $isValidToken = false;

    if ($token) {
        $current_time = date("Y-m-d H:i:s");
        $sql = "SELECT account_id FROM account WHERE token = ? AND token_expiry > ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $token, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $isValidToken = true;
        }
    }

    $password_error = isset($_SESSION['password_error']) ? $_SESSION['password_error'] : null;
    $system_error = isset($_SESSION['error']) ? $_SESSION['error'] : null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu</title>
    <link rel="stylesheet" href="../../public/css/auth/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php if ($password_error || $system_error): ?>
    <div class="toast toast-error show">
        <span><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($password_error ?? $system_error); ?></span>
    </div>
    <?php 
        unset($_SESSION['password_error']); 
        unset($_SESSION['error']);
    endif; 
    ?>

    <div class="auth-container">
        <div class="form-card">
            <h2>Đặt Lại Mật Khẩu</h2>
            
            <?php if ($isValidToken): ?>
                <p class="subtitle">Nhập mật khẩu mới cho tài khoản của bạn.</p>
                <form action="../../controller/authentification/AuthController.php?action=reset_password" method="post">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="input-group">
                        <input type="password" name="password" placeholder="Mật khẩu mới" required minlength="6">
                    </div>
                    <div class="input-group">
                        <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required minlength="6">
                    </div>
                    
                    <button type="submit" class="auth-btn" name="reset">Đổi mật khẩu</button>
                </form>
            <?php else: ?>
                <div style="text-align: center; color: var(--primary-color);">
                    <i class="fas fa-times-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <p>Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.</p>
                    <br>
                    <a href="forgot_password.php" class="auth-btn" style="display: inline-block; text-decoration: none;">Yêu cầu link mới</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        setTimeout(() => {
            const toast = document.querySelector('.toast');
            if(toast) toast.classList.remove('show');
        }, 5000);
    </script>
</body>
</html>