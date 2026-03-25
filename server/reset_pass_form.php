<?php
require_once 'db_connect.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    die('Invalid or expired link.');
}

// Check token validity
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires > NOW()");
$stmt->execute([$token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Invalid or expired link.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['newPassword'] ?? '';
    if (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Update password in applicant table
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE applicant SET Password = ? WHERE Email = ?");
        $stmt->execute([$hash, $row['email']]);
        // Remove used token
        $conn->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
        $success = "Password has been reset. You can now log in.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta charset="utf-8">
</head>
<body>
    <h2>Reset Your Password</h2>
    <?php if (!empty($error)): ?>
        <div style="color:red"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div style="color:green"><?= htmlspecialchars($success) ?></div>
    <?php else: ?>
    <form method="post">
        <label>New Password:</label>
        <input type="password" name="newPassword" required minlength="8">
        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</body>
</html>