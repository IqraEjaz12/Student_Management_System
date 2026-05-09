<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Attendance Management';

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_attendance'])) {
    $course_id = intval($_POST['course_id']);
    $date = $_POST['date'];
    $attendance = $_POST['attendance']; // array of student_id => status

    foreach ($attendance as $student_id => $status) {
        mysqli_query($conn, "INSERT INTO attendance (student_id, course_id, date, status)
                             VALUES ($student_id, $course_id, '$date', '$status')
                             ON DUPLICATE KEY UPDATE status='$status'");
    }
    header("Location: attendance.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Attendance Management</h2>

    <!-- Mark Attendance Form -->
    <div class="form-card">
        <h3>Mark Attendance</h3>
        <form action="attendance.php" method="POST">
            <div class="form-row">
                <select name="course_id" id="course_select" required onchange="loadStudents()">
                    <option value="">Select Course</option>
                    <?php
                    $courses = mysqli_query($conn, "SELECT id, name FROM courses");
                    while ($course = mysqli_fetch_assoc($courses)) {
                        echo "<option value='{$course['id']}'>" . htmlspecialchars($course['name']) . "</option>";
                    }
                    ?>
                </select>
                <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div id="attendance-list">
                <!-- Students will be loaded here via JavaScript -->
            </div>

            <button type="submit" name="mark_attendance" class="btn-primary">Save Attendance</button>
        </form>
    </div>

    <!-- Attendance Records -->
    <div class="table-card">
        <h3>Recent Attendance</h3>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Course</th>
                    <th>Student</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT a.*, s.name as student_name, c.name as course_name
                                               FROM attendance a
                                               JOIN students s ON a.student_id = s.id
                                               JOIN courses c ON a.course_id = c.id
                                               ORDER BY a.date DESC, a.id DESC LIMIT 50");
                while ($row = mysqli_fetch_assoc($result)) {
                    $status_class = $row['status'] == 'present' ? 'present' : ($row['status'] == 'late' ? 'late' : 'absent');
                    echo "<tr>
                        <td>" . htmlspecialchars($row['date']) . "</td>
                        <td>" . htmlspecialchars($row['course_name']) . "</td>
                        <td>" . htmlspecialchars($row['student_name']) . "</td>
                        <td class='$status_class'>" . ucfirst($row['status']) . "</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function loadStudents() {
    const courseId = document.getElementById('course_select').value;
    if (!courseId) return;

    fetch(`get_enrolled_students.php?course_id=${courseId}`)
        .then(response => response.json())
        .then(students => {
            const list = document.getElementById('attendance-list');
            list.innerHTML = '<h4>Mark Attendance:</h4>';
            students.forEach(student => {
                list.innerHTML += `
                    <div class="attendance-item">
                        <span>${student.name} (${student.roll_no})</span>
                        <select name="attendance[${student.id}]" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                        </select>
                    </div>
                `;
            });
        });
}
</script>

<?php include 'footer.php'; ?>