<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <!-- Link đến file CSS chung cho auth -->
    <link rel="stylesheet" href="../../public/css/auth/auth.css">
    <!-- Font Awesome (dùng cho icon Google) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="auth-container">
        <div class="form-card">
            <h2>Đăng nhập</h2>
            <p class="subtitle">Chào mừng trở lại! Vui lòng nhập thông tin của bạn.</p>

            <!-- Form Đăng nhập -->
            <form action="../../controller/AuthController.php?action=login" method="POST">
                <div class="input-group">
                    <!-- <label for="email">Email</label> -->
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-group">
                    <!-- <label for="password">Mật khẩu</label> -->
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>

                <div class="form-options">
                    <a href="forgot_password.php" class="forgot-password">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="auth-btn">Đăng nhập</button>
            </form>

            <div class="divider">
                <span>hoặc</span>
            </div>

            <!-- Đăng nhập với Google -->
            <button type="button" class="google-btn">
                <i class="fab fa-google"></i>
                <span>Đăng nhập với Google</span>
            </button>

            <!-- Chuyển sang trang Đăng ký -->
            <p class="switch-auth">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </p>
        </div>
    </div>
</body>

</html>