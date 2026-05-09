<?php
include 'db.php';

$course_id = intval($_GET['course_id'] ?? 0);

$result = mysqli_query($conn, "SELECT s.id, s.name, s.roll_no FROM students s
                               JOIN enrollments e ON s.id = e.student_id
                               WHERE e.course_id = $course_id");

$students = [];
while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

header('Content-Type: application/json');
echo json_encode($students);
?>