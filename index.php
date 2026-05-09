<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Student Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="landing-container">
        <div class="landing-card">
            <div class="landing-header">
                <h1>Welcome to Student Management System</h1>
                <p>Professional, secure, and easy-to-use student management for your school.</p>
            </div>
            <div class="landing-actions">
                <a href="login.php" class="btn-primary landing-button">Sign In</a>
            </div>
            <div class="landing-note">
                <p>Need help? Use the credentials provided by your administrator to sign in.</p>
            </div>
        </div>
    </div>
</body>
</html>