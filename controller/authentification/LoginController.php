<?php
    require_once __DIR__ . '/ErrorController.php';

    function handleLogin($conn){
        cleanupExpiredAccounts($conn);
        if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["btnlogin"])) {
            $email = $_POST['email'];
            $password_input = $_POST['password'];
            $sql = "SELECT account_id, username, avatar, bg_avatar, role_id, password, token 
                    FROM account WHERE email = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $stmt->store_result();
                    // Thông báo chung cho mọi lỗi đăng nhập
                    $COMMON_ERROR = "Email hoặc mật khẩu không đúng.";
                    if ($stmt->num_rows == 1) {
                        $account_id = "";
                        $username = "";
                        $avatar = "";
                        $bg_avatar = "";
                        $role = "";
                        $hashed_password_from_db = "";
                        $token = null;
                        $stmt->bind_result(
                            $account_id,
                            $username,
                            $avatar,
                            $bg_avatar,
                            $role,
                            $hashed_password_from_db,
                            $token
                        );
                        if ($stmt->fetch()) {
                            if (!password_verify($password_input, $hashed_password_from_db)) {
                                sendLoginErrors(null, $COMMON_ERROR); 
                                return;
                            }
                            // Tài khoản chưa kích hoạt
                            if ($token !== null) {
                                $_SESSION['inactive_error'] = "Tài khoản của bạn chưa được kích hoạt. Vui lòng kiểm tra email.";
                                $_SESSION['login_email_attempt'] = $email;
                                header("Location: ../../views/login/login.php");
                                exit;
                            }
                            //Đăng nhập thành công
                            unset($_SESSION['email_error']);
                            unset($_SESSION['password_error']);
                            unset($_SESSION['login_email_attempt']);
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_user"] = $account_id;
                            $_SESSION["name_user"] = $username;
                            $_SESSION["avatar"] = $avatar;
                            $_SESSION["bg_avatar"] = $avatar;
                            $_SESSION["role"] = $role;
                            header("Location: ../../views/user/home.php");
                            exit;
                        }
                    } else {
                        sendLoginErrors($COMMON_ERROR, null); 
                    }
                } else {
                    sendLoginErrors("Đã xảy ra lỗi. Vui lòng thử lại.", null);
                }
                $stmt->close();
            }
            $conn->close();
        }
    }
?>
