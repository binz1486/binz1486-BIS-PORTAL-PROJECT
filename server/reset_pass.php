<?php
session_start();
require_once 'db_connect.php'; // Your PDO connection

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
$applicantId = trim($_POST['applicantId'] ?? '');

if (!$email || !$applicantId) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// --- Rate limiting: allow max 3 requests per email per 24 hours ---
$ip = $_SERVER['REMOTE_ADDR'];
$now = date('Y-m-d H:i:s');
$limit = 3;

// Create table for tracking requests if not exists
$conn->exec("CREATE TABLE IF NOT EXISTS password_reset_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    request_time DATETIME,
    ip VARCHAR(45)
)");

// Count requests in last 24 hours
$stmt = $conn->prepare("SELECT COUNT(*) FROM password_reset_requests WHERE email = ? AND request_time > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$stmt->execute([$email]);
$count = $stmt->fetchColumn();

if ($count >= $limit) {
    // Always respond with generic message
    echo json_encode(['success' => true, 'message' => 'Password reset link sent if email exists.']);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT * FROM applicant WHERE Email = ? AND Applicant_Id = ?");
$stmt->execute([$email, $applicantId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Store token in DB (create table if not exists)
    $conn->exec("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255),
        token VARCHAR(64),
        expires DATETIME
    )");

    // Remove old tokens
    $conn->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);
    // Insert new token
    $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)")
        ->execute([$email, $token, $expires]);

// Build reset link for localhost or your domain
    $host = $_SERVER['HTTP_HOST'];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $resetLink = "$protocol://$host/reset_password_form.php?token=$token";

    $subject = "Binz Scholarship Password Reset";
    $message = "Hello,\n\nTo reset your password, click the link below:\n$resetLink\n\nThis link will expire in 1 hour.\n\nIf you did not request this, please ignore this email.";
    $headers = "From: binzeria@gmail.com\r\nReply-To: binzeria@gmail.com";

    // For local development, mail() may not work. Use a tool like MailHog, Papercut, or check your mail server.
    mail($email, $subject, $message, $headers);
}

// Log the request (even if user doesn't exist)
$conn->prepare("INSERT INTO password_reset_requests (email, request_time, ip) VALUES (?, ?, ?)")
    ->execute([$email, $now, $ip]);

// Always respond with generic message
echo json_encode(['success' => true, 'message' => 'Password reset link sent if email exists.']);