<?php
/**
 * Password reset logic: request + validate + consume.
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/mailer.php';

/* ---------- REQUEST RESET ---------- */
if (($_GET['action'] ?? '') === 'request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = filter_var(sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        set_flash('danger', 'Please enter a valid email.');
        redirect(BASE_URL . '/public/forgot-password.php');
    }

    $db = Database::getConnection();
    $stmt = $db->prepare("SELECT id, full_name, email FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    /* For security: always show same message even if email doesn't exist */
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $hash  = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        /* Invalidate previous unused tokens */
        $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL")
           ->execute([$user['id']]);

        $db->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)")
           ->execute([$user['id'], $hash, $expires]);

        $link = BASE_URL . '/public/reset-password.php?token=' . urlencode($token);
        $html = mail_template(
            'Reset your password',
            '<p>Hi ' . htmlspecialchars($user['full_name']) . ',</p>'
            . '<p>We received a request to reset your DonutsTecLiberia password. Click the button below to choose a new one.</p>'
            . '<p style="color:#6C757D;font-size:13px;">This link expires in 1 hour. If you didn\'t request a reset, you can safely ignore this email.</p>',
            'Reset Password',
            $link
        );
        send_mail($user['email'], 'Reset your DonutsTecLiberia password', $html, $user['full_name']);
    }

    set_flash('success', 'If that email exists in our system, a reset link has been sent. Check your inbox (and spam).');
    redirect(BASE_URL . '/public/forgot-password.php');
}

/* ---------- CONSUME RESET (submit new password) ---------- */
if (($_GET['action'] ?? '') === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $token    = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$token || strlen($password) < 8) {
        set_flash('danger', 'Password must be at least 8 characters.');
        redirect(BASE_URL . '/public/reset-password.php?token=' . urlencode($token));
    }
    if ($password !== $confirm) {
        set_flash('danger', 'Passwords do not match.');
        redirect(BASE_URL . '/public/reset-password.php?token=' . urlencode($token));
    }

    $db = Database::getConnection();
    $hash = hash('sha256', $token);

    $stmt = $db->prepare("SELECT * FROM password_resets
                          WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()
                          LIMIT 1");
    $stmt->execute([$hash]);
    $reset = $stmt->fetch();

    if (!$reset) {
        set_flash('danger', 'This reset link is invalid or has expired.');
        redirect(BASE_URL . '/public/forgot-password.php');
    }

    /* Update password */
    $newHash = password_hash($password, PASSWORD_BCRYPT);
    $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $reset['user_id']]);
    $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = ?")->execute([$reset['id']]);

    log_activity('Password reset completed', (int)$reset['user_id']);
    set_flash('success', 'Password updated! You can now log in.');
    redirect(BASE_URL . '/public/login.php');
}