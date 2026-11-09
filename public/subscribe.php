<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$email = filter_var(sanitize($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email.']);
    exit;
}

try {
    $stmt = Database::getConnection()->prepare(
        "INSERT INTO newsletter (email) VALUES (?) ON DUPLICATE KEY UPDATE is_active = 1"
    );
    $stmt->execute([$email]);
    echo json_encode(['success' => true, 'message' => 'Subscribed! Welcome aboard.']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Try again.']);
}