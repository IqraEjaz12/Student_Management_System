<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Student Dashboard';

// Get student info
$student_id = $_SESSION['user_id'];
$student_result = mysqli_query($conn, "SELECT * FROM students WHERE user_id = (SELECT id FROM users WHERE username = '{$_SESSION['username']}')");
$student = mysqli_fetch_assoc($student_result);

// Get enrolled courses
$courses_result = mysqli_query($conn, "SELECT c.*, t.name as teacher_name
                                       FROM enrollments e
                                       JOIN courses c ON e.course_id = c.id
                                       LEFT JOIN teachers t ON c.teacher_id = t.id
                                       WHERE e.student_id = {$student['id']}");

// Get grades
$grades_result = mysqli_query($conn, "SELECT g.*, c.name as course_name
                                      FROM grades g
                                      JOIN courses c ON g.course_id = c.id
                                      WHERE g.student_id = {$student['id']}
                                      ORDER BY g.grade_date DESC");

// Get attendance
$attendance_result = mysqli_query($conn, "SELECT a.*, c.name as course_name
                                          FROM attendance a
                                          JOIN courses c ON a.course_id = c.id
                                          WHERE a.student_id = {$student['id']}
                                          ORDER BY a.date DESC LIMIT 10");

// Get fees
$fees_result = mysqli_query($conn, "SELECT * FROM fees WHERE student_id = {$student['id']} ORDER BY due_date DESC");
?>

<?php include 'header.php'; ?>

<div class="dashboard">
    <h2>Student Dashboard</h2>
    <p>Welcome, <?php echo htmlspecialchars($student['name']); ?>!</p>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>My Courses</h3>
            <p class="stat-number"><?php echo mysqli_num_rows($courses_result); ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Grades</h3>
            <p class="stat-number"><?php echo mysqli_num_rows($grades_result); ?></p>
        </div>
        <div class="stat-card">
            <h3>Present Days</h3>
            <p class="stat-number">
                <?php
                $present_count = 0;
                mysqli_data_seek($attendance_result, 0);
                while ($att = mysqli_fetch_assoc($attendance_result)) {
                    if ($att['status'] == 'present') $present_count++;
                }
                echo $present_count;
                ?>
            </p>
        </div>
        <div class="stat-card">
            <h3>Pending Fees</h3>
            <p class="stat-number">
                <?php
                $pending_fees = 0;
                mysqli_data_seek($fees_result, 0);
                while ($fee = mysqli_fetch_assoc($fees_result)) {
                    if (!$fee['paid']) $pending_fees++;
                }
                echo $pending_fees;
                ?>
            </p>
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
                            <th>Teacher</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($courses_result, 0);
                        while ($course = mysqli_fetch_assoc($courses_result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($course['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($course['teacher_name'] ?? 'Not Assigned') . "</td>";
                            echo "<td>" . htmlspecialchars($course['description'] ?? '') . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-card">
            <h3>My Grades</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Grade</th>
                            <th>Semester</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($grades_result, 0);
                        while ($grade = mysqli_fetch_assoc($grades_result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($grade['course_name']) . "</td>";
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

        <div class="dashboard-card">
            <h3>Recent Attendance</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($attendance_result, 0);
                        while ($att = mysqli_fetch_assoc($attendance_result)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($att['course_name']) . "</td>";
                            echo "<td>" . $att['date'] . "</td>";
                            echo "<td class='" . $att['status'] . "'>" . ucfirst($att['status']) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-card">
            <h3>My Fees</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Paid Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($fees_result, 0);
                        while ($fee = mysqli_fetch_assoc($fees_result)) {
                            echo "<tr>";
                            echo "<td>$" . number_format($fee['amount'], 2) . "</td>";
                            echo "<td>" . $fee['due_date'] . "</td>";
                            echo "<td class='" . ($fee['paid'] ? 'paid' : 'unpaid') . "'>" . ($fee['paid'] ? 'Paid' : 'Unpaid') . "</td>";
                            echo "<td>" . ($fee['paid_date'] ?? '-') . "</td>";
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