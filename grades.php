<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Grades Management';

// Handle grade submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_grade'])) {
    $student_id = intval($_POST['student_id']);
    $course_id = intval($_POST['course_id']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $grade_date = $_POST['grade_date'];

    mysqli_query($conn, "INSERT INTO grades (student_id, course_id, grade, semester, grade_date)
                         VALUES ($student_id, $course_id, '$grade', '$semester', '$grade_date')
                         ON DUPLICATE KEY UPDATE grade='$grade', semester='$semester', grade_date='$grade_date'");
    header("Location: grades.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Grades Management</h2>

    <!-- Add Grade Form -->
    <div class="form-card">
        <h3>Add/Edit Grade</h3>
        <form action="grades.php" method="POST">
            <div class="form-row">
                <select name="student_id" id="student_id" required>
                    <option value="">Select Student</option>
                    <?php
                    $students = mysqli_query($conn, "SELECT id, name, roll_no FROM students");
                    while ($student = mysqli_fetch_assoc($students)) {
                        echo "<option value='{$student['id']}'>" . htmlspecialchars($student['name']) . " ({$student['roll_no']})</option>";
                    }
                    ?>
                </select>
                <select name="course_id" id="course_id" required>
                    <option value="">Select Course</option>
                    <?php
                    $courses = mysqli_query($conn, "SELECT id, name FROM courses");
                    while ($course = mysqli_fetch_assoc($courses)) {
                        echo "<option value='{$course['id']}'>" . htmlspecialchars($course['name']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-row">
                <input type="text" name="grade" placeholder="Grade (e.g., A, B+, 85)" required>
                <input type="text" name="semester" placeholder="Semester (e.g., Fall 2023)" required>
            </div>
            <input type="date" name="grade_date" required>

            <button type="submit" name="add_grade" class="btn-primary">Add Grade</button>
        </form>
    </div>

    <!-- Grades Table -->
    <div class="table-card">
        <h3>All Grades</h3>
        <input type="text" id="searchInput" placeholder="Search grades..." onkeyup="searchGrades()" class="search-bar">
        <table id="gradesTable">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Roll No</th>
                    <th>Course</th>
                    <th>Grade</th>
                    <th>Semester</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT g.*, s.name as student_name, s.roll_no, c.name as course_name
                                               FROM grades g
                                               JOIN students s ON g.student_id = s.id
                                               JOIN courses c ON g.course_id = c.id
                                               ORDER BY g.grade_date DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['student_name']) . "</td>
                        <td>" . htmlspecialchars($row['roll_no']) . "</td>
                        <td>" . htmlspecialchars($row['course_name']) . "</td>
                        <td>" . htmlspecialchars($row['grade']) . "</td>
                        <td>" . htmlspecialchars($row['semester']) . "</td>
                        <td>" . htmlspecialchars($row['grade_date']) . "</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchGrades() {
    const input = document.getElementById("searchInput").value.toUpperCase();
    const rows = document.querySelectorAll("#gradesTable tbody tr");
    rows.forEach(row => {
        const text = row.innerText.toUpperCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

<?php include 'footer.php'; ?>