<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <!-- Link đến file CSS chung cho auth -->
    <link rel="stylesheet" href="../../public/css/auth/auth.css">
    <!-- Font Awesome (dùng cho icon Google) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="auth-container">
        <div class="form-card">
            <h2>Tạo tài khoản</h2>
            <p class="subtitle">Bắt đầu hành trình của bạn bằng cách tạo tài khoản.</p>

            <!-- Form Đăng ký -->
            <form action="../../controller/AuthController.php?action=register" method="POST">
                <div class="input-group">
                    <!-- <label for="username">Tên người dùng</label> -->
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>

                <div class="input-group">
                    <!-- <label for="email">Email</label> -->
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>

                <div class="input-group">
                    <!-- <label for="password">Mật khẩu</label> -->
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>

                <div class="input-group">
                    <!-- <label for="repeat_password">Nhập lại mật khẩu</label> -->
                    <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat password" required>
                </div>

                <button type="submit" class="auth-btn">Đăng ký</button>
            </form>

            <div class="divider">
                <span>hoặc</span>
            </div>

            <!-- Đăng ký với Google -->
            <button type="button" class="google-btn">
                <i class="fab fa-google"></i>
                <span>Đăng ký với Google</span>
            </button>

            <!-- Chuyển sang trang Đăng nhập -->
            <p class="switch-auth">
                Đã có tài khoản? <a href="login.php">Đăng nhập</a>
            </p>
        </div>
    </div>
</body>

</html>