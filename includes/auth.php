<?php
/**
 * DonutsTecLiberia — Authentication Handlers
 * Handles: register, login, logout
 */
require_once __DIR__ . '/functions.php';

/* ============ LOGIN ============ */
if (($_GET['action'] ?? '') === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email    = filter_var(sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || empty($password)) {
        set_flash('danger', 'Please fill in all fields with a valid email.');
        redirect(BASE_URL . '/public/login.php');
    }

    $db = Database::getConnection();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        log_activity("Failed login attempt: $email");
        set_flash('danger', 'Invalid email or password.');
        redirect(BASE_URL . '/public/login.php');
    }

    /* 2FA check */
    if (!empty($user['totp_enabled']) && !empty($user['totp_secret'])) {
         $_SESSION['2fa_pending_user'] = $user['id'];
         redirect(BASE_URL . '/public/verify-2fa.php');
    }

    if ($user['status'] === 'banned') {
        set_flash('danger', 'Your account has been suspended. Contact support.');
        redirect(BASE_URL . '/public/login.php');
    }
    if ($user['status'] === 'pending') {
        set_flash('warning', 'Your account is pending approval.');
        redirect(BASE_URL . '/public/login.php');
    }

    // Set session
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['full_name'];

    // Update last_login
    $db->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
    log_activity('Logged in', $user['id']);

    if ($user['role'] === 'admin') {
        redirect(BASE_URL . '/admin/dashboard.php');
    }
    redirect(BASE_URL . '/client/dashboard.php');
}

/* ============ REGISTER ============ */
if (($_GET['action'] ?? '') === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $full_name = sanitize($_POST['full_name'] ?? '');
    $email     = filter_var(sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone     = sanitize($_POST['phone'] ?? '');
    $company   = sanitize($_POST['company_name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (strlen($full_name) < 2)                     $errors[] = 'Full name is required.';
    if (!$email)                                    $errors[] = 'A valid email is required.';
    if (strlen($password) < 8)                      $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)                     $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already registered.';
        }
    }

    if ($errors) {
        foreach ($errors as $err) set_flash('danger', $err);
        $_SESSION['old'] = ['full_name' => $full_name, 'email' => $email, 'phone' => $phone, 'company_name' => $company];
        redirect(BASE_URL . '/public/register.php');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = Database::getConnection()->prepare(
        'INSERT INTO users (full_name, email, password, phone, company_name, role, status)
         VALUES (?, ?, ?, ?, ?, "client", "active")'
    );
    $stmt->execute([$full_name, $email, $hash, $phone, $company]);

    log_activity("New client registered: $email", (int)Database::getConnection()->lastInsertId());

    set_flash('success', 'Account created! You can now log in.');
    redirect(BASE_URL . '/public/login.php');
}

/* ============ LOGOUT ============ */
if (($_GET['action'] ?? '') === 'logout') {
    if (is_logged_in()) {
        log_activity('Logged out', $_SESSION['user_id']);
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    redirect(BASE_URL . '/public/login.php');
}