<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Teacher Dashboard';

// Get teacher info
$teacher_id = $_SESSION['user_id'];
$teacher_result = mysqli_query($conn, "SELECT * FROM teachers WHERE user_id = (SELECT id FROM users WHERE username = '{$_SESSION['username']}')");
$teacher = mysqli_fetch_assoc($teacher_result);

// Get teacher's courses
$courses_result = mysqli_query($conn, "SELECT * FROM courses WHERE teacher_id = {$teacher['id']}");

// Get recent grades
$grades_result = mysqli_query($conn, "SELECT g.*, s.name as student_name, c.name as course_name
                                      FROM grades g
                                      JOIN students s ON g.student_id = s.id
                                      JOIN courses c ON g.course_id = c.id
                                      WHERE c.teacher_id = {$teacher['id']}
                                      ORDER BY g.grade_date DESC LIMIT 10");

// Get attendance stats
$attendance_result = mysqli_query($conn, "SELECT
    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present_count,
    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent_count,
    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late_count
    FROM attendance a
    JOIN courses c ON a.course_id = c.id
    WHERE c.teacher_id = {$teacher['id']}");
$attendance_stats = mysqli_fetch_assoc($attendance_result);
?>

<?php include 'header.php'; ?>

<div class="dashboard">
    <h2>Teacher Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($teacher['name']); ?>!</p>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>My Courses</h3>
            <p class="stat-number"><?php echo mysqli_num_rows($courses_result); ?></p>
        </div>
        <div class="stat-card">
            <h3>Present</h3>
            <p class="stat-number"><?php echo $attendance_stats['present_count']; ?></p>
        </div>
        <div class="stat-card">
            <h3>Absent</h3>
            <p class="stat-number"><?php echo $attendance_stats['absent_count']; ?></p>
        </div>
        <div class="stat-card">
            <h3>Late</h3>
            <p class="stat-number"><?php echo $attendance_stats['late_count']; ?></p>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>My Courses</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($courses_result, 0); // Reset pointer
                        while ($course = mysqli_fetch_assoc($courses_result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($course['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($course['description'] ?? '') . "</td>";
                            echo "<td>
                                <a href='view_course.php?id={$course['id']}' class='btn-secondary'>View Students</a>
                                <a href='manage_grades.php?course_id={$course['id']}' class='btn-secondary'>Manage Grades</a>
                            </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-card">
            <h3>Recent Grades</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Grade</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($grade = mysqli_fetch_assoc($grades_result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($grade['student_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($grade['course_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($grade['grade']) . "</td>";
                            echo "<td>" . $grade['grade_date'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>