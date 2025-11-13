<?php
    function sendLoginErrors($email_error, $password_error)
    {
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
        header("Location: ../../views/login/login.php");
        exit;
    }

    function sendRegisterError($errors, $inputs)
    {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_inputs'] = $inputs;
        header("Location: ../../views/login/register.php");
        exit;
    }
?>