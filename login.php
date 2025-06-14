<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $isAdmin = isset($_POST['admin']) && $_POST['admin'] === '1';

    if ($isAdmin) {
        // Only allow permanent admin credentials
        if ($email === 'binzeria@gmail.com' && $password === '360') {
            $_SESSION['applicant_id'] = 'admin';
            echo "admin_success"; // <--- change here
        } else {
            echo "invalid";
        }
        exit;
    }

    // User login (not admin)
    $sql = "SELECT Applicant_Id, Password FROM Applicant WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['applicant_id'] = $user['Applicant_Id'];
        echo "user_success"; // <--- change here
    } else {
        echo "invalid";
    }
} else {
    echo "Invalid request";
}
?>