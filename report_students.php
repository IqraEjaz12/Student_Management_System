<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Student Report';
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Student Report</h2>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Roll No</th>
                    <th>Class</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Date of Birth</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM students ORDER BY id";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['roll_no']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['class'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone'] ?? '') . "</td>";
                    echo "<td>" . ($row['date_of_birth'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['address'] ?? '') . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="report-actions">
        <button onclick="window.print()" class="btn-secondary">Print Report</button>
        <a href="export_students.php" class="btn-secondary">Export to CSV</a>
    </div>
</div>

<?php include 'footer.php'; ?>