<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'My Fees';

// Get student info
$student_result = mysqli_query($conn, "SELECT * FROM students WHERE user_id = (SELECT id FROM users WHERE username = '{$_SESSION['username']}')");
$student = mysqli_fetch_assoc($student_result);

// Get fees
$fees_result = mysqli_query($conn, "SELECT * FROM fees WHERE student_id = {$student['id']} ORDER BY due_date DESC");

// Calculate fee stats
$total_fees = 0;
$paid_fees = 0;
$pending_fees = 0;

mysqli_data_seek($fees_result, 0);
while ($fee = mysqli_fetch_assoc($fees_result)) {
    $total_fees += $fee['amount'];
    if ($fee['paid']) {
        $paid_fees += $fee['amount'];
    } else {
        $pending_fees += $fee['amount'];
    }
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>My Fees</h2>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Fees</h3>
            <p class="stat-number">$<?php echo number_format($total_fees, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Paid</h3>
            <p class="stat-number">$<?php echo number_format($paid_fees, 2); ?></p>
        </div>
        <div class="stat-card">
            <h3>Pending</h3>
            <p class="stat-number">$<?php echo number_format($pending_fees, 2); ?></p>
        </div>
    </div>

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

<?php include 'footer.php'; ?>