<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Reports';
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Reports</h2>

    <div class="reports-grid">
        <div class="report-card">
            <h3>Student Report</h3>
            <p>View detailed student information</p>
            <a href="report_students.php" class="btn-primary">Generate</a>
        </div>

        <div class="report-card">
            <h3>Grade Report</h3>
            <p>View student grades by course</p>
            <a href="report_grades.php" class="btn-primary">Generate</a>
        </div>

        <div class="report-card">
            <h3>Attendance Report</h3>
            <p>View attendance statistics</p>
            <a href="report_attendance.php" class="btn-primary">Generate</a>
        </div>

        <div class="report-card">
            <h3>Fee Report</h3>
            <p>View fee payment status</p>
            <a href="report_fees.php" class="btn-primary">Generate</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>