<?php
require_once __DIR__ . '/includes/guard.php';
$me = current_user();
$db = Database::getConnection();

$id = (int)($_GET['id'] ?? 0);
$s = $db->prepare("
  SELECT i.*, p.title AS project_title, p.id AS project_id,
         u.full_name AS client_name, u.email AS client_email, u.company_name, u.phone
  FROM invoices i
  JOIN projects p ON i.project_id = p.id
  JOIN users u ON p.client_id = u.id
  WHERE i.id = ? AND p.client_id = ? LIMIT 1
");
$s->execute([$id, $me['id']]);
$inv = $s->fetch();
if (!$inv) { set_flash('danger', 'Invoice not found.'); redirect(BASE_URL . '/client/projects.php'); }