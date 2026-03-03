<?php
// ============================================================
// mailer.php — Send email via Gmail SMTP (uses PHPMailer)
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

function send_email(array $applicant): bool {
    $name  = $applicant['name'];
    $to    = $applicant['email'];
    $job   = $applicant['job'] ?? JOB_TITLE;

    // Fill in placeholders
    $subject = str_replace(['{name}', '{job}'], [$name, $job], EMAIL_SUBJECT);
    $body    = str_replace(['{name}', '{job}'], [$name, $job], EMAIL_BODY);

    $mail = new PHPMailer(true);

    try {
        // Gmail SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_ADDRESS;
        $mail->Password   = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // From / To
        $mail->setFrom(EMAIL_ADDRESS, COMPANY_NAME);
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        log_info("✅ Email sent → $name <$to> | Job: $job");
        return true;

    } catch (Exception $e) {
        log_error("❌ Failed to send to $to: {$mail->ErrorInfo}");
        return false;
    }
}
