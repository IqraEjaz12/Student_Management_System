<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'My Grades';

// Get student info
$student_result = mysqli_query($conn, "SELECT * FROM students WHERE user_id = (SELECT id FROM users WHERE username = '{$_SESSION['username']}')");
$student = mysqli_fetch_assoc($student_result);

// Get grades
$grades_result = mysqli_query($conn, "SELECT g.*, c.name as course_name, t.name as teacher_name
                                      FROM grades g
                                      JOIN courses c ON g.course_id = c.id
                                      LEFT JOIN teachers t ON c.teacher_id = t.id
                                      WHERE g.student_id = {$student['id']}
                                      ORDER BY g.grade_date DESC");
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>My Grades</h2>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Teacher</th>
                    <th>Grade</th>
                    <th>Semester</th>
                    <th>Grade Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($grade = mysqli_fetch_assoc($grades_result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($grade['course_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($grade['teacher_name'] ?? 'Not Assigned') . "</td>";
                    echo "<td>" . htmlspecialchars($grade['grade']) . "</td>";
                    echo "<td>" . htmlspecialchars($grade['semester']) . "</td>";
                    echo "<td>" . $grade['grade_date'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>