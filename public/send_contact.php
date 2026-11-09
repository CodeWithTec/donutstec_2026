<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/public/contact.php');
}
verify_csrf();

$name    = sanitize($_POST['name'] ?? '');
$email   = filter_var(sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone   = sanitize($_POST['phone'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');

if (!$name || !$email || !$subject || !$message) {
    set_flash('danger', 'Please fill in all required fields.');
    redirect(BASE_URL . '/public/contact.php');
}

$stmt = Database::getConnection()->prepare(
    "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)"
);
$stmt->execute([$name, $email, $phone, $subject, $message]);

set_flash('success', 'Thank you! Your message was sent. We will reply within 24 hours.');
redirect(BASE_URL . '/public/contact.php');