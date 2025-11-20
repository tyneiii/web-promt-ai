<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    require_once __DIR__ . '/../../public/mail/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/../../public/mail/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../public/mail/PHPMailer/src/SMTP.php';

    function sendActivationEmail($recipient_email, $token)
    {
        $activation_link = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?action=activate&token=" . $token;
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'anhthu.ht1409@gmail.com'; 
            $mail->Password   = 'ksgm hxae qsfc ssav';    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';
            $mail->setLanguage('vi', __DIR__ . '/../../public/mail/PHPMailer/language/'); 

            // Người gửi
            $mail->setFrom('anhthu.ht1409@gmail.com', 'Web Prompt AI');
            // Người nhận
            $mail->addAddress($recipient_email);

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = 'Hãy kích hoạt tài khoản của bạn';
            $mail->Body    = "<p>Vui lòng truy cập vào link dưới đây để kích hoạt tài khoản của bạn:</p>"
                . "<a href='$activation_link'>Kích hoạt ngay</a>"
                . "<p>Link này sẽ hết hạn sau 30 phút.</p>";
            $mail->AltBody = "Hãy truy cập vào link này để kích hoạt tài khoản của bạn: $activation_link";

            $mail->send();
        } catch (Exception $e) {
            throw new Exception("Mailer Error: {$mail->ErrorInfo}");
        }
    }
    function sendResetPasswordEmail($recipient_email, $token){
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        $current_path = $_SERVER['SCRIPT_NAME'];
        $view_path = str_replace(
            '/controller/authentification/AuthController.php', 
            '/views/login/reset_password.php', 
            $current_path
        );
        $reset_link = $protocol . "://" . $host . $view_path . "?token=" . $token;
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'anhthu.ht1409@gmail.com'; 
            $mail->Password   = 'ksgm hxae qsfc ssav';    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';
            $mail->setLanguage('vi', __DIR__ . '/../../public/mail/PHPMailer/language/'); 

            // Người gửi
            $mail->setFrom('anhthu.ht1409@gmail.com', 'Web Prompt AI');
            
            // Người nhận
            $mail->addAddress($recipient_email);

            // Nội dung email
            $mail->isHTML(true);
            $mail->Subject = 'Yêu cầu đặt lại mật khẩu';
            $mail->Body    = "
                <h3>Xin chào,</h3>
                <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                <p>Vui lòng nhấn vào đường link bên dưới để đặt lại mật khẩu (Link có hiệu lực trong 15 phút):</p>
                <p><a href='$reset_link' style='background-color: #e53935; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Đặt lại mật khẩu</a></p>
                <p>Hoặc copy đường dẫn sau: $reset_link</p>
                <p>Nếu bạn không yêu cầu điều này, vui lòng bỏ qua email này.</p>
            ";
            
            $mail->AltBody = "Vui lòng truy cập vào link sau để đặt lại mật khẩu: $reset_link";

            $mail->send();
            return true; 

        } catch (Exception $e) {
            throw new Exception("Mailer Error: {$mail->ErrorInfo}");
        }
    }
?>