<?php
/**
 * DonutsTecLiberia — Global Configuration
 */

// Prevent direct access to config from browser in some servers
define('DTC_ROOT', dirname(__DIR__));

// --- Environment ---
define('DTC_ENV', 'development'); // 'development' | 'production'

// --- Site ---
define('SITE_NAME', 'DonutsTecLiberia');
define('BASE_URL',  'http://localhost:8080'); // Change this to your actual base URL

// --- Paths ---
define('UPLOAD_DIR', DTC_ROOT . '/assets/uploads/');
define('UPLOAD_URL', BASE_URL . '/assets/uploads/');

// --- Security ---
define('SESSION_NAME', 'DTC_SESSION');
define('CSRF_KEY',     'dtc_csrf_token');
define('MAX_LOGIN_ATTEMPTS', 5);

// --- Error reporting ---
if (DTC_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// --- Timezone ---
date_default_timezone_set('Africa/Monrovia');

// --- Secure session start ---
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,           // set true when on HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}