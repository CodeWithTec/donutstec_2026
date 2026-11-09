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
$ticketId = (int)($_POST['ticket_id'] ?? 0);
$body     = trim($_POST['body'] ?? '');

if (!$ticketId || $body === '') {
    set_flash('danger', 'Reply cannot be empty.');
    redirect(BASE_URL . '/client/tickets.php');
}

$db = Database::getConnection();
$stmt = $db->prepare("SELECT id FROM tickets WHERE id = ? AND client_id = ?");
$stmt->execute([$ticketId, $me['id']]);
if (!$stmt->fetch()) {
    set_flash('danger', 'Ticket not found.');
    redirect(BASE_URL . '/client/tickets.php');
}

$db->prepare("INSERT INTO ticket_replies (ticket_id, user_id, body) VALUES (?, ?, ?)")
   ->execute([$ticketId, $me['id'], $body]);
$db->prepare("UPDATE tickets SET status = 'open', updated_at = NOW() WHERE id = ?")
   ->execute([$ticketId]);

set_flash('success', 'Reply sent.');
redirect(BASE_URL . '/client/ticket-detail.php?id=' . $ticketId);


// Notify admin

$adminStmt = Database::getConnection()->prepare("SELECT id FROM users WHERE role='admin' LIMIT 1");
$adminStmt->execute();
if ($a = $adminStmt->fetch()) {
    notify($a['id'], 'Client replied to ticket #' . $ticketId,
        'New reply on a support ticket.', 'ticket',
        '/admin/ticket-detail.php?id=' . $ticketId);
}