<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Student Management System'; ?></title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Student Management System</h1>
            <div class="user-info">
                Welcome, <?php echo $_SESSION['username'] ?? 'User'; ?> (<?php echo $_SESSION['role'] ?? 'Role'; ?>)
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>
    <div class="main-content">
        <div class="sidebar">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_students.php">Students</a></li>
                <li><a href="manage_teachers.php">Teachers</a></li>
                <li><a href="manage_courses.php">Courses</a></li>
                <li><a href="enroll_students.php">Enrollments</a></li>
                <li><a href="grades.php">Grades</a></li>
                <li><a href="attendance.php">Attendance</a></li>
                <li><a href="fees.php">Fees</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>
        <div class="content">