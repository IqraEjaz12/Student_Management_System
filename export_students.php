<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students_report.csv"');

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, array('ID', 'Name', 'Roll No', 'Class', 'Email', 'Phone', 'Date of Birth', 'Address'));

// Data rows
$sql = "SELECT * FROM students ORDER BY id";
$result = mysqli_query($conn, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, array(
        $row['id'],
        $row['name'],
        $row['roll_no'],
        $row['class'],
        $row['email'],
        $row['phone'],
        $row['date_of_birth'],
        $row['address']
    ));
}

fclose($output);
?>