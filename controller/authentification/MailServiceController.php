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
        // --- CẤU HÌNH SERVER (SMTP) ---
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
?>