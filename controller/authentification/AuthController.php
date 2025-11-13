<?php
    session_start();

    require_once __DIR__ . '/../../config.php';

    if (isset($_GET["action"])) {
        switch ($_GET['action']) {
            case 'login':
                handleLogin($conn);
                break;
            case 'register':
                handleRegister($conn); // THÊM MỚI
            break;
            case 'activate':
             handleActivate($conn); // THÊM MỚI
            break;
            default:
                header("Location: ../../views/user/home.php");
                exit;
        }
    }

    /**
     * Xử lý logic đăng nhập
     */
    function handleLogin($conn) {
        if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["btnlogin"])) {
            $email = $_POST['email'];
            $password_input = $_POST['password'];

            $sql = "SELECT account_id, username, password FROM account WHERE email = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $email);
                
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows == 1) {
                        $account_id = "";
                        $username = "";
                        $hashed_password_from_db = "";
                        $token = "";

                        $stmt->bind_result($account_id, $username, $hashed_password_from_db);
                        
                        if ($stmt->fetch()) {
                            if (password_verify($password_input, $hashed_password_from_db)) {
                            if ($token !== "" ) {
                                sendLoginErrors("Tài khoản của bạn chưa được kích hoạt. Vui lòng kiểm tra email.", null);
                                exit;
                            }
                                unset($_SESSION['email_error']);
                                unset($_SESSION['password_error']);
                                unset($_SESSION['login_email_attempt']);

                                $_SESSION["loggedin"] = true;
                                $_SESSION["id_user"] = $account_id;
                                $_SESSION["name_user"] = $username;
                                header("Location: ../../views/user/home.php");
                                exit;
                            } else {
                                sendLoginErrors(null, "Mật khẩu không đúng.");
                            }
                        }
                    } else {
                        // Không tìm thấy email
                        sendLoginErrors("Email này không tồn tại hoặc không đúng.", null);
                    }
                } else {
                    // Lỗi chung (ít khi xảy ra)
                    sendLoginErrors("Đã xảy ra lỗi. Vui lòng thử lại.", null);
                }
                $stmt->close();
            }
            $conn->close();
        }
    }

    function handleRegister($conn) {
        if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["btnregister"])) {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $repeat_password = $_POST['repeat_password'];

            $errors = [];
            $inputs = ['username' => $username, 'email' => $email]; // Giữ lại giá trị

            // 1. Kiểm tra mật khẩu có khớp không
            if ($password !== $repeat_password) {
                $errors['repeat_password'] = "Mật khẩu nhập lại không khớp.";
            }

            // 2. Kiểm tra email đã tồn tại chưa
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

            // Nếu có lỗi, quay lại trang đăng ký
            if (!empty($errors)) {
                sendRegisterError($errors, $inputs);
                exit;
            }
            
            // HASH MẬT KHẨU (Bảo mật)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // TẠO TOKEN KÍCH HOẠT (unique)
            $token = bin2hex(random_bytes(50)); // Tạo token ngẫu nhiên
            
            $role_id = 2; 
            
            $create_at = date('Y-m-d H:i:s');

            // 4. Lưu vào CSDL
            $sql_insert = "INSERT INTO account (username, email, password, role_id, token, create_at) VALUES (?, ?, ?, ?, ?, ?)";
            
            if ($stmt_insert = $conn->prepare($sql_insert)) {
                $stmt_insert->bind_param("sssiss", $username, $email, $hashed_password, $role_id, $token, $create_at);
                
                if ($stmt_insert->execute()) {
                    // 5. Gửi email kích hoạt
                    // sendActivationEmail($email, $token); 

                    $_SESSION['register_success'] = "Đăng ký thành công! Vui lòng kiểm tra email để kích hoạt tài khoản.";
                    header("Location: ../../views/login/login.php");
                    exit;

                } else {
                    $errors['general'] = "Đã xảy ra lỗi khi đăng ký. Vui lòng thử lại.";
                    sendRegisterError($errors, $inputs);
                }
                $stmt_insert->close();
            }
            $conn->close();
        }
    }

   
    function handleActivate($conn) {
        if (isset($_GET['token'])) {
            $token = $_GET['token'];

            // CẬP NHẬT: Lấy thêm create_at để kiểm tra thời gian
            $sql_find = "SELECT account_id, create_at FROM account WHERE token = ?";
            if ($stmt_find = $conn->prepare($sql_find)) {
                $stmt_find->bind_param("s", $token);
                $stmt_find->execute();
                $stmt_find->store_result();

                if ($stmt_find->num_rows == 1) {
                    // Gán kết quả
                    $account_id = 0;
                    $registration_time_str = '';
                    $stmt_find->bind_result($account_id, $registration_time_str);
                    $stmt_find->fetch(); // Lấy dữ liệu
                    $stmt_find->close(); // Đóng sớm

                    // KIỂM TRA THỜI GIAN HẾT HẠN (30 phút)
                    $registration_time = new DateTime($registration_time_str);
                    $current_time = new DateTime();
                    
                    // Tính khoảng chênh lệch (tính bằng giây)
                    $interval_seconds = $current_time->getTimestamp() - $registration_time->getTimestamp();
                    $minutes_passed = $interval_seconds / 60;

                    if ($minutes_passed > 30) {
                        // ĐÃ HẾT HẠN (quá 30 phút)
                        
                        // Xóa tài khoản chưa được kích hoạt để user có thể đăng ký lại
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
                        
                        // Tìm thấy, kích hoạt tài khoản bằng cách set token = NULL
                        $sql_update = "UPDATE account SET token = NULL WHERE token = ?";
                        if ($stmt_update = $conn->prepare($sql_update)) {
                            $stmt_update->bind_param("s", $token);
                            $stmt_update->execute();
                            $stmt_update->close();

                            // Kích hoạt thành công
                            $_SESSION['activate_success'] = "Kích hoạt tài khoản thành công! Bạn có thể đăng nhập ngay bây giờ.";
                            header("Location: ../../views/login/login.php");
                            exit;
                        }
                    }

                } else {
                    // Token không hợp lệ (hoặc đã được kích hoạt rồi)
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


    function sendLoginErrors($email_error, $password_error) {
        // Xóa lỗi cũ trước
        unset($_SESSION['email_error']);
        unset($_SESSION['password_error']);

        if ($email_error) {
            $_SESSION['email_error'] = $email_error;
        }
        if ($password_error) {
            $_SESSION['password_error'] = $password_error;
        }

        // Lưu lại email đã nhập 
        if (isset($_POST['email'])) {
            $_SESSION['login_email_attempt'] = $_POST['email'];
        }
        
        // Chuyển hướng về trang login
        header("Location: ../../views/login/login.php");
        exit;
    }
    function sendRegisterError($errors, $inputs) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_inputs'] = $inputs;
        header("Location: ../../views/login/register.php");
        exit;
    }
?>