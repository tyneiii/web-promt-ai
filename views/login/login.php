<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <!-- Link đến file CSS chung cho auth -->
    <link rel="stylesheet" href="../../public/css/auth/auth.css">
    <!-- Font Awesome (dùng cho icon Google và con mắt) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php
        // THÊM MỚI: Logic hiển thị TOAST
        if (isset($_SESSION['register_success'])) {
            echo '<div id="toast-success" class="toast toast-success">';
            echo '<span><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['register_success']) . '</span>';
            echo '</div>';
            unset($_SESSION['register_success']);
        }
        if (isset($_SESSION['activate_success'])) {
            echo '<div class="success-message">' . htmlspecialchars($_SESSION['activate_success']) . '</div>';
            unset($_SESSION['activate_success']);
        }
        // THÊM MỚI: Hiển thị thông báo lỗi kích hoạt (nếu có)
        if (isset($_SESSION['activate_error'])) {
            // Dùng class error-message cũ
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['activate_error']) . '</div>';
            unset($_SESSION['activate_error']);
        }
    ?>
    <a href="../user/home.php" class="back-home-btn" title="Về trang chủ">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="auth-container">
        <div class="form-card">
            <h2>Đăng nhập</h2>
            <p class="subtitle">Chào mừng trở lại! Vui lòng nhập thông tin của bạn.</p>

            <?php
            // Lấy email đã nhập (nếu có)
            $email_attempt = '';
            if (isset($_SESSION['login_email_attempt'])) {
                $email_attempt = htmlspecialchars($_SESSION['login_email_attempt']);
                unset($_SESSION['login_email_attempt']); // Xóa đi sau khi dùng
            }

            // Lấy lỗi (nếu có)
            $email_error = isset($_SESSION['email_error']) ? $_SESSION['email_error'] : null;
            $password_error = isset($_SESSION['password_error']) ? $_SESSION['password_error'] : null;
            ?>

            <!-- Form Đăng nhập -->
            <form action="../../controller/authentification/AuthController.php?action=login" method="post">
                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder="Email" value="<?php echo $email_attempt; ?>" class="<?php echo $email_error ? 'input-error' : ''; ?>" required>
                    <?php
                    // Hiển thị lỗi email ngay dưới input
                    if ($email_error) {
                        echo '<div class="input-error-message">' . htmlspecialchars($email_error) . '</div>';
                        unset($_SESSION['email_error']);
                    }
                    ?>
                </div>

                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Password" class="<?php echo $password_error ? 'input-error' : ''; ?>" required>
                  
                    <?php
                    // Hiển thị lỗi password ngay dưới input
                    if ($password_error) {
                        echo '<div class="input-error-message">' . htmlspecialchars($password_error) . '</div>';
                        unset($_SESSION['password_error']);
                    }
                    ?>
                </div>

                <div class="form-options">
                     <label for="showPassword" class="show-password-label">
                        <input type="checkbox" id="showPassword"> Hiển thị mật khẩu
                    </label>

                    <a href="forgot_password.php" class="forgot-password">Quên mật khẩu?</a>
                   
                </div>

                <button type="submit" class="auth-btn" name ="btnlogin">Đăng nhập</button>
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

    <!-- SCRIPT XỬ LÝ HIỂN THỊ MẬT KHẨU -->
    <script>
        const toast = document.getElementById('toast-success');
        if (toast) {
            // 1. Hiển thị toast (thêm class 'show')
            setTimeout(() => {
                toast.classList.add('show');
            }, 100); // Delay 100ms để transition kích hoạt

            // 2. Tự động ẩn sau 5 giây (5000ms)
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5100); // 100ms delay + 5000ms hiển thị
        }
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            // const toggleIcon = document.getElementById('togglePassword'); // ĐÃ XÓA
            const showPasswordCheckbox = document.getElementById('showPassword'); // MỚI

            if (showPasswordCheckbox && passwordInput) { // CẬP NHẬT ĐIỀU KIỆN
                showPasswordCheckbox.addEventListener('change', function() { // ĐỔI SANG CHECKBOX VÀ SỰ KIỆN 'CHANGE'
                    // Lấy trạng thái type hiện tại của input
                    // const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    // passwordInput.setAttribute('type', type);

                    // Logic mới dựa trên checkbox
                    if (this.checked) {
                        passwordInput.setAttribute('type', 'text');
                    } else {
                        passwordInput.setAttribute('type', 'password');
                    }

                });
            }
        });
    </script>
</body>

</html>