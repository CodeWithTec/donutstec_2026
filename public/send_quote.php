<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/public/quote.php');
}
verify_csrf();

$name    = sanitize($_POST['name'] ?? '');
$email   = filter_var(sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone   = sanitize($_POST['phone'] ?? '');
$service = sanitize($_POST['service_type'] ?? '');
$budget  = sanitize($_POST['budget_range'] ?? '');
$details = sanitize($_POST['details'] ?? '');

if (!$name || !$email || !$service || !$details) {
    set_flash('danger', 'Please fill in all required fields.');
    redirect(BASE_URL . '/public/quote.php');
}

$stmt = Database::getConnection()->prepare(
    "INSERT INTO quotes (name, email, phone, service_type, budget_range, details)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->execute([$name, $email, $phone, $service, $budget, $details]);

set_flash('success', 'Thank you! Your quote request was received. Expect our reply within 24–48 hours.');
redirect(BASE_URL . '/public/quote.php');


// Notify admin + send email

$quoteId = (int)Database::getConnection()->lastInsertId();
$adminStmt = Database::getConnection()->prepare("SELECT id, email, full_name FROM users WHERE role='admin' LIMIT 1");
$adminStmt->execute();
if ($admin = $adminStmt->fetch()) {
    notify($admin['id'], 'New quote request',
        "$name requested a quote for $service.",
        'quote',
        '/admin/quotes.php');

    require_once __DIR__ . '/../includes/mailer.php';
    $html = mail_template(
        'New quote request',
        '<p><strong>From:</strong> ' . htmlspecialchars($name) . ' &lt;' . htmlspecialchars($email) . '&gt;</p>'
        . '<p><strong>Service:</strong> ' . htmlspecialchars($service) . '</p>'
        . '<p><strong>Budget:</strong> ' . htmlspecialchars($budget ?: '—') . '</p>'
        . '<hr><p>' . nl2br(htmlspecialchars($details)) . '</p>',
        'Open quotes',
        BASE_URL . '/admin/quotes.php'
    );
    send_mail($admin['email'], 'New quote request from ' . $name, $html, $admin['full_name']);
}