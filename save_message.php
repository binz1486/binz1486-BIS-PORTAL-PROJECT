<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- BULK DELETE LOGIC FOR ADMIN ---
if (
    isset($_SESSION['applicant_id']) &&
    $_SESSION['applicant_id'] === 'admin' &&
    isset($_POST['action']) && $_POST['action'] === 'delete_messages' &&
    isset($_POST['message_ids']) && is_array($_POST['message_ids'])
) {
    try {
        $ids = $_POST['message_ids'];
        $in = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $conn->prepare("DELETE FROM messages WHERE Message_Id IN ($in)");
        $stmt->execute($ids);
        echo json_encode(['success' => true, 'message' => 'Messages deleted']);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}
// --- END BULK DELETE LOGIC ---

// Verify session and required data
if (!isset($_SESSION['applicant_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$applicant_id = $_SESSION['applicant_id'];

try {
    // Get user details
    $stmt = $conn->prepare("SELECT Name, Email FROM applicant WHERE Applicant_Id = ?");
    if (!$stmt->execute([$applicant_id])) {
        throw new Exception("Failed to fetch user details");
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        throw new Exception("User not found");
    }

    // Validate input
    $message_type = $_POST['messageType'] ?? '';
    $message = $_POST['messageText'] ?? '';
    
    if (empty($message_type) || empty($message)) {
        throw new Exception("All fields are required");
    }

    // Insert message - updated to match your table structure
    $stmt = $conn->prepare("INSERT INTO messages 
                          (Applicant_Id, Name, Email, Message_type, Message) 
                          VALUES (?, ?, ?, ?, ?)");
    
    $success = $stmt->execute([
        $applicant_id,
        $user['Name'],
        $user['Email'],
        $message_type,
        $message
    ]);

    if (!$success) {
        $errorInfo = $stmt->errorInfo();
        throw new Exception("Database error: " . $errorInfo[2]);
    }

    // Success response
    echo json_encode(['success' => true, 'message' => 'Message saved successfully']);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => [
            'session' => $_SESSION,
            'post' => $_POST,
            'user' => $user ?? null
        ]
    ]);
    exit;
}