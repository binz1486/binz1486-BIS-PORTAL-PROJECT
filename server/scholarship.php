<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

// --- UPDATE APPLICATION STATUS LOGIC ---
if (
    isset($_SESSION['applicant_id']) &&
    $_SESSION['applicant_id'] === 'admin' &&
    isset($_POST['action']) && $_POST['action'] === 'update_status' &&
    isset($_POST['application_id']) && isset($_POST['status'])
) {
    $allowed = ['Approved', 'Pending', 'Rejected'];
    $status = $_POST['status'];
    if (!in_array($status, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    $appId = $_POST['application_id'];
    try {
        $stmt = $conn->prepare("UPDATE scholarship_applications SET Status = ? WHERE Application_Id = ?");
        $stmt->execute([$status, $appId]);
        echo json_encode(['success' => true, 'message' => 'Status updated']);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}
// --- END STATUS UPDATE LOGIC ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $applicantId = $_SESSION['applicant_id'] ?? '';
    if (!$applicantId) {
        echo "Session expired. Please log in again.";
        exit;
    }

    // Check if this applicant already applied
    $check = $conn->prepare("SELECT 1 FROM scholarship_applications WHERE Applicant_Id = ?");
    $check->execute([$applicantId]);
    if ($check->fetch()) {
        echo "You have already applied for a scholarship.";
        exit;
    }

    $applicationId = trim($_POST['applicationId'] ?? '');
    $university = trim($_POST['scholarshipUniversity'] ?? '');
    $degreeLevel = trim($_POST['scholarshipEligibility'] ?? '');
    $program1 = trim($_POST['scholarshipName'] ?? '');
    $program2 = trim($_POST['scholarshipName2'] ?? '');
    $profilePic = $_FILES['profilePic'] ?? null;
    $doxfile = $_FILES['doxfile'] ?? null;

    // Basic validation
    if (!$applicationId || !$university || !$degreeLevel || !$program1 || !$program2 || !$profilePic || !$doxfile) {
        echo "All fields are required.";
        exit;
    }

    // Save docs file in uploads folder
    $doxfilePath = '';
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir);

    if ($doxfile && $doxfile['error'] === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $doxfile['tmp_name']);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            echo "Only PDF files are allowed for documents.";
            exit;
        }
        $doxfilePath = $uploadDir . uniqid('docs_') . '_' . basename($doxfile['name']);
        move_uploaded_file($doxfile['tmp_name'], $doxfilePath);
    } else {
        echo "Document upload failed.";
        exit;
    }

    // Store profile pic as BLOB in DB
    $profilePicData = null;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if ($profilePic && $profilePic['error'] === UPLOAD_ERR_OK) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $profilePic['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowedTypes)) {
            echo "Only JPG, JPEG, or PNG images are allowed.";
            exit;
        }
        $profilePicData = file_get_contents($profilePic['tmp_name']);
    } else {
        echo "Profile picture upload failed.";
        exit;
    }

    $sql = "INSERT INTO scholarship_applications 
        (Application_Id, Applicant_Id, University, Degree_Level, Program_1, Program_2, Profile_Pic, Docs_File, Submitted_At)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        $applicationId, $applicantId, $university, $degreeLevel, $program1, $program2, $profilePicData, $doxfilePath
    ]);

    if ($success) {
        echo "success";
    } else {
        echo "Database error. Please try again.";
    }
} else {
    echo "Invalid request.";
}
?>