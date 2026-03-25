<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$applicantId = $_GET['applicant_id'] ?? $_SESSION['applicant_id'] ?? null;
if (!$applicantId) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("SELECT Name, Applicant_Id, Email FROM Applicant WHERE Applicant_Id = ?");
$stmt->execute([$applicantId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode([
        'success' => true,
        'name' => $user['Name'],
        'applicant_id' => $user['Applicant_Id'],
        'email' => $user['Email'] ?? ''
    ]);
} else {
    echo json_encode(['success' => false]);
}
?>