<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="../../public/css/auth/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php
        if (isset($_SESSION['register_success'])) {
            echo '<div id="toast-success" class="toast toast-success">';
            echo '<span><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['register_success']) . '</span>';
            echo '</div>';
            unset($_SESSION['register_success']);
        }
        if (isset($_GET['require_login']) && $_GET['require_login'] === 'favorites') {
            echo '<div class="toast toast-error">'; 
            echo '<span><i class="fas fa-exclamation-circle"></i> Vui lòng đăng nhập để xem danh sách yêu thích!</span>';
            echo '</div>';
        }
        if (isset($_SESSION['inactive_error'])) {
            echo '<div class="toast toast-error">'; 
            echo '<span><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['inactive_error']) . '</span>'; // Icon lỗi
            echo '</div>';
            unset($_SESSION['inactive_error']);
        }
        if (isset($_SESSION['activate_success'])) {
            echo '<div id="toast-success" class="toast toast-success">';
            echo '<span><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['activate_success']) . '</span>';
            echo '</div>';
            unset($_SESSION['activate_success']);
        }
        if (isset($_SESSION['activate_error'])) {
            echo '<div class="toast toast-error">'; 
            echo '<span><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['activate_error']) . '</span>';
            echo '</div>';
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
            $email_attempt = '';
            if (isset($_SESSION['login_email_attempt'])) {
                $email_attempt = htmlspecialchars($_SESSION['login_email_attempt']);
                unset($_SESSION['login_email_attempt']); 
            }
            $email_error = isset($_SESSION['email_error']) ? $_SESSION['email_error'] : null;
            $password_error = isset($_SESSION['password_error']) ? $_SESSION['password_error'] : null;
            ?>
            <form action="../../controller/authentification/AuthController.php?action=login" method="post">
                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder="Email" value="<?php echo $email_attempt; ?>" class="<?php echo $email_error ? 'input-error' : ''; ?>" required>
                    <?php
                    if ($email_error) {
                        echo '<div class="input-error-message">' . htmlspecialchars($email_error) . '</div>';
                        unset($_SESSION['email_error']);
                    }
                    ?>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Password" class="<?php echo $password_error ? 'input-error' : ''; ?>" required>
                    <?php
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
            <a href="../../controller/authentification/GoogleController.php" style="text-decoration: none; width: 100%;">
                <button type="button" class="google-btn">
                    <i class="fab fa-google"></i>
                    <span>Đăng nhập với Google</span>
                </button>
            </a>
            <p class="switch-auth">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </p>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allToasts = document.querySelectorAll('.toast');
            allToasts.forEach((toast, index) => {
                // Hiển thị toast
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100 * (index + 1)); 
                setTimeout(() => {
                    toast.classList.remove('show');
                    // Xóa hẳn khỏi DOM sau khi mờ đi
                     setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 500); 
                }, 5100 * (index + 1));
            });
            const passwordInput = document.getElementById('password');
            const showPasswordCheckbox = document.getElementById('showPassword'); 

            if (showPasswordCheckbox && passwordInput) {
                showPasswordCheckbox.addEventListener('change', function() {
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