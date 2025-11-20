<?php
    session_start();
    require_once __DIR__ . '/../../config.php';
    require_once __DIR__ . '/ErrorController.php';
    require_once __DIR__ . '/MailServiceController.php';
    require_once __DIR__ . '/LoginController.php';
    require_once __DIR__ . '/RegisterController.php';
    require_once __DIR__ . '/ResetPasswordController.php';
    if (isset($_GET["action"])) {
        switch ($_GET['action']) {
            case 'login':
                handleLogin($conn);
                break;
            case 'register':
                handleRegister($conn);
                break;
            case 'activate':
                handleActivate($conn);
                break;
            case 'forgot_password':
                handleForgotPassword($conn);
                break;
            case 'reset_password':
                handleResetPassword($conn);
                break;
            default:
                header("Location: ../../views/user/home.php");
                exit;
        }
    } else {
        header("Location: ../../views/user/home.php");
        exit;
    }
?>