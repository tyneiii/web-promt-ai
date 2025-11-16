<?php
// Chỉ xử lý logic đăng nhập
require_once __DIR__ . '/ErrorController.php';

function handleLogin($conn)
{
    if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["btnlogin"])) {

        $email = $_POST['email'];
        $password_input = $_POST['password'];

        $sql = "SELECT account_id, username, avatar, role_id, password, token FROM account WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $account_id = "";
                    $username = "";
                    $avatar = "";
                    $role = "";
                    $hashed_password_from_db = "";

                    $token = null;

                    $stmt->bind_result($account_id, $username, $avatar, $role, $hashed_password_from_db, $token);

                    if ($stmt->fetch()) {
                        if (password_verify($password_input, $hashed_password_from_db)) {
                            if ($token !== null) {
                                $_SESSION['inactive_error'] = "Tài khoản của bạn chưa được kích hoạt. Vui lòng kiểm tra email.";
                                if (isset($_POST['email'])) {
                                    $_SESSION['login_email_attempt'] = $_POST['email'];
                                }
                                header("Location: ../../views/login/login.php");
                                exit;
                            }
                            // Đăng nhập thành công
                            unset($_SESSION['email_error']);
                            unset($_SESSION['password_error']);
                            unset($_SESSION['login_email_attempt']);

                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_user"] = $account_id;
                            $_SESSION["name_user"] = $username;
                            $_SESSION["avatar"] = $avatar;
                            // FIX avatar NULL → dùng ảnh mặc định
                            // $_SESSION["avatar"] = !empty($avatar)
                            //     ? "../../public/img/" . $avatar
                            //     : "../../public/img/default-avatar.png";

                            $_SESSION["role"] = $role;
                            header("Location: ../../views/user/home.php");
                            exit;
                        } else {
                            sendLoginErrors(null, "Mật khẩu không đúng.");
                        }
                    }
                } else {
                    sendLoginErrors("Email này không tồn tại hoặc không đúng.", null);
                }
            } else {
                sendLoginErrors("Đã xảy ra lỗi. Vui lòng thử lại.", null);
            }
            $stmt->close();
        }
        $conn->close();
    }
}
