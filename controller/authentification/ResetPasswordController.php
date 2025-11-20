<?php
    require_once __DIR__ . '/ErrorController.php';
    require_once __DIR__ . '/MailServiceController.php';
    
    function handleForgotPassword($conn) {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot'])) {
            $email = trim($_POST['email']);
            $sql = "SELECT account_id FROM account WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $token = bin2hex(random_bytes(32)); 
                $expiry = date("Y-m-d H:i:s", strtotime('+15 minutes'));
                $updateSql = "UPDATE account SET token = ?, token_expiry = ? WHERE email = ?";
                $stmtUpdate = $conn->prepare($updateSql);
                $stmtUpdate->bind_param("sss", $token, $expiry, $email);
                
                if ($stmtUpdate->execute()) {
                    try {
                        sendResetPasswordEmail($email, $token);
                        $_SESSION['activate_success'] = "Link đặt lại mật khẩu đã được gửi vào email của bạn.";
                        header("Location: ../../views/login/login.php");
                        exit;

                    } catch (Exception $e) {
                        $_SESSION['email_error'] = "Lỗi gửi mail: " . $e->getMessage();
                        header("Location: ../../views/login/forgot_password.php");
                        exit;
                    }
                } else {
                     $_SESSION['email_error'] = "Lỗi cơ sở dữ liệu. Vui lòng thử lại.";
                }
            } else {
                $_SESSION['email_error'] = "Email này không tồn tại trong hệ thống.";
            }
            header("Location: ../../views/login/forgot_password.php");
            exit;
        }
    }
    
    function handleResetPassword($conn) {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
            $token = $_POST['token'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                $_SESSION['password_error'] = "Mật khẩu xác nhận không khớp.";
                header("Location: ../../views/login/reset_password.php?token=" . $token);
                exit;
            }
            // 1. Kiểm tra token và hạn sử dụng
            $current_time = date("Y-m-d H:i:s");
            $sql = "SELECT account_id FROM account WHERE token = ? AND token_expiry > ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $token, $current_time);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // 2. Cập nhật mật khẩu mới và xóa token
                $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $updateSql = "UPDATE account SET password = ?, token = NULL, token_expiry = NULL WHERE token = ?";
                $stmtUpdate = $conn->prepare($updateSql);
                $stmtUpdate->bind_param("ss", $new_password_hash, $token);
                
                if ($stmtUpdate->execute()) {
                    $_SESSION['activate_success'] = "Đổi mật khẩu thành công! Vui lòng đăng nhập.";
                    header("Location: ../../views/login/login.php");
                } else {
                    $_SESSION['error'] = "Lỗi hệ thống, vui lòng thử lại.";
                    header("Location: ../../views/login/reset_password.php?token=" . $token);
                }
            } else {
                $_SESSION['error'] = "Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.";
                header("Location: ../../views/login/forgot_password.php");
            }
            exit;
        }
    }
?>