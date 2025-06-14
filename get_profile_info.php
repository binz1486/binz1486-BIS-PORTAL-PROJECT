<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['applicant_id'])) {
    echo json_encode([]);
    exit;
}

$applicant_id = $_SESSION['applicant_id'];

// Fetch email from Applicant table
$stmt = $conn->prepare("SELECT Email FROM Applicant WHERE Applicant_Id = ?");
$stmt->execute([$applicant_id]);
$applicant = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch personal info from personal_info table
$stmt2 = $conn->prepare("SELECT Name, Religion, Date_of_Birth, Gender, Address, Nationality, Qualification, Grade_Percent, Contact_Number FROM personal_info WHERE Applicant_Id = ?");
$stmt2->execute([$applicant_id]);
$info = $stmt2->fetch(PDO::FETCH_ASSOC);

echo json_encode(array_merge(
    [
        'email' => $applicant['Email'] ?? '',
        'Applicant_Id' => $applicant_id 
    ],
    $info ?: []
));
?>