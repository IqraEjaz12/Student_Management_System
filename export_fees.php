<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="fees_report.csv"');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, array('Student Name', 'Roll No', 'Amount', 'Due Date', 'Status', 'Paid Date'));

// Data rows
$sql = "SELECT f.*, s.name as student_name, s.roll_no
        FROM fees f
        JOIN students s ON f.student_id = s.id
        ORDER BY f.due_date DESC";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, array(
        $row['student_name'],
        $row['roll_no'],
        '$' . number_format($row['amount'], 2),
        $row['due_date'],
        $row['paid'] ? 'Paid' : 'Unpaid',
        $row['paid_date'] ?: '-'
    ));
}

fclose($output);
?>