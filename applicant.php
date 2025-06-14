<?php
session_start();
include 'db_connect.php';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $name = trim($_POST['fullName'] ?? '');
        $applicantId = trim($_POST['applicantId'] ?? '');
        $email = trim($_POST['regEmail'] ?? '');
        $password = $_POST['regPassword'] ?? '';

        if (!$name || !$applicantId || !$email || !$password) {
            echo "All fields are required.";
            exit;
        }

        // Check email
        $sql = "SELECT 1 FROM Applicant WHERE Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo "duplicate";
            exit;
        }
        // Check applicant ID
        $sql = "SELECT 1 FROM Applicant WHERE Applicant_Id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$applicantId]);
        if ($stmt->fetch()) {
            echo "duplicate";
            exit;
        }

        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO Applicant (Name, Applicant_Id, Email, Password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$name, $applicantId, $email, $hashedPassword])) {
            echo "success";
        } else {
            $errorInfo = $stmt->errorInfo();
            echo "error: " . $errorInfo[2];
        }
    } else {
        echo "Invalid request";
    }
} catch (PDOException $e) {
    echo "PDO error: " . $e->getMessage();
}
?>