<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Manage Teachers';

$error_message = '';
$form_data = [];

// Handle edit request
$edit_teacher = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $result = mysqli_query($conn, "SELECT * FROM teachers WHERE id = $edit_id");
    $edit_teacher = mysqli_fetch_assoc($result);
}

if (isset($_GET['error']) && $_GET['error'] === 'photo_required') {
    $error_message = 'Please upload a profile photo when adding a new teacher.';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $form_data = $_POST;

    if ($action == 'add' || $action == 'update') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $subject = mysqli_real_escape_string($conn, $_POST['subject']);

        // Handle photo upload
        $photo = '';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $target_dir = 'uploads/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $photo = $target_dir . basename($_FILES['photo']['name']);
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
                $error_message = 'Unable to upload the photo. Please try again.';
            }
        }

        if ($action == 'add' && empty($photo)) {
            $error_message = 'Please upload a profile photo when adding a new teacher.';
        }

        $teacher_id = $action === 'update' ? intval($_POST['id']) : 0;
        if (!$error_message) {
            $check_sql = "SELECT id FROM teachers WHERE email = '$email'" . ($action === 'update' ? " AND id <> $teacher_id" : "");
            $check_result = mysqli_query($conn, $check_sql);
            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $error_message = 'This email is already in use. Please enter a unique email.';
            }
        }

        if (!$error_message) {
            if ($action == 'add') {
                $sql = "INSERT INTO teachers (name, email, phone, subject, photo)
                        VALUES ('$name', '$email', '$phone', '$subject', '$photo')";
            } else {
                $sql = "UPDATE teachers SET name='$name', email='$email', phone='$phone', subject='$subject'";
                if ($photo) {
                    $sql .= ", photo='$photo'";
                }
                $sql .= " WHERE id=$teacher_id";
            }

            if (!mysqli_query($conn, $sql)) {
                $error_message = 'Database error: ' . mysqli_error($conn);
            } else {
                header("Location: manage_teachers.php");
                exit;
            }
        }
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM teachers WHERE id=$id");
        header("Location: manage_teachers.php");
        exit;
    }
}

$form_action = $edit_teacher ? 'update' : ($form_data['action'] ?? 'add');
$form_title = $form_action === 'update' ? 'Edit Teacher' : 'Add New Teacher';
?>
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Manage Teachers</h2>

    <!-- Add/Edit Form -->
    <div class="form-card">
        <h3 id="form-title"><?php echo htmlspecialchars($form_title); ?></h3>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form action="manage_teachers.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="teacher_id" value="<?php echo htmlspecialchars($form_data['id'] ?? $edit_teacher['id'] ?? ''); ?>">
            <input type="hidden" name="action" id="form_action" value="<?php echo htmlspecialchars($form_action); ?>">

            <div class="form-row">
                <input type="text" name="name" id="name" placeholder="Full Name" value="<?php echo htmlspecialchars($form_data['name'] ?? $edit_teacher['name'] ?? ''); ?>" required>
                <input type="email" name="email" id="email" placeholder="Email Address" value="<?php echo htmlspecialchars($form_data['email'] ?? $edit_teacher['email'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <input type="text" name="phone" id="phone" placeholder="Phone Number" value="<?php echo htmlspecialchars($form_data['phone'] ?? $edit_teacher['phone'] ?? ''); ?>">
                <input type="text" name="subject" id="subject" placeholder="Subject" value="<?php echo htmlspecialchars($form_data['subject'] ?? $edit_teacher['subject'] ?? ''); ?>" required>
            </div>
            <div class="form-row">
                <label for="photo" style="display: block; margin-bottom: 5px; font-weight: 500;">Profile Photo</label>
                <?php if ($edit_teacher && ($edit_teacher['photo'] ?? '')): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php echo $edit_teacher['photo']; ?>" width="100" height="100" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                        <p style="font-size: 12px; color: #718096; margin-top: 5px;">Current photo - upload new to replace</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="photo" id="photo" accept="image/*" style="margin-top: 0;" <?php echo $edit_teacher ? '' : 'required'; ?>>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-primary" id="submit-btn"><?php echo $form_action === 'update' ? 'Update Teacher' : 'Add Teacher'; ?></button>
                <button type="button" class="btn-secondary" onclick="clearForm()">Clear</button>
            </div>
        </form>
    </div>

    <!-- Teachers Table -->
    <div class="table-card">
        <h3>All Teachers</h3>
        <input type="text" id="searchInput" placeholder="Search teachers..." onkeyup="searchTeachers()" class="search-bar">
        <table id="teachersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM teachers ORDER BY id ASC");
                $serial = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $serial++;
                    $photo = ($row['photo'] ?? '') ? "<img src='{$row['photo']}' width='50' height='50' style='border-radius:50%;'>" : "No Photo";
                    echo "<tr>
                        <td>{$serial}</td>
                        <td>$photo</td>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['email']) . "</td>
                        <td>" . htmlspecialchars($row['phone']) . "</td>
                        <td>" . htmlspecialchars($row['subject']) . "</td>
                        <td>
                            <button class='btn-edit' onclick='editTeacher({$row['id']})'>Edit</button>
                            <button class='btn-delete' onclick='deleteTeacher({$row['id']})'>Delete</button>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchTeachers() {
    const input = document.getElementById("searchInput").value.toUpperCase();
    const rows = document.querySelectorAll("#teachersTable tbody tr");
    rows.forEach(row => {
        const text = row.innerText.toUpperCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

function editTeacher(id) {
    window.location.href = `manage_teachers.php?edit=${id}`;
}

function deleteTeacher(id) {
    if (confirm('Delete this teacher?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input name="action" value="delete"><input name="id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function clearForm() {
    document.getElementById('teacher_id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('email').value = '';
    document.getElementById('phone').value = '';
    document.getElementById('subject').value = '';
    document.getElementById('photo').value = '';
    document.getElementById('form_action').value = 'add';
    document.getElementById('submit-btn').textContent = 'Add Teacher';
    document.getElementById('form-title').textContent = 'Add New Teacher';
}
</script>

<?php include 'footer.php'; ?>