<?php
// Test script to verify database connection and basic functionality
include 'db.php';

echo "<h1>Student Management System - Test</h1>";

// Test database connection
if ($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";

    // Test tables exist
    $tables = ['users', 'students', 'teachers', 'courses', 'enrollments', 'grades', 'attendance', 'fees'];
    foreach ($tables as $table) {
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
        if (mysqli_num_rows($result) > 0) {
            echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        } else {
            echo "<p style='color: red;'>✗ Table '$table' missing</p>";
        }
    }

    // Test default admin user
    $admin_check = mysqli_query($conn, "SELECT * FROM users WHERE username = 'admin'");
    if ($admin_check && mysqli_num_rows($admin_check) > 0) {
        echo "<p style='color: green;'>✓ Default admin user exists</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Default admin user not found - you may need to create it manually</p>";
    }

} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
}

echo "<br><a href='index.php'>← Back to Application</a>";
?>