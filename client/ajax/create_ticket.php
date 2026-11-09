<?php
require_once __DIR__ . '/../../includes/functions.php';

if (!is_logged_in() || current_user_role() !== 'client') {
    redirect(BASE_URL . '/public/login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/client/tickets.php');
}
verify_csrf();

$me = current_user();
$subject  = sanitize($_POST['subject'] ?? '');
$message  = sanitize($_POST['message'] ?? '');
$priority = in_array($_POST['priority'] ?? '', ['low','medium','high','urgent'], true) ? $_POST['priority'] : 'medium';

if (!$subject || !$message) {
    set_flash('danger', 'Subject and message are required.');
    redirect(BASE_URL . '/client/tickets.php');
}

$stmt = Database::getConnection()->prepare(
    "INSERT INTO tickets (client_id, subject, message, priority) VALUES (?, ?, ?, ?)"
);
$stmt->execute([$me['id'], $subject, $message, $priority]);

$ticketId = (int)Database::getConnection()->lastInsertId();

$adminStmt = Database::getConnection()->prepare("SELECT id, email, full_name FROM users WHERE role='admin' LIMIT 1");
$adminStmt->execute();
if ($admin = $adminStmt->fetch()) {
    notify($admin['id'], 'New support ticket',
        "{$me['full_name']} opened a ticket: $subject",
        'ticket',
        '/admin/ticket-detail.php?id=' . $ticketId);

    require_once __DIR__ . '/../../includes/mailer.php';
    $bodyHtml = mail_template(
        'New support ticket #' . $ticketId,
        '<p><strong>From:</strong> ' . htmlspecialchars($me['full_name']) . '</p>'
        . '<p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>'
        . '<p><strong>Priority:</strong> ' . strtoupper($priority) . '</p>'
        . '<hr><p>' . nl2br(htmlspecialchars($message)) . '</p>',
        'Open ticket',
        BASE_URL . '/admin/ticket-detail.php?id=' . $ticketId
    );
    send_mail($admin['email'], "New support ticket: $subject", $bodyHtml, $admin['full_name']);
}

set_flash('success', 'Ticket submitted! Our team will respond shortly.');
redirect(BASE_URL . '/client/tickets.php');