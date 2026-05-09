<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Fees Management';

// Handle fee submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_fee'])) {
    $student_id = intval($_POST['student_id']);
    $amount = floatval($_POST['amount']);
    $due_date = $_POST['due_date'];

    mysqli_query($conn, "INSERT INTO fees (student_id, amount, due_date) VALUES ($student_id, $amount, '$due_date')");
    header("Location: fees.php");
    exit;
}

// Handle payment
if (isset($_GET['pay'])) {
    $fee_id = intval($_GET['pay']);
    mysqli_query($conn, "UPDATE fees SET paid=TRUE, paid_date=CURDATE() WHERE id=$fee_id");
    header("Location: fees.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Fees Management</h2>

    <!-- Add Fee Form -->
    <div class="form-card">
        <h3>Add Fee</h3>
        <form action="fees.php" method="POST">
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
                <input type="number" name="amount" step="0.01" placeholder="Amount" required>
            </div>
            <input type="date" name="due_date" required>

            <button type="submit" name="add_fee" class="btn-primary">Add Fee</button>
        </form>
    </div>

    <!-- Fees Table -->
    <div class="table-card">
        <h3>All Fees</h3>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Roll No</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT f.*, s.name as student_name, s.roll_no
                                               FROM fees f
                                               JOIN students s ON f.student_id = s.id
                                               ORDER BY f.due_date DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                    $status = $row['paid'] ? 'Paid' : 'Unpaid';
                    $status_class = $row['paid'] ? 'paid' : 'unpaid';
                    $paid_date = $row['paid_date'] ? $row['paid_date'] : '-';
                    $action = $row['paid'] ? '-' : "<a href='fees.php?pay={$row['id']}' onclick=\"return confirm('Mark as paid?')\"><button class='btn-primary'>Mark Paid</button></a>";

                    echo "<tr>
                        <td>" . htmlspecialchars($row['student_name']) . "</td>
                        <td>" . htmlspecialchars($row['roll_no']) . "</td>
                        <td>$" . number_format($row['amount'], 2) . "</td>
                        <td>" . htmlspecialchars($row['due_date']) . "</td>
                        <td class='$status_class'>$status</td>
                        <td>$paid_date</td>
                        <td>$action</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>