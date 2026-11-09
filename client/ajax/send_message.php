<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

if (!is_logged_in() || current_user_role() !== 'client') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Invalid method']); exit;
}
if (!hash_equals($_SESSION[CSRF_KEY] ?? '', $_POST['csrf_token'] ?? '')) {
    echo json_encode(['success'=>false,'message'=>'CSRF failed']); exit;
}

$me = current_user();
$projectId  = (int)($_POST['project_id'] ?? 0);
$receiverId = (int)($_POST['receiver_id'] ?? 0);
$body       = trim($_POST['body'] ?? '');

if (!$projectId || !$receiverId || $body === '') {
    echo json_encode(['success'=>false,'message'=>'Missing fields']); exit;
}

$db = Database::getConnection();

/* Verify ownership */
$check = $db->prepare("SELECT id, title FROM projects WHERE id=? AND client_id=?");
$check->execute([$projectId, $me['id']]);
$project = $check->fetch();
if (!$project) { echo json_encode(['success'=>false,'message'=>'Project not found']); exit; }

$stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, project_id, body) VALUES (?,?,?,?)");
$stmt->execute([$me['id'], $receiverId, $projectId, $body]);
$newId = (int)$db->lastInsertId();

/* Notify admin */
$adminStmt = $db->prepare("SELECT id, email, full_name FROM users WHERE role='admin' LIMIT 1");
$adminStmt->execute();
$admin = $adminStmt->fetch();
if ($admin) {
    notify($admin['id'], 'New client message',
        "{$me['full_name']} sent a message about \"{$project['title']}\".",
        'message',
        '/admin/messages.php?project=' . $projectId);

    require_once __DIR__ . '/../../includes/mailer.php';
    $bodyHtml = mail_template(
        'New message from ' . $me['full_name'],
        '<p>' . nl2br(htmlspecialchars($body)) . '</p>'
        . '<p style="color:#6C757D;font-size:13px;">Project: ' . htmlspecialchars($project['title']) . '</p>',
        'Open conversation',
        BASE_URL . '/admin/messages.php?project=' . $projectId
    );
    send_mail($admin['email'], 'New message from ' . $me['full_name'], $bodyHtml, $admin['full_name']);
}

/* Return the rendered HTML + id */
$init = strtoupper(substr($me['full_name'], 0, 1));
$html = '<div class="d-flex mb-3 flex-row-reverse" data-msg-id="' . $newId . '">'
      . '<div class="me-2 ms-2 avatar-badge" style="background:var(--dtc-dodger);">' . e($init) . '</div>'
      . '<div class="p-2 px-3 rounded-3" style="max-width:75%;background:var(--dtc-dodger);color:#fff;">'
      . '<div class="small fw-semibold">' . e($me['full_name']) . '</div>'
      . '<div style="white-space:pre-wrap;">' . e($body) . '</div>'
      . '<div class="small opacity-75 mt-1">' . date('M d, g:i A') . '</div>'
      . '</div></div>';

echo json_encode(['success' => true, 'id' => $newId, 'html' => $html]);