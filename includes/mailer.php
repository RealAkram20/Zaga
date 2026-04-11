<?php
/**
 * Zaga Technologies - Email Helper
 * Uses PHPMailer with SMTP for sending transactional emails.
 */

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send an email using SMTP.
 *
 * @param string $to      Recipient email
 * @param string $subject Email subject
 * @param string $body    HTML email body
 * @return array ['success' => bool, 'message' => string]
 */
function send_email(string $to, string $subject, string $body): array {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings from environment
        $smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $smtpPort = intval(getenv('SMTP_PORT') ?: 587);
        $smtpUser = getenv('SMTP_USER') ?: '';
        $smtpPass = getenv('SMTP_PASS') ?: '';
        $fromEmail = getenv('SMTP_FROM') ?: $smtpUser;
        $fromName = getenv('SMTP_FROM_NAME') ?: 'Zaga Technologies';

        if (empty($smtpUser) || empty($smtpPass)) {
            return ['success' => false, 'message' => 'SMTP credentials not configured. Please set SMTP_USER and SMTP_PASS in .env'];
        }

        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->Port       = $smtpPort;
        // Port 465 = SSL, Port 587 = STARTTLS
        $mail->SMTPSecure = ($smtpPort == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo];
    }
}

/**
 * Build a styled password reset email.
 */
function build_reset_email(string $name, string $resetLink): string {
    return '
    <div style="max-width:600px;margin:0 auto;font-family:system-ui,-apple-system,sans-serif;color:#1e293b;">
        <div style="background:linear-gradient(135deg,#2563eb,#1e40af);padding:30px 20px;text-align:center;border-radius:8px 8px 0 0;">
            <h1 style="color:white;margin:0;font-size:24px;">Zaga Technologies</h1>
            <p style="color:rgba(255,255,255,0.9);margin:8px 0 0;font-size:14px;">Password Reset Request</p>
        </div>
        <div style="background:white;padding:30px 20px;border:1px solid #e2e8f0;border-top:none;">
            <p style="font-size:16px;">Hello <strong>' . htmlspecialchars($name) . '</strong>,</p>
            <p style="color:#475569;line-height:1.6;">We received a request to reset your password. Click the button below to create a new password:</p>
            <div style="text-align:center;margin:30px 0;">
                <a href="' . htmlspecialchars($resetLink) . '" style="display:inline-block;background:#2563eb;color:white;padding:14px 32px;border-radius:6px;text-decoration:none;font-weight:600;font-size:16px;">Reset Password</a>
            </div>
            <p style="color:#64748b;font-size:13px;">If the button doesn\'t work, copy and paste this link into your browser:</p>
            <p style="color:#2563eb;font-size:13px;word-break:break-all;">' . htmlspecialchars($resetLink) . '</p>
            <hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0;">
            <p style="color:#94a3b8;font-size:12px;">This link expires in 1 hour. If you did not request a password reset, please ignore this email.</p>
        </div>
        <div style="text-align:center;padding:16px;color:#94a3b8;font-size:12px;">
            &copy; ' . date('Y') . ' Zaga Technologies Ltd. All rights reserved.
        </div>
    </div>';
}
