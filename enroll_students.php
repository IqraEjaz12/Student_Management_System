<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Student Enrollments';

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll'])) {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    mysqli_query($conn, "INSERT IGNORE INTO enrollments (student_id, course_id) VALUES ($student_id, $course_id)");
    header("Location: enroll_students.php");
    exit;
}

// Handle unenrollment
if (isset($_GET['unenroll'])) {
    $student_id = intval($_GET['student_id']);
    $course_id = intval($_GET['course_id']);
    mysqli_query($conn, "DELETE FROM enrollments WHERE student_id=$student_id AND course_id=$course_id");
    header("Location: enroll_students.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Student Enrollments</h2>

    <!-- Enrollment Form -->
    <div class="form-card">
        <h3>Enroll Student in Course</h3>
        <form action="enroll_students.php" method="POST">
            <div class="form-row">
                <select name="student_id" required>
                    <option value="">Select Student</option>
                    <?php
                    $students = mysqli_query($conn, "SELECT id, name, roll_no FROM students");
                    while ($student = mysqli_fetch_assoc($students)) {
                        echo "<option value='{$student['id']}'>" . htmlspecialchars($student['name']) . " ({$student['roll_no']})</option>";
                    }
                    ?>
                </select>
                <select name="course_id" required>
                    <option value="">Select Course</option>
                    <?php
                    $courses = mysqli_query($conn, "SELECT id, name FROM courses");
                    while ($course = mysqli_fetch_assoc($courses)) {
                        echo "<option value='{$course['id']}'>" . htmlspecialchars($course['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="enroll" class="btn-primary">Enroll Student</button>
        </form>
    </div>

    <!-- Current Enrollments -->
    <div class="table-card">
        <h3>Current Enrollments</h3>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Roll No</th>
                    <th>Course</th>
                    <th>Teacher</th>
                    <th>Enrolled Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT e.*, s.name as student_name, s.roll_no, c.name as course_name, t.name as teacher_name
                                               FROM enrollments e
                                               JOIN students s ON e.student_id = s.id
                                               JOIN courses c ON e.course_id = c.id
                                               LEFT JOIN teachers t ON c.teacher_id = t.id
                                               ORDER BY e.enrolled_at DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['student_name']) . "</td>
                        <td>" . htmlspecialchars($row['roll_no']) . "</td>
                        <td>" . htmlspecialchars($row['course_name']) . "</td>
                        <td>" . htmlspecialchars($row['teacher_name'] ?? 'Not Assigned') . "</td>
                        <td>" . date('Y-m-d', strtotime($row['enrolled_at'])) . "</td>
                        <td>
                            <a href='enroll_students.php?unenroll=1&student_id={$row['student_id']}&course_id={$row['course_id']}'
                               onclick=\"return confirm('Unenroll this student?')\">
                                <button class='btn-delete'>Unenroll</button>
                            </a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>