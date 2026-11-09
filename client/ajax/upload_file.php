<?php
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in() || current_user_role() !== 'client') {
    redirect(BASE_URL . '/public/login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/client/projects.php');
}
verify_csrf();

$me = current_user();
$projectId = (int)($_POST['project_id'] ?? 0);
if (!$projectId || empty($_FILES['file']['name'])) {
    set_flash('danger', 'Please choose a file.');
    redirect(BASE_URL . '/client/project-detail.php?id=' . $projectId);
}

/* Ownership */
$db = Database::getConnection();
$chk = $db->prepare("SELECT id FROM projects WHERE id=? AND client_id=?");
$chk->execute([$projectId, $me['id']]);
if (!$chk->fetch()) {
    set_flash('danger', 'Project not found.');
    redirect(BASE_URL . '/client/projects.php');
}

$allowed = ['pdf','png','jpg','jpeg','gif','doc','docx','xls','xlsx','zip','txt','csv'];
$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
$maxBytes = 10 * 1024 * 1024;

if (!in_array($ext, $allowed)) {
    set_flash('danger', 'File type not allowed (' . e($ext) . ').');
    redirect(BASE_URL . '/client/project-detail.php?id=' . $projectId);
}
if ($_FILES['file']['size'] > $maxBytes) {
    set_flash('danger', 'File too large (max 10 MB).');
    redirect(BASE_URL . '/client/project-detail.php?id=' . $projectId);
}

if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
$stored = 'cu_' . uniqid() . '.' . $ext;

if (move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_DIR . $stored)) {
    $db->prepare("INSERT INTO project_files (project_id, uploaded_by, original_name, stored_name, file_size, mime_type, file_type) VALUES (?, ?, ?, ?, ?, ?, 'client_upload')")
       ->execute([$projectId, $me['id'], $_FILES['file']['name'], $stored, $_FILES['file']['size'], $_FILES['file']['type']]);

    /* Notify admin */
    $adminStmt = $db->prepare("SELECT id FROM users WHERE role='admin' LIMIT 1");
    $adminStmt->execute();
    if ($a = $adminStmt->fetch()) {
        notify($a['id'], 'New client file upload',
            "{$me['full_name']} uploaded a file to project #$projectId.",
            'project',
            '/admin/project-edit.php?id=' . $projectId);
    }

    set_flash('success', 'File uploaded successfully.');
} else {
    set_flash('danger', 'Upload failed.');
}
redirect(BASE_URL . '/client/project-detail.php?id=' . $projectId);