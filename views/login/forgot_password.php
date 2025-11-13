<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
    <!-- Link đến file CSS chung cho auth -->
    <link rel="stylesheet" href="../../public/css/auth/auth.css">
    <!-- Font Awesome (dùng cho icon Google và con mắt) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <!-- Nút quay về trang chủ -->
    <a href="../login/login.php" class="back-home-btn" title="Về trang chủ">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div class="auth-container">
        <div class="form-card">
            <h2>Quên Mật Khẩu</h2>

            <form action="../../controller/authentification/AuthController.php?action=login" method="post">
                <div class="input-group">
                    <!-- Thêm value và class lỗi -->
                    <input type="email" id="email" name="email" placeholder="Email"  class="<?php echo $email_error ? 'input-error' : ''; ?>" required>
                    
                </div>

                <button type="submit" class="auth-btn" name="forgot">Lấy lại mật khẩu</button>
            </form>

           
    </div>

</body>

</html>