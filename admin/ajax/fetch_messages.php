<?php
require_once __DIR__ . '/../../includes/functions.php';
header('Content-Type: application/json');

if (!is_logged_in() || current_user_role() !== 'admin') {
    echo json_encode(['success'=>false]); exit;
}

$me = current_user();
$projectId = (int)($_GET['project_id'] ?? 0);
$lastId    = (int)($_GET['last_id'] ?? 0);
if (!$projectId) { echo json_encode(['success'=>false]); exit; }

$db = Database::getConnection();

$sql = "SELECT m.*, u.full_name AS sender_name, u.role
        FROM messages m JOIN users u ON m.sender_id=u.id
        WHERE m.project_id=?";
$params = [$projectId];
if ($lastId > 0) { $sql .= " AND m.id > ?"; $params[] = $lastId; }
$sql .= " ORDER BY m.created_at ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

/* Mark client messages as read */
$db->prepare("UPDATE messages SET is_read=1 WHERE project_id=? AND receiver_id=? AND is_read=0")
   ->execute([$projectId, $me['id']]);

$out = [];
foreach ($rows as $m) {
    $isMe = $m['sender_id'] == $me['id'];
    $bg   = $isMe ? 'var(--dtc-dodger)' : '#EEF4FF';
    $col  = $isMe ? '#fff' : '#212529';
    $align = $isMe ? 'flex-row-reverse' : '';
    $avatarBg = $m['role'] === 'admin' ? 'var(--dtc-night)' : 'var(--dtc-dodger)';
    $init = strtoupper(substr($m['sender_name'], 0, 1));

    $html = '<div class="d-flex mb-3 ' . $align . '">'
          . '<div class="avatar-badge me-2 ms-2" style="background:' . $avatarBg . ';">' . e($init) . '</div>'
          . '<div class="p-2 px-3 rounded-3" style="max-width:75%;background:' . $bg . ';color:' . $col . ';">'
          . '<div class="small fw-semibold">' . e($m['sender_name']) . '</div>'
          . '<div style="white-space:pre-wrap;">' . e($m['body']) . '</div>'
          . '<div class="small opacity-75 mt-1">' . date('M d, g:i A', strtotime($m['created_at'])) . '</div>'
          . '</div></div>';

    $out[] = ['id' => (int)$m['id'], 'html' => $html];
}

echo json_encode(['success' => true, 'messages' => $out]);