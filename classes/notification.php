<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/../libraries/PHPMailer/Exception.php';
require_once __DIR__ . '/../libraries/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libraries/PHPMailer/SMTP.php';

class Notification
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function createNotification(int $userId, string $message, ?string $link, string $userEmail, string $emailSubject, string $emailBody)
    {
        try {
            $sql = "INSERT INTO notifications (user_id, message, link) VALUES (:user_id, :message, :link)";
            $query = $this->pdo->prepare($sql);
            $query->execute([
                ':user_id' => $userId,
                ':message' => $message,
                ':link' => $link
            ]);
        } catch (PDOException $e) {
            error_log("DB Notification Error: " . $e->getMessage());
        }

        $this->sendEmail($userEmail, $emailSubject, $emailBody);
    }

    private function sendEmail(string $to, string $subject, string $body)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}