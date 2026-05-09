<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Manage Courses';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add' || $action == 'update') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $teacher_id = intval($_POST['teacher_id']);

        if ($action == 'add') {
            $sql = "INSERT INTO courses (name, description, teacher_id)
                    VALUES ('$name', '$description', $teacher_id)";
        } else {
            $id = intval($_POST['id']);
            $sql = "UPDATE courses SET name='$name', description='$description', teacher_id=$teacher_id WHERE id=$id";
        }
        mysqli_query($conn, $sql);
        header("Location: manage_courses.php");
        exit;
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM courses WHERE id=$id");
        header("Location: manage_courses.php");
        exit;
    }
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Manage Courses</h2>

    <!-- Add/Edit Form -->
    <div class="form-card">
        <h3 id="form-title">Add New Course</h3>
        <form action="manage_courses.php" method="POST">
            <input type="hidden" name="id" id="course_id">
            <input type="hidden" name="action" id="form_action" value="add">

            <input type="text" name="name" id="name" placeholder="Course Name" required>
            <textarea name="description" id="description" placeholder="Course Description" rows="3"></textarea>
            <select name="teacher_id" id="teacher_id" required>
                <option value="">Select Teacher</option>
                <?php
                $teachers = mysqli_query($conn, "SELECT id, name FROM teachers");
                while ($teacher = mysqli_fetch_assoc($teachers)) {
                    echo "<option value='{$teacher['id']}'>" . htmlspecialchars($teacher['name']) . "</option>";
                }
                ?>
            </select>

            <div class="btn-row">
                <button type="submit" class="btn-primary" id="submit-btn">Add Course</button>
                <button type="button" class="btn-secondary" onclick="clearForm()">Clear</button>
            </div>
        </form>
    </div>

    <!-- Courses Table -->
    <div class="table-card">
        <h3>All Courses</h3>
        <input type="text" id="searchInput" placeholder="Search courses..." onkeyup="searchCourses()" class="search-bar">
        <table id="coursesTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Teacher</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT c.*, t.name as teacher_name FROM courses c LEFT JOIN teachers t ON c.teacher_id = t.id ORDER BY c.id DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['description']) . "</td>
                        <td>" . htmlspecialchars($row['teacher_name'] ?? 'Not Assigned') . "</td>
                        <td>
                            <button class='btn-edit' onclick='editCourse({$row['id']})'>Edit</button>
                            <button class='btn-delete' onclick='deleteCourse({$row['id']})'>Delete</button>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchCourses() {
    const input = document.getElementById("searchInput").value.toUpperCase();
    const rows = document.querySelectorAll("#coursesTable tbody tr");
    rows.forEach(row => {
        const text = row.innerText.toUpperCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

function editCourse(id) {
    window.location.href = `manage_courses.php?edit=${id}`;
}

function deleteCourse(id) {
    if (confirm('Delete this course?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input name="action" value="delete"><input name="id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function clearForm() {
    document.getElementById('course_id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('teacher_id').value = '';
    document.getElementById('form_action').value = 'add';
    document.getElementById('submit-btn').textContent = 'Add Course';
    document.getElementById('form-title').textContent = 'Add New Course';
}
</script>

<?php include 'footer.php'; ?>