<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Manage Students';

$error_message = '';
$form_data = [];

// Handle edit request
$edit_student = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM students WHERE id = $edit_id");
    $edit_student = mysqli_fetch_assoc($result);
}

if (isset($_GET['error']) && $_GET['error'] === 'photo_required') {
    $error_message = 'Please upload a profile photo when adding a new student.';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $form_data = $_POST;

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
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
                $error_message = 'Unable to upload the photo. Please try again.';
            }
        }

        if ($action == 'add' && empty($photo)) {
            $error_message = 'Please upload a profile photo when adding a new student.';
        }

        $student_id = $action === 'update' ? intval($_POST['id']) : 0;
        if (!$error_message) {
            $check_sql = "SELECT id FROM students WHERE roll_no = '$roll_no'" . ($action === 'update' ? " AND id <> $student_id" : "");
            $check_result = mysqli_query($conn, $check_sql);
            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $error_message = 'This roll number is already in use. Please enter a unique roll number.';
            }
        }

        if (!$error_message) {
            if ($action == 'add') {
                $sql = "INSERT INTO students (name, roll_no, class, email, phone, date_of_birth, address, photo)
                        VALUES ('$name', '$roll_no', '$class', '$email', '$phone', '$date_of_birth', '$address', '$photo')";
            } else {
                $sql = "UPDATE students SET name='$name', roll_no='$roll_no', class='$class',
                        email='$email', phone='$phone', date_of_birth='$date_of_birth',
                        address='$address'" . ($photo ? ", photo='$photo'" : "") . " WHERE id=$student_id";
            }

            if (!mysqli_query($conn, $sql)) {
                $error_message = 'Database error: ' . mysqli_error($conn);
            } else {
                header("Location: manage_students.php");
                exit;
            }
        }
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM students WHERE id=$id");
        header("Location: manage_students.php");
        exit;
    }
}

$form_action = $edit_student ? 'update' : ($form_data['action'] ?? 'add');
$form_title = $form_action === 'update' ? 'Edit Student' : 'Add New Student';
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Manage Students</h2>

    <!-- Add/Edit Form -->
    <div class="form-card">
        <h3 id="form-title"><?php echo htmlspecialchars($form_title); ?></h3>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form action="manage_students.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="student_id" value="<?php echo htmlspecialchars($form_data['id'] ?? $edit_student['id'] ?? ''); ?>">
            <input type="hidden" name="action" id="form_action" value="<?php echo htmlspecialchars($form_action); ?>">

            <div class="form-row">
                <input type="text" name="name" id="name" placeholder="Full Name" value="<?php echo htmlspecialchars($form_data['name'] ?? $edit_student['name'] ?? ''); ?>" required>
                <input type="text" name="roll_no" id="roll_no" placeholder="Roll Number" value="<?php echo htmlspecialchars($form_data['roll_no'] ?? $edit_student['roll_no'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <input type="text" name="class" id="class" placeholder="Class" value="<?php echo htmlspecialchars($form_data['class'] ?? $edit_student['class'] ?? ''); ?>">
                <input type="email" name="email" id="email" placeholder="Email Address" value="<?php echo htmlspecialchars($form_data['email'] ?? $edit_student['email'] ?? ''); ?>">
            </div>
            <div class="form-row">
                <input type="text" name="phone" id="phone" placeholder="Phone Number" value="<?php echo htmlspecialchars($form_data['phone'] ?? $edit_student['phone'] ?? ''); ?>">
                <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo htmlspecialchars($form_data['date_of_birth'] ?? $edit_student['date_of_birth'] ?? ''); ?>">
            </div>
            <textarea name="address" id="address" placeholder="Address" rows="3"><?php echo htmlspecialchars($form_data['address'] ?? $edit_student['address'] ?? ''); ?></textarea>
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
                <button type="submit" class="btn-primary" id="submit-btn"><?php echo $form_action === 'update' ? 'Update Student' : 'Add Student'; ?></button>
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