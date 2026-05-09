<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';

if ($_SESSION['role'] === 'student') {
    $page_title = 'My Courses';

    // Get student info
    $student_result = mysqli_query($conn, "SELECT * FROM students WHERE user_id = (SELECT id FROM users WHERE username = '{$_SESSION['username']}')");
    $student = mysqli_fetch_assoc($student_result);

    // Get enrolled courses
    $courses_result = mysqli_query($conn, "SELECT c.*, t.name as teacher_name, e.enrolled_at
                                           FROM enrollments e
                                           JOIN courses c ON e.course_id = c.id
                                           LEFT JOIN teachers t ON c.teacher_id = t.id
                                           WHERE e.student_id = {$student['id']}
                                           ORDER BY e.enrolled_at DESC");

} elseif ($_SESSION['role'] === 'teacher') {
    $page_title = 'My Courses';

    // Get teacher info
    $teacher_result = mysqli_query($conn, "SELECT * FROM teachers WHERE user_id = (SELECT id FROM users WHERE username = '{$_SESSION['username']}')");
    $teacher = mysqli_fetch_assoc($teacher_result);

    // Get teacher's courses
    $courses_result = mysqli_query($conn, "SELECT c.*, COUNT(e.student_id) as student_count
                                           FROM courses c
                                           LEFT JOIN enrollments e ON c.id = e.course_id
                                           WHERE c.teacher_id = {$teacher['id']}
                                           GROUP BY c.id
                                           ORDER BY c.created_at DESC");
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2><?php echo $page_title; ?></h2>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Description</th>
                    <?php if ($_SESSION['role'] === 'student'): ?>
                        <th>Teacher</th>
                        <th>Enrolled Date</th>
                    <?php else: ?>
                        <th>Students Enrolled</th>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($course = mysqli_fetch_assoc($courses_result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($course['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($course['description'] ?? '') . "</td>";

                    if ($_SESSION['role'] === 'student') {
                        echo "<td>" . htmlspecialchars($course['teacher_name'] ?? 'Not Assigned') . "</td>";
                        echo "<td>" . $course['enrolled_at'] . "</td>";
                    } else {
                        echo "<td>" . $course['student_count'] . "</td>";
                        echo "<td>
                            <a href='view_course.php?id={$course['id']}' class='btn-secondary'>View Students</a>
                            <a href='manage_grades.php?course_id={$course['id']}' class='btn-secondary'>Grades</a>
                        </td>";
                    }
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>