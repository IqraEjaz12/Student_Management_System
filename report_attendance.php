<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Attendance Report';
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Attendance Report</h2>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Roll No</th>
                    <th>Course</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT a.*, s.name as student_name, s.roll_no, c.name as course_name
                        FROM attendance a
                        JOIN students s ON a.student_id = s.id
                        JOIN courses c ON a.course_id = c.id
                        ORDER BY a.date DESC";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['roll_no']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                    echo "<td>" . $row['date'] . "</td>";
                    echo "<td>" . ucfirst($row['status']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="report-actions">
        <button onclick="window.print()" class="btn-secondary">Print Report</button>
        <a href="export_attendance.php" class="btn-secondary">Export to CSV</a>
    </div>
</div>

<?php include 'footer.php'; ?>