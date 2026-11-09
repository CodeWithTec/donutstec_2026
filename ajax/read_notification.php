<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) { echo json_encode(['success'=>false]); exit; }

$me = current_user();
$id = (int)($_POST['id'] ?? 0);
$all = ($_POST['all'] ?? '') === '1';

$db = Database::getConnection();

if ($all) {
    $db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$me['id']]);
} elseif ($id) {
    $db->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?")->execute([$id, $me['id']]);
}

echo json_encode(['success' => true]);