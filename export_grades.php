<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="grades_report.csv"');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, array('Student Name', 'Roll No', 'Course', 'Grade', 'Semester', 'Grade Date'));

// Data rows
$sql = "SELECT g.*, s.name as student_name, s.roll_no, c.name as course_name
        FROM grades g
        JOIN students s ON g.student_id = s.id
        JOIN courses c ON g.course_id = c.id
        ORDER BY g.grade_date DESC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, array(
        $row['student_name'],
        $row['roll_no'],
        $row['course_name'],
        $row['grade'],
        $row['semester'],
        $row['grade_date']
    ));
}

fclose($output);
?>