<?php
/**
 * Thin wrapper around PHPMailer with graceful fallback (log file).
 * If PHPMailer is not installed via composer, we try to require it from
 * vendor/ — if that fails too, we just log emails to /logs/mail.log
 * so the app never breaks in development.
 */
require_once __DIR__ . '/../config/config.php';

function send_mail(string $to, string $subject, string $bodyHtml, ?string $toName = null): bool
{
    $cfg = require __DIR__ . '/../config/mail.php';

    // Log every email attempt regardless
    $logDir = DTC_ROOT . '/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $log = sprintf(
        "[%s] TO: %s | SUBJECT: %s\n%s\n---\n",
        date('Y-m-d H:i:s'), $to, $subject, strip_tags($bodyHtml)
    );
    @file_put_contents($logDir . '/mail.log', $log, FILE_APPEND);

    if (!$cfg['enabled']) return true; // pretend success in dev

    // Try composer autoload
    $autoload = DTC_ROOT . '/vendor/autoload.php';
    if (file_exists($autoload)) require_once $autoload;

    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log('PHPMailer not installed. Run: composer require phpmailer/phpmailer');
        return false;
    }

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $cfg['host'];
        $mail->Port       = $cfg['port'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg['username'];
        $mail->Password   = $cfg['password'];
        $mail->SMTPSecure = $cfg['encryption'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($cfg['from'], $cfg['from_name']);
        $mail->addReplyTo($cfg['reply_to'], $cfg['from_name']);
        $mail->addAddress($to, $toName ?? '');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = strip_tags($bodyHtml);

        return $mail->send();
    } catch (Throwable $e) {
        error_log('Mail send failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Branded email wrapper (uses your blue palette).
 */
function mail_template(string $title, string $innerHtml, ?string $ctaText = null, ?string $ctaUrl = null): string
{
    $cta = '';
    if ($ctaText && $ctaUrl) {
        $cta = '<p style="text-align:center;margin:28px 0;">'
             . '<a href="' . htmlspecialchars($ctaUrl) . '" style="background:#0000FF;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;display:inline-block;">'
             . htmlspecialchars($ctaText) . '</a></p>';
    }
    return '<!DOCTYPE html><html><body style="margin:0;padding:0;background:#F8F9FC;font-family:Segoe UI,Tahoma,sans-serif;">'
         . '<div style="max-width:600px;margin:0 auto;background:#fff;">'
         . '<div style="background:linear-gradient(135deg,#151B54,#1E90FF);padding:28px;text-align:center;color:#fff;">'
         . '<h1 style="margin:0;font-size:22px;font-weight:800;">Donuts<span style="color:#82CAFF;">Tec</span> Liberia</h1>'
         . '</div>'
         . '<div style="padding:32px;color:#212529;line-height:1.6;">'
         . '<h2 style="color:#151B54;margin-top:0;">' . htmlspecialchars($title) . '</h2>'
         . $innerHtml
         . $cta
         . '</div>'
         . '<div style="background:#151B54;padding:18px;text-align:center;color:#82CAFF;font-size:12px;">'
         . '&copy; ' . date('Y') . ' DonutsTecLiberia &middot; Monrovia, Liberia'
         . '</div></div></body></html>';
}