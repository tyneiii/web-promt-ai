<?php
session_start();

// Lấy lỗi (nếu có)
$errors = isset($_SESSION['register_errors']) ? $_SESSION['register_errors'] : [];
$inputs = isset($_SESSION['register_inputs']) ? $_SESSION['register_inputs'] : [];

// Lấy giá trị cũ, nếu không có thì rỗng
$username_attempt = isset($inputs['username']) ? htmlspecialchars($inputs['username']) : '';
$email_attempt = isset($inputs['email']) ? htmlspecialchars($inputs['email']) : '';

// Lấy lỗi cho từng trường
$username_error = isset($errors['username']) ? $errors['username'] : null;
$email_error = isset($errors['email']) ? $errors['email'] : null;
$password_error = isset($errors['password']) ? $errors['password'] : null;
$repeat_password_error = isset($errors['repeat_password']) ? $errors['repeat_password'] : null;
$general_error = isset($errors['general']) ? $errors['general'] : null;

// Xóa session lỗi sau khi dùng
unset($_SESSION['register_errors']);
unset($_SESSION['register_inputs']);
?>
<!DOCTYPE html>
<html lang="vi">

<head> <link rel="icon" href="../../public/img/T1.png" type="image/png" sizes="180x180">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="../../public/css/auth/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <a href="../login/login.php" class="back-home-btn" title="Về trang đăng nhập">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="auth-container">
        <div class="form-card">
            <h2>Tạo tài khoản</h2>
            <p class="subtitle">Bắt đầu hành trình của bạn bằng cách tạo tài khoản.</p>
            <?php
            if ($general_error) {
                echo '<div class="error-message">' . htmlspecialchars($general_error) . '</div>';
            }
            ?>
            <form action="../../controller/authentification/AuthController.php?action=register" method="post">

                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder="Username" value="<?php echo $username_attempt; ?>" class="<?php echo $username_error ? 'input-error' : ''; ?>" required>
                    <?php
                    // Hiển thị lỗi username
                    if ($username_error) {
                        echo '<div class="input-error-message">' . htmlspecialchars($username_error) . '</div>';
                    }
                    ?>
                </div>

                <div class="input-group">
                    <input type="email" id="email" name="email" placeholder="Email" value="<?php echo $email_attempt; ?>" class="<?php echo $email_error ? 'input-error' : ''; ?>" required>
                    <?php
                    // Hiển thị lỗi email
                    if ($email_error) {
                        echo '<div class="input-error-message">' . htmlspecialchars($email_error) . '</div>';
                    }
                    ?>
                </div>

                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder="Password" class="<?php echo $password_error ? 'input-error' : ''; ?>" required required minlength="6">
                    <?php
                    // Hiển thị lỗi password
                    if ($password_error) {
                        echo '<div class="input-error-message">' . htmlspecialchars($password_error) . '</div>';
                    }
                    ?>
                </div>

                <div class="input-group">
                    <input type="password" id="repeat_password" name="repeat_password" placeholder="Repeat password" class="<?php echo $repeat_password_error ? 'input-error' : ''; ?>" required required minlength="6">
                    <?php
                    // Hiển thị lỗi repeat_password
                    if ($repeat_password_error) {
                        echo '<div class="input-error-message">' . htmlspecialchars($repeat_password_error) . '</div>';
                    }
                    ?>
                </div>

                <button type="submit" class="auth-btn" name="btnregister">Đăng ký</button>
            </form>

            <div class="divider">
                <span>hoặc</span>
            </div>

            <!-- Đăng ký với Google -->
            <a href="../../controller/authentification/GoogleController.php" style="text-decoration: none; width: 100%;">
                <button type="button" class="google-btn">
                    <i class="fab fa-google"></i>
                    <span>Đăng ký với Google</span>
                </button>
            </a>

            <!-- Chuyển sang trang Đăng nhập -->
            <p class="switch-auth">
                Đã có tài khoản? <a href="login.php">Đăng nhập</a>
            </p>
        </div>
    </div>
</body>

</html>