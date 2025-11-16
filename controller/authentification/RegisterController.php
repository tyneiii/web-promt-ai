<?php
require_once __DIR__ . '/ErrorController.php';
require_once __DIR__ . '/MailServiceController.php'; 

function handleRegister($conn)
{
    if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["btnregister"])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $repeat_password = $_POST['repeat_password'];

        $errors = [];
        $inputs = ['username' => $username, 'email' => $email];
        // Kiểm tra mật khẩu có khớp không
        if ($password !== $repeat_password) {
            $errors['repeat_password'] = "Mật khẩu nhập lại không khớp.";
        }
        //  Kiểm tra email đã tồn tại chưa
        $sql_check = "SELECT account_id FROM account WHERE email = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $errors['email'] = "Email này đã được đăng ký.";
            }
            $stmt_check->close();
        }
        // kiểm tra username đã tồn tại chưa
        $sql_check_user = "SELECT account_id FROM account WHERE username = ?";
        if ($stmt_check_user = $conn->prepare($sql_check_user)) {
            $stmt_check_user->bind_param("s", $username);
            $stmt_check_user->execute();
            $stmt_check_user->store_result();
            if ($stmt_check_user->num_rows > 0) {
                $errors['username'] = "Username này đã tồn tại.";
            }
            $stmt_check_user->close();
        }
        // Nếu có lỗi, quay lại trang đăng ký
        if (!empty($errors)) {
            sendRegisterError($errors, $inputs);
            exit;
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(50));
        $role_id = 2;
        $create_at = date('Y-m-d H:i:s');
        
        // ⭐️ THAY ĐỔI: Gán đường dẫn avatar mặc định
        $avatar = "../../public/img/default_avatar.png";
        // ⭐️ THAY ĐỔI: Thêm `avatar` vào câu lệnh INSERT
        $sql_insert = "INSERT INTO account (username, email, password, role_id, token, create_at, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)";
        try {
            if ($stmt_insert = $conn->prepare($sql_insert)) {
                
                // ⭐️ THAY ĐỔI: Thêm `avatar` vào bind_param ("sssiss" -> "sssiss**s**")
                $stmt_insert->bind_param("sssisss", $username, $email, $hashed_password, $role_id, $token, $create_at, $avatar);

                if ($stmt_insert->execute()) {
                    // 5. Gửi email kích hoạt
                    sendActivationEmail($email, $token);

                    $_SESSION['register_success'] = "Đăng ký thành công! Vui lòng kiểm tra email để kích hoạt tài khoản.";
                    header("Location: ../../views/login/login.php");
                } else {
                    $errors['general'] = "Đã xảy ra lỗi khi đăng ký. Vui lòng thử lại. Lỗi SQL: " . $conn->error;
                    sendRegisterError($errors, $inputs);
                }
                $stmt_insert->close();
            } else {
                $errors['general'] = "Lỗi hệ thống (Prepare failed). Vui lòng thử lại sau.";
                sendRegisterError($errors, $inputs);
            }
        } catch (Exception $e) {
            // Bắt lỗi từ sendActivationEmail
            $sql_delete = "DELETE FROM account WHERE email = ?";
            if ($stmt_delete = $conn->prepare($sql_delete)) {
                $stmt_delete->bind_param("s", $email);
                $stmt_delete->execute();
                $stmt_delete->close();
            }

            $errors['general'] = "Không thể gửi email kích hoạt. Vui lòng thử lại. Lỗi: " . $e->getMessage();
            sendRegisterError($errors, $inputs);
        }
        $conn->close();
        exit;
    }
}

function handleActivate($conn)
{
    if (isset($_GET['token'])) {
        $token = $_GET['token'];
        $sql_find = "SELECT account_id, create_at FROM account WHERE token = ?";
        if ($stmt_find = $conn->prepare($sql_find)) {
            $stmt_find->bind_param("s", $token);
            $stmt_find->execute();
            $stmt_find->store_result();

            if ($stmt_find->num_rows == 1) {
                $account_id = 0;
                $registration_time_str = '';
                $stmt_find->bind_result($account_id, $registration_time_str);
                $stmt_find->fetch();
                $stmt_find->close();
                // KIỂM TRA THỜI GIAN HẾT HẠN (30 phút)
                $registration_time = new DateTime($registration_time_str);
                $current_time = new DateTime();
                $interval_seconds = $current_time->getTimestamp() - $registration_time->getTimestamp();
                $minutes_passed = $interval_seconds / 60;
                if ($minutes_passed > 30) {
                    $sql_delete = "DELETE FROM account WHERE account_id = ?";
                    if ($stmt_delete = $conn->prepare($sql_delete)) {
                        $stmt_delete->bind_param("i", $account_id);
                        $stmt_delete->execute();
                        $stmt_delete->close();
                    }
                    $_SESSION['activate_error'] = "Link kích hoạt đã hết hạn (quá 30 phút). Vui lòng đăng ký lại.";
                    header("Location: ../../views/login/login.php");
                    exit;
                } else {
                    // CHƯA HẾT HẠN (trong vòng 30 phút)
                    $sql_update = "UPDATE account SET token = NULL WHERE token = ?";
                    if ($stmt_update = $conn->prepare($sql_update)) {
                        $stmt_update->bind_param("s", $token);
                        $stmt_update->execute();
                        $stmt_update->close();

                        $_SESSION['activate_success'] = "Kích hoạt thành công! Vui lòng đăng nhập vào tài khoản của bạn.";
                        header("Location: ../../views/login/login.php");
                        exit;
                    }
                }
            } else {
                $stmt_find->close();
                $_SESSION['activate_error'] = "Token kích hoạt không hợp lệ hoặc đã hết hạn.";
                header("Location: ../../views/login/login.php");
                exit;
            }
        }
        $conn->close();
    } else {
        // Không có token
        header("Location: ../../views/login/login.php");
        exit;
    }
}
?>