<?php
/**
 * Admin chat message sender
 * POST: project_id, receiver_id, body, csrf_token
 * Returns: { success, id, html }
 */
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

/* --- Guard --- */
if (!is_logged_in() || current_user_role() !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}
if (!hash_equals($_SESSION[CSRF_KEY] ?? '', $_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'CSRF failed — refresh the page and try again.']);
    exit;
}

$me         = current_user();
$projectId  = (int)($_POST['project_id'] ?? 0);
$receiverId = (int)($_POST['receiver_id'] ?? 0);
$body       = trim($_POST['body'] ?? '');

if (!$projectId || !$receiverId || $body === '') {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$db = Database::getConnection();

/* Verify project exists and receiver is the client on that project */
$chk = $db->prepare("SELECT id, client_id, title FROM projects WHERE id = ? LIMIT 1");
$chk->execute([$projectId]);
$project = $chk->fetch();
if (!$project) {
    echo json_encode(['success' => false, 'message' => 'Project not found']);
    exit;
}
if ((int)$project['client_id'] !== $receiverId) {
    echo json_encode(['success' => false, 'message' => 'Receiver mismatch']);
    exit;
}

/* --- Insert message --- */
try {
    $db->prepare("INSERT INTO messages (sender_id, receiver_id, project_id, body) VALUES (?, ?, ?, ?)")
       ->execute([$me['id'], $receiverId, $projectId, $body]);
    $newId = (int)$db->lastInsertId();
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    exit;
}

/* --- Notify client (non-fatal if fails) --- */
try {
    notify($receiverId, 'New reply from DonutsTecLiberia',
        'The team replied to your project thread.',
        'message',
        '/client/messages.php?project=' . $projectId);
} catch (Throwable $e) { /* silent */ }

/* --- Email client (non-fatal) --- */
try {
    require_once __DIR__ . '/../../includes/mailer.php';
    $cs = $db->prepare("SELECT email, full_name FROM users WHERE id = ? LIMIT 1");
    $cs->execute([$receiverId]);
    if ($client = $cs->fetch()) {
        $html = mail_template(
            'New message from DonutsTecLiberia',
            '<p>' . nl2br(htmlspecialchars($body)) . '</p>'
            . '<p style="color:#6C757D;font-size:13px;">Project: ' . htmlspecialchars($project['title']) . '</p>',
            'Reply in portal',
            BASE_URL . '/client/messages.php?project=' . $projectId
        );
        send_mail($client['email'], 'New message from DonutsTecLiberia', $html, $client['full_name']);
    }
} catch (Throwable $e) { /* silent */ }

/* --- Return HTML for immediate render --- */
$init = strtoupper(substr($me['full_name'], 0, 1));
$html = '<div class="d-flex mb-3 flex-row-reverse" data-msg-id="' . $newId . '">'
      . '<div class="avatar-badge me-2 ms-2" style="background:var(--dtc-night);">' . e($init) . '</div>'
      . '<div class="p-2 px-3 rounded-3" style="max-width:75%;background:var(--dtc-dodger);color:#fff;">'
      . '<div class="small fw-semibold">' . e($me['full_name']) . '</div>'
      . '<div style="white-space:pre-wrap;">' . e($body) . '</div>'
      . '<div class="small opacity-75 mt-1">' . date('M d, g:i A') . '</div>'
      . '</div></div>';

echo json_encode(['success' => true, 'id' => $newId, 'html' => $html]);