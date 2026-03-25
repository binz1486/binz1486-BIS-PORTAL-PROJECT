<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $isAdmin = isset($input['admin']) && $input['admin'] === true;

    if ($isAdmin) {
        // Only allow permanent admin credentials
        if ($email === 'binzeria@gmail.com' && $password === '360') {
            $_SESSION['applicant_id'] = 'admin';
            echo json_encode(["status" => "success", "role" => "admin"]);
        } else {
            echo json_encode(["status" => "error", "message" => "invalid_admin_credentials"]);
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
        echo json_encode(["status" => "success", "role" => "user", "userId" => $user['Applicant_Id']]);
    } else {
        echo json_encode(["status" => "error", "message" => "invalid_user_credentials"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "invalid_request_method"]);
}
?>