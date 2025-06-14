<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['applicant_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$oldPassword = $data['oldPassword'] ?? '';
$newPassword = $data['newPassword'] ?? '';

if (!$oldPassword || !$newPassword) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

$applicant_id = $_SESSION['applicant_id'];

// Fetch current password hash from DB
$stmt = $conn->prepare("SELECT Password FROM applicant WHERE Applicant_Id = ?");
$stmt->execute([$applicant_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($oldPassword, $user['Password'])) {
    echo json_encode(['success' => false, 'message' => 'Old password is incorrect']);
    exit;
}

// Prevent using the same password
if (password_verify($newPassword, $user['Password'])) {
    echo json_encode(['success' => false, 'message' => 'New password must be different from old password.']);
    exit;
}

// Update to new password (hashed)
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE applicant SET Password = ? WHERE Applicant_Id = ?");
$success = $stmt->execute([$newHash, $applicant_id]);

echo json_encode(['success' => $success]);