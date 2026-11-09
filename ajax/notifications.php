<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) { echo json_encode(['success'=>false]); exit; }

$me = current_user();
$db = Database::getConnection();

$stmt = $db->prepare("SELECT id, title, message, notification_type, link_url, is_read, created_at
                      FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 15");
$stmt->execute([$me['id']]);
$rows = $stmt->fetchAll();

$unread = 0;
foreach ($rows as $r) if (!$r['is_read']) $unread++;

$out = [];
foreach ($rows as $r) {
    $out[] = [
        'id' => (int)$r['id'],
        'title' => $r['title'],
        'message' => $r['message'],
        'type' => $r['notification_type'],
        'link' => $r['link_url'],
        'is_read' => (bool)$r['is_read'],
        'time_ago' => time_ago($r['created_at']),
    ];
}

echo json_encode(['success' => true, 'unread' => $unread, 'notifications' => $out]);

/* ---------- helper ---------- */
function time_ago(string $ts): string {
    $diff = time() - strtotime($ts);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff/60) . 'm ago';
    if ($diff < 86400)  return floor($diff/3600) . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    return date('M d', strtotime($ts));
}