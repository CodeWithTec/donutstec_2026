<?php
/**
 * DonutsTecLiberia — Helper Functions
 */
require_once __DIR__ . '/../config/database.php';

/* ---------- Output escaping ---------- */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/* ---------- Input sanitizing ---------- */
function sanitize(?string $value): string {
    return trim(strip_tags($value ?? ''));
}

/* ---------- Redirect ---------- */
function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

/* ---------- Flash messages ---------- */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array {
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function display_flashes(): void {
    foreach (get_flashes() as $f) {
        $type = e($f['type']);
        $msg  = e($f['message']);
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$msg}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

/* ---------- Auth helpers ---------- */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

function current_user(): ?array {
    if (!is_logged_in()) return null;
    static $u = null;
    if ($u === null) {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $u = $stmt->fetch() ?: null;
    }
    return $u;
}

function current_user_role(): ?string {
    $u = current_user();
    return $u['role'] ?? null;
}

function require_login(): void {
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in first.');
        redirect(BASE_URL . '/public/login.php');
    }
}

function require_role(string ...$roles): void {
    require_login();
    if (!in_array(current_user_role(), $roles, true)) {
        set_flash('danger', 'Access denied.');
        redirect(BASE_URL . '/public/index.php');
    }
}

/* ---------- CSRF ---------- */
function csrf_token(): string {
    if (empty($_SESSION[CSRF_KEY])) {
        $_SESSION[CSRF_KEY] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_KEY];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION[CSRF_KEY] ?? '', $token)) {
        set_flash('danger', 'Invalid or expired form session. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL . '/public/index.php');
    }
}

/* ---------- Password reset / misc helpers ---------- */
function generate_invoice_no(): string {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

/* ---------- Log activity ---------- */
function log_activity(string $action, ?int $userId = null): void {
    try {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $action, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Throwable $e) { /* silent */ }
}


/* ---------- Notifications ---------- */
function notify(int $userId, string $title, string $message, string $type = 'general', ?string $linkUrl = null): void
{
    try {
        $db = Database::getConnection();
        $db->prepare("INSERT INTO notifications (user_id, title, message, notification_type, link_url) VALUES (?, ?, ?, ?, ?)")
           ->execute([$userId, $title, $message, $type, $linkUrl]);
    } catch (Throwable $e) {
        error_log('notify() failed: ' . $e->getMessage());
    }
}

function unread_notifications_count(int $userId): int
{
    try {
        $stmt = Database::getConnection()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    } catch (Throwable $e) { return 0; }
}