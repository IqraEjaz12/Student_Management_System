<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Manage Students';

// Handle edit request
$edit_student = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM students WHERE id = $edit_id");
    $edit_student = mysqli_fetch_assoc($result);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add' || $action == 'update') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $roll_no = mysqli_real_escape_string($conn, $_POST['roll_no']);
        $class = mysqli_real_escape_string($conn, $_POST['class']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $date_of_birth = $_POST['date_of_birth'];
        $address = mysqli_real_escape_string($conn, $_POST['address']);

        // Handle photo upload
        $photo = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $photo = $target_dir . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
        }

        if ($action == 'add' && empty($photo)) {
            header("Location: manage_students.php?error=photo_required");
            exit;
        }

        if ($action == 'add') {
            $sql = "INSERT INTO students (name, roll_no, class, email, phone, date_of_birth, address, photo)
                    VALUES ('$name', '$roll_no', '$class', '$email', '$phone', '$date_of_birth', '$address', '$photo')";
        } else {
            $id = intval($_POST['id']);
            $sql = "UPDATE students SET name='$name', roll_no='$roll_no', class='$class',
                    email='$email', phone='$phone', date_of_birth='$date_of_birth',
                    address='$address'" . ($photo ? ", photo='$photo'" : "") . " WHERE id=$id";
        }
        mysqli_query($conn, $sql);
        header("Location: manage_students.php");
        exit;
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM students WHERE id=$id");
        header("Location: manage_students.php");
        exit;
    }
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Manage Students</h2>

    <!-- Add/Edit Form -->
    <div class="form-card">
        <h3 id="form-title"><?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?></h3>
        <form action="manage_students.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="student_id" value="<?php echo $edit_student['id'] ?? ''; ?>">
            <input type="hidden" name="action" id="form_action" value="<?php echo $edit_student ? 'update' : 'add'; ?>">

            <div class="form-row">
                <input type="text" name="name" id="name" placeholder="Full Name" value="<?php echo htmlspecialchars($edit_student['name'] ?? ''); ?>" required>
                <input type="text" name="roll_no" id="roll_no" placeholder="Roll Number" value="<?php echo htmlspecialchars($edit_student['roll_no'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <input type="text" name="class" id="class" placeholder="Class" value="<?php echo htmlspecialchars($edit_student['class'] ?? ''); ?>">
                <input type="email" name="email" id="email" placeholder="Email Address" value="<?php echo htmlspecialchars($edit_student['email'] ?? ''); ?>">
            </div>
            <div class="form-row">
                <input type="text" name="phone" id="phone" placeholder="Phone Number" value="<?php echo htmlspecialchars($edit_student['phone'] ?? ''); ?>">
                <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo $edit_student['date_of_birth'] ?? ''; ?>">
            </div>
            <textarea name="address" id="address" placeholder="Address" rows="3"><?php echo htmlspecialchars($edit_student['address'] ?? ''); ?></textarea>
            <div class="form-row">
                <label for="photo" style="display: block; margin-bottom: 5px; font-weight: 500;">Profile Photo</label>
                <?php if ($edit_student && ($edit_student['photo'] ?? '')): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php echo $edit_student['photo']; ?>" width="100" height="100" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                        <p style="font-size: 12px; color: #718096; margin-top: 5px;">Current photo - upload new to replace</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="photo" id="photo" accept="image/*" style="margin-top: 0;" <?php echo $edit_student ? '' : 'required'; ?>>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-primary" id="submit-btn"><?php echo $edit_student ? 'Update Student' : 'Add Student'; ?></button>
                <button type="button" class="btn-secondary" onclick="clearForm()">Clear</button>
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="table-card">
        <h3>All Students</h3>
        <input type="text" id="searchInput" placeholder="Search students..." onkeyup="searchStudents()" class="search-bar">
        <table id="studentsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Roll No</th>
                    <th>Class</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                    $id = $row['id'];
                    $photo = ($row['photo'] ?? '') ? "<img src='{$row['photo']}' width='50' height='50' style='border-radius:50%;'>" : "No Photo";
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>$photo</td>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['roll_no']) . "</td>
                        <td>" . htmlspecialchars($row['class'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['email'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['phone'] ?? '') . "</td>
                        <td>
                            <button class='btn-edit' onclick='editStudent({$row['id']})'>Edit</button>
                            <button class='btn-delete' onclick='deleteStudent({$row['id']})'>Delete</button>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchStudents() {
    const input = document.getElementById("searchInput").value.toUpperCase();
    const rows = document.querySelectorAll("#studentsTable tbody tr");
    rows.forEach(row => {
        const text = row.innerText.toUpperCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

function editStudent(id) {
    // Fetch student data via AJAX or redirect to edit page
    window.location.href = `manage_students.php?edit=${id}`;
}

function deleteStudent(id) {
    if (confirm('Delete this student?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input name="action" value="delete"><input name="id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function clearForm() {
    document.getElementById('student_id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('roll_no').value = '';
    document.getElementById('class').value = '';
    document.getElementById('email').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('date_of_birth').value = '';
    document.getElementById('address').value = '';
    document.getElementById('photo').value = '';
    document.getElementById('form_action').value = 'add';
    document.getElementById('submit-btn').textContent = 'Add Student';
    document.getElementById('form-title').textContent = 'Add New Student';
}
</script>

<?php include 'footer.php'; ?>