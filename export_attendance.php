<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report.csv"');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, array('Student Name', 'Roll No', 'Course', 'Date', 'Status'));

// Data rows
$sql = "SELECT a.*, s.name as student_name, s.roll_no, c.name as course_name
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        JOIN courses c ON a.course_id = c.id
        ORDER BY a.date DESC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, array(
        $row['student_name'],
        $row['roll_no'],
        $row['course_name'],
        $row['date'],
        ucfirst($row['status'])
    ));
}

fclose($output);
?>