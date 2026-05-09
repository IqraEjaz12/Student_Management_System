<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Fee Report';
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Fee Report</h2>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Roll No</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT f.*, s.name as student_name, s.roll_no
                        FROM fees f
                        JOIN students s ON f.student_id = s.id
                        ORDER BY f.due_date DESC";
                $result = mysqli_query($conn, $sql);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['roll_no']) . "</td>";
                    echo "<td>$" . number_format($row['amount'], 2) . "</td>";
                    echo "<td>" . $row['due_date'] . "</td>";
                    echo "<td>" . ($row['paid'] ? 'Paid' : 'Unpaid') . "</td>";
                    echo "<td>" . ($row['paid_date'] ? $row['paid_date'] : '-') . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="report-actions">
        <button onclick="window.print()" class="btn-secondary">Print Report</button>
        <a href="export_fees.php" class="btn-secondary">Export to CSV</a>
    </div>
</div>

<?php include 'footer.php'; ?>