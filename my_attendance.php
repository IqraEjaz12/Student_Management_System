<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'My Attendance';

// Get student info
$student_result = mysqli_query($conn, "SELECT * FROM students WHERE user_id = (SELECT id FROM users WHERE username = '{$_SESSION['username']}')");
$student = mysqli_fetch_assoc($student_result);

// Get attendance
$attendance_result = mysqli_query($conn, "SELECT a.*, c.name as course_name
                                          FROM attendance a
                                          JOIN courses c ON a.course_id = c.id
                                          WHERE a.student_id = {$student['id']}
                                          ORDER BY a.date DESC");

// Calculate attendance stats
$total_days = mysqli_num_rows($attendance_result);
$present_days = 0;
$absent_days = 0;
$late_days = 0;

mysqli_data_seek($attendance_result, 0);
while ($att = mysqli_fetch_assoc($attendance_result)) {
    switch ($att['status']) {
        case 'present': $present_days++; break;
        case 'absent': $absent_days++; break;
        case 'late': $late_days++; break;
    }
}

$attendance_percentage = $total_days > 0 ? round(($present_days / $total_days) * 100, 2) : 0;
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>My Attendance</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Days</h3>
            <p class="stat-number"><?php echo $total_days; ?></p>
        </div>
        <div class="stat-card">
            <h3>Present</h3>
            <p class="stat-number"><?php echo $present_days; ?></p>
        </div>
        <div class="stat-card">
            <h3>Absent</h3>
            <p class="stat-number"><?php echo $absent_days; ?></p>
        </div>
        <div class="stat-card">
            <h3>Late</h3>
            <p class="stat-number"><?php echo $late_days; ?></p>
        </div>
        <div class="stat-card">
            <h3>Attendance %</h3>
            <p class="stat-number"><?php echo $attendance_percentage; ?>%</p>
        </div>
    </div>

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

<?php include 'footer.php'; ?>