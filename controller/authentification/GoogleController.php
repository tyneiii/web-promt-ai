<?php
    include_once __DIR__ . '/../../config.php';
    include_once __DIR__ . "/../../vendor/autoload.php";

    $client = new Google_Client();
    $client->setClientId($clientID);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($redirectUri);
    $client->addScope("email");
    $client->addScope("profile");

    if (isset($_GET['code'])) {
        // 1. Google trả về code, đổi code lấy token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (!isset($token['error'])) {
            $client->setAccessToken($token['access_token']);

            // 2. Lấy thông tin user từ Google
            $google_oauth = new Google_Service_Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();
            
            $google_id = $google_account_info->id;
            $email = $google_account_info->email;
            $name = $google_account_info->name;
            $avatar = $google_account_info->picture;

            // 3. Kiểm tra xem user này đã tồn tại trong DB chưa?
            // Ưu tiên check theo google_id trước, sau đó check theo email
            $sql = "SELECT * FROM account WHERE google_id = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $google_id, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                // Nếu chưa có google_id (user cũ đăng ký bằng email thường), thì cập nhật thêm google_id
                if (empty($user['google_id'])) {
                    $updateSql = "UPDATE account SET google_id = ?, avatar = ? WHERE account_id = ?";
                    $stmtUpdate = $conn->prepare($updateSql);
                    $stmtUpdate->bind_param("ssi", $google_id, $avatar, $user['account_id']);
                    $stmtUpdate->execute();
                }
                $_SESSION['id_user'] = $user['account_id'];
                $_SESSION['name_user'] = $user['username'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['avatar'] = $user['avatar'];
                header("Location: ../../views/user/home.php");
                exit;

            } else {
                $username_base = explode('@', $email)[0];
                $username = $username_base;
                $checkUserSql = "SELECT account_id FROM account WHERE username = ?";
                $stmtCheck = $conn->prepare($checkUserSql);
                $stmtCheck->bind_param("s", $username);
                $stmtCheck->execute();
                if ($stmtCheck->get_result()->num_rows > 0) {
                    $username = $username_base . rand(100, 999);
                }
                $role_id = 2;
                $create_at = date('Y-m-d H:i:s');
                $avatar = "default_avatar.png";
                $bg_avatar = "bg.png"; 

                $insertSql = "INSERT INTO account (username, email, google_id, fullname, avatar, role_id, create_at, bg_avatar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                
                if ($stmtInsert = $conn->prepare($insertSql)) {
                    $stmtInsert->bind_param("sssssiss", $username, $email, $google_id, $name, $avatar, $role_id, $create_at, $bg_avatar);
                    
                    if ($stmtInsert->execute()) {
                        // Đăng ký xong thì Login luôn
                        $new_user_id = $conn->insert_id;
                        $_SESSION['id_user'] = $new_user_id;
                        $_SESSION['name_user'] = $username;
                        $_SESSION['role_id'] = $role_id;
                        $_SESSION['avatar'] = $avatar;
                        //$_SESSION['register_success'] = "Chào mừng thành viên mới! Bạn đã đăng ký thành công qua Google.";
                        header("Location: ../../views/user/home.php");
                        exit;
                    } else {
                        $_SESSION['error'] = "Lỗi tạo tài khoản: " . $conn->error;
                        header("Location: ../../views/login/login.php");
                        exit;
                    }
                }
            }
        } else {
            $_SESSION['error'] = "Lỗi xác thực Google.";
            header("Location: ../../views/login/login.php");
            exit;
        }
    } else {
        // Nếu chưa có code, chuyển hướng sang trang đăng nhập của Google
        $authUrl = $client->createAuthUrl();
        header("Location: " . $authUrl);
        exit;
    }
?>