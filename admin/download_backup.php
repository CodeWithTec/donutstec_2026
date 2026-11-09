<?php
require_once __DIR__ . '/includes/guard.php';

$file = basename($_GET['file'] ?? '');
$path = DTC_ROOT . '/backups/' . $file;

if (!$file || !file_exists($path)) {
    set_flash('danger', 'Backup not found.');
    redirect(BASE_URL . '/admin/backup.php');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;