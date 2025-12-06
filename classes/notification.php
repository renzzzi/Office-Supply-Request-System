<?php

require_once __DIR__ . '/../libraries/PHPMailer/Exception.php';
require_once __DIR__ . '/../libraries/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libraries/PHPMailer/SMTP.php';
require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Notification
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    private function getStyledEmailTemplate($title, $bodyContent)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { margin: 0; padding: 0; background-color: #f4f4f5; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
                .email-container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
                .email-header { background-color: #1e1e24; padding: 30px; text-align: center; border-bottom: 4px solid #7f5af0; }
                .email-header h1 { margin: 0; color: #e4e4e7; font-size: 24px; letter-spacing: 1px; }
                .email-body { padding: 40px 30px; color: #333333; line-height: 1.6; font-size: 16px; }
                .email-body h2 { color: #1e1e24; margin-top: 0; font-size: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
                .email-body a { display: inline-block; background-color: #7f5af0; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; margin-top: 20px; text-align: center; }
                .email-footer { background-color: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #888888; border-top: 1px solid #eeeeee; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="email-header">
                    <h1>Supply Desk</h1>
                </div>
                <div class="email-body">
                    ' . $bodyContent . '
                </div>
                <div class="email-footer">
                    &copy; ' . date("Y") . ' Supply Desk System. All rights reserved.<br>
                    This is an automated notification. Please do not reply.
                </div>
            </div>
        </body>
        </html>
        ';
    }

    public function createNotification($userId, $message, $link, $email = null, $emailSubject = null, $emailBody = null)
    {
        $sql = "INSERT INTO notifications (users_id, message, link, created_at, is_read) 
                VALUES (:users_id, :message, :link, NOW(), 0)";
        
        $query = $this->pdo->prepare($sql);
        $query->execute([
            ':users_id' => $userId,
            ':message' => $message,
            ':link' => $link
        ]);

        if ($email && $emailSubject && $emailBody) {
            $this->sendEmail($email, $emailSubject, $emailBody);
        }
    }

    private function sendEmail($to, $subject, $body)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            
            $styledBody = $this->getStyledEmailTemplate($subject, $body);
            $mail->Body = $styledBody;
            
            $mail->AltBody = strip_tags($body);

            $mail->send();
        } catch (Exception $e) {
            
        }
    }

    public function getUnreadNotifications($userId)
    {
        $sql = "SELECT * FROM notifications WHERE users_id = ? AND is_read = 0 ORDER BY created_at DESC";
        $query = $this->pdo->prepare($sql);
        $query->execute([$userId]);
        return $query->fetchAll();
    }

    public function markAsRead($notificationId)
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        $query = $this->pdo->prepare($sql);
        $query->execute([$notificationId]);
    }
}
?>