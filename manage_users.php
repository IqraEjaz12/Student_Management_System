<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// User Management portion has been removed from the application.
header("Location: dashboard.php");
exit;
$error_message = '';
$success_message = '';
$form_data = [];

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_GET['error']) && $_GET['error'] === 'photo_required') {
    $error_message = 'Please upload a profile photo when adding a new user.';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $form_data = $_POST;

    if ($action == 'add_user') {
        $username = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
        $password = trim($_POST['password'] ?? '');
        $role = mysqli_real_escape_string($conn, trim($_POST['role'] ?? ''));
        $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
        $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
        $roll_no = mysqli_real_escape_string($conn, trim($_POST['roll_no'] ?? ''));
        $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
        $class = mysqli_real_escape_string($conn, trim($_POST['class'] ?? ''));
        $subject = mysqli_real_escape_string($conn, trim($_POST['subject'] ?? ''));
        $date_of_birth = $_POST['date_of_birth'] ?? '';
        $address = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));

        if ($username === '' || $password === '' || $role === '') {
            $error_message = 'Please fill in all required fields.';
        }

        if ($role === 'admin') {
            // Admin only needs username and password.
            if ($username === '' || $password === '') {
                $error_message = 'Please provide a username and password for the admin account.';
            }
        } elseif ($role === 'teacher') {
            if ($name === '' || $email === '' || $subject === '') {
                $error_message = 'Please fill in all required fields for the teacher account.';
            }
        } elseif ($role === 'student') {
            if ($name === '' || $email === '' || $roll_no === '' || $class === '') {
                $error_message = 'Please fill in all required fields for the student account.';
            }
        }

        $photo = '';
        if (($role === 'teacher' || $role === 'student') && isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $target_dir = 'uploads/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $photo = $target_dir . basename($_FILES['photo']['name']);
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
                $error_message = 'Unable to upload the photo. Please try again.';
            }
        }

        if (!$error_message && ($role === 'teacher' || $role === 'student') && empty($photo)) {
            $error_message = 'Please upload a profile photo when adding a teacher or student account.';
        }

        if (!$error_message) {
            $existing_user = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
            if ($existing_user && mysqli_num_rows($existing_user) > 0) {
                $error_message = 'That username is already taken. Please choose a different username.';
            }
        }

        if (!$error_message && ($role === 'teacher' || $role === 'student')) {
            $existing_email = mysqli_query($conn, "SELECT id FROM teachers WHERE email = '$email' UNION SELECT id FROM students WHERE email = '$email'");
            if ($existing_email && mysqli_num_rows($existing_email) > 0) {
                $error_message = 'This email address is already registered. Please use a different email.';
            }
        }

        if (!$error_message) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
            if (!mysqli_query($conn, $sql)) {
                $error_message = 'Database error creating user: ' . mysqli_error($conn);
            } else {
                $user_id = mysqli_insert_id($conn);
                if ($role == 'teacher') {
                    $sql = "INSERT INTO teachers (user_id, name, email, phone, subject, photo) VALUES ($user_id, '$name', '$email', '$phone', '$subject', '$photo')";
                } elseif ($role == 'student') {
                    $sql = "INSERT INTO students (user_id, name, roll_no, class, email, phone, date_of_birth, address, photo) VALUES ($user_id, '$name', '$roll_no', '$class', '$email', '$phone', '$date_of_birth', '$address', '$photo')";
                } else {
                    $sql = '';
                }

                if ($sql && !mysqli_query($conn, $sql)) {
                    $error_message = 'Database error adding profile: ' . mysqli_error($conn);
                    mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
                }
            }
        }

        if (!$error_message) {
            if (!$success_message) {
                $success_message = 'User created successfully.';
            }
            $_SESSION['success_message'] = $success_message;
            header("Location: manage_users.php");
            exit;
        }
    } elseif ($action == 'update_credentials') {
        $user_id = intval($_POST['user_id']);
        $new_username = trim($_POST['new_username'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');

        if ($new_username !== '') {
            $new_username = mysqli_real_escape_string($conn, $new_username);
            mysqli_query($conn, "UPDATE users SET username = '$new_username' WHERE id = $user_id");
        }
        if ($new_password !== '') {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE id = $user_id");
        }

        header("Location: manage_users.php");
        exit;
    } elseif ($action == 'delete_user') {
        $user_id = intval($_POST['user_id']);

        // Delete from users table (cascade will handle others)
        mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
        header("Location: manage_users.php");
        exit;
    }
}

// Get next user ID
$next_id_result = mysqli_query($conn, "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'");
$next_id = 1; // default
if ($next_id_result && $row = mysqli_fetch_assoc($next_id_result)) {
    $next_id = $row['AUTO_INCREMENT'];
} else {
    // Fallback: get max ID + 1
    $max_id_result = mysqli_query($conn, "SELECT MAX(id) as max_id FROM users");
    if ($max_id_result && $row = mysqli_fetch_assoc($max_id_result)) {
        $next_id = ($row['max_id'] ?? 0) + 1;
    }
}

// Get all users
$users_result = mysqli_query($conn, "SELECT u.*,
                                     t.name as teacher_name, t.email as teacher_email, t.phone as teacher_phone, t.subject,
                                     s.name as student_name, s.email as student_email, s.phone as student_phone, s.roll_no, s.class
                                     FROM users u
                                     LEFT JOIN teachers t ON u.id = t.user_id
                                     LEFT JOIN students s ON u.id = s.user_id
                                     ORDER BY u.id");

if (!$users_result) {
    $error_message = 'Database error: ' . mysqli_error($conn);
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>User Management</h2>

    <?php if ($error_message): ?>
        <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <!-- Add User Form -->
    <div class="form-card">
        <h3>Add New User <span style="font-size: 14px; color: #666;">(Next ID: <?php echo $next_id; ?>)</span></h3>
        <form action="manage_users.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_user">

            <div class="form-row">
                <select name="role" id="role" required onchange="toggleFields()">
                    <option value="">Select Role</option>
                    <option value="admin" <?php echo (isset($form_data['role']) && $form_data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="teacher" <?php echo (isset($form_data['role']) && $form_data['role'] === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                    <option value="student" <?php echo (isset($form_data['role']) && $form_data['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                </select>
                <div id="role_help" style="font-size: 12px; color: #666; margin-top: 5px;">Please select a role to show relevant fields</div>
            </div>
            <div class="form-row">
                <input type="text" name="name" id="name_field" placeholder="Full Name" value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" style="display:none;">
                <input type="email" name="email" id="email_field" placeholder="Email" value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" style="display:none;">
            </div>
            <div class="form-row">
                <input type="text" name="username" id="username_field" placeholder="Username" required value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>" style="display:none;">
                <input type="password" name="password" id="password_field" placeholder="Password" required style="display:none;">
            </div>
            <div class="form-row">
                <input type="tel" name="phone" id="phone_field" placeholder="Phone Number" value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>" style="display:none;">
                <input type="text" name="subject" id="subject_field" placeholder="Subject (for teachers)" value="<?php echo htmlspecialchars($form_data['subject'] ?? ''); ?>" style="display:none;">
            </div>
            <div class="form-row" id="student_fields" style="display:none;">
                <input type="text" name="roll_no" id="roll_no_field" placeholder="Roll No" required value="<?php echo htmlspecialchars($form_data['roll_no'] ?? ''); ?>">
                <input type="text" name="class" id="class_field" placeholder="Class" required value="<?php echo htmlspecialchars($form_data['class'] ?? ''); ?>">
            </div>
            <div class="form-row" id="student_extra_fields" style="display:none;">
                <input type="date" name="date_of_birth" id="dob_field" placeholder="Date of Birth" value="<?php echo htmlspecialchars($form_data['date_of_birth'] ?? ''); ?>">
                <input type="text" name="address" id="address_field" placeholder="Address" value="<?php echo htmlspecialchars($form_data['address'] ?? ''); ?>">
            </div>
            <div class="form-row" id="photo_row" style="display:none;">
                <label for="photo" style="display:block; margin-bottom:5px; font-weight:500;">Profile Photo</label>
                <input type="file" name="photo" id="photo" accept="image/*">
            </div>

            <button type="submit" id="submit_btn" class="btn-primary" style="display:none;">Add User</button>
        </form>
    </div>

    <!-- Users Table -->
    <div class="table-card">
        <h3>All Users</h3>
        <table id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($users_result && mysqli_num_rows($users_result) > 0) {
                    while ($user = mysqli_fetch_assoc($users_result)) {
                        $name = '';
                        $email = '';
                        $phone = '';
                        $details = '';

                        if ($user['role'] == 'teacher') {
                            $name = $user['teacher_name'];
                            $email = $user['teacher_email'];
                            $phone = $user['teacher_phone'];
                            $details = $user['subject'] ? "Subject: {$user['subject']}" : '';
                        } elseif ($user['role'] == 'student') {
                            $name = $user['student_name'];
                            $email = $user['student_email'];
                            $phone = $user['student_phone'];
                            $details = "Roll: {$user['roll_no']}, Class: {$user['class']}";
                        }

                        echo "<tr>";
                        echo "<td>{$user['id']}</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td>" . ucfirst($user['role']) . "</td>";
                        echo "<td>" . htmlspecialchars($name) . "</td>";
                        echo "<td>" . htmlspecialchars($email) . "</td>";
                        echo "<td>" . htmlspecialchars($phone) . "</td>";
                        echo "<td>" . htmlspecialchars($details) . "</td>";
                        echo "<td>
                            <button onclick='editCredentials({$user['id']})' class='btn-edit'>Reset Credentials</button>
                            <button onclick='deleteUser({$user['id']})' class='btn-delete'>Delete</button>
                        </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' style='text-align: center; color: #666;'>No users found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reset Credentials Modal -->
<div id="resetModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Reset Username / Password</h3>
        <form id="resetForm" action="manage_users.php" method="POST">
            <input type="hidden" name="action" value="update_credentials">
            <input type="hidden" name="user_id" id="reset_user_id">
            <input type="text" name="new_username" id="new_username" placeholder="New Username">
            <input type="password" name="new_password" id="new_password" placeholder="New Password">
            <p style="font-size:13px; color:#718096; margin-bottom:12px;">Leave blank to keep current value.</p>
            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<script>
function toggleFields() {
    const role = document.getElementById('role').value;
    const roleHelp = document.getElementById('role_help');
    const nameField = document.getElementById('name_field');
    const usernameField = document.getElementById('username_field');
    const passwordField = document.getElementById('password_field');
    const emailField = document.getElementById('email_field');
    const phoneField = document.getElementById('phone_field');
    const subjectField = document.getElementById('subject_field');
    const rollNoField = document.getElementById('roll_no_field');
    const classField = document.getElementById('class_field');
    const dobField = document.getElementById('dob_field');
    const addressField = document.getElementById('address_field');
    const studentFields = document.getElementById('student_fields');
    const studentExtraFields = document.getElementById('student_extra_fields');
    const photoRow = document.getElementById('photo_row');
    const submitBtn = document.getElementById('submit_btn');

    // Hide all fields initially
    roleHelp.style.display = role ? 'none' : 'block';
    nameField.style.display = 'none';
    usernameField.style.display = 'none';
    passwordField.style.display = 'none';
    emailField.style.display = 'none';
    phoneField.style.display = 'none';
    subjectField.style.display = 'none';
    rollNoField.style.display = 'none';
    classField.style.display = 'none';
    dobField.style.display = 'none';
    addressField.style.display = 'none';
    studentFields.style.display = 'none';
    studentExtraFields.style.display = 'none';
    photoRow.style.display = 'none';
    submitBtn.style.display = 'none';

    // Make fields required conditionally
    nameField.required = false;
    usernameField.required = false;
    passwordField.required = false;
    emailField.required = false;
    phoneField.required = false;
    subjectField.required = false;
    rollNoField.required = false;
    classField.required = false;
    dobField.required = false;
    addressField.required = false;

    if (role) {
        submitBtn.style.display = 'block';
        usernameField.style.display = 'block';
        passwordField.style.display = 'block';
        usernameField.required = true;
        passwordField.required = true;

        if (role === 'admin') {
            // Admin only needs username and password.
        } else {
            nameField.style.display = 'block';
            emailField.style.display = 'block';
            phoneField.style.display = 'block';
            nameField.required = true;
            emailField.required = true;
            phoneField.required = false;
        }

        if (role === 'teacher') {
            subjectField.style.display = 'block';
            subjectField.required = true;
            photoRow.style.display = 'block';
        } else if (role === 'student') {
            studentFields.style.display = 'block';
            studentExtraFields.style.display = 'block';
            rollNoField.required = true;
            classField.required = true;
            photoRow.style.display = 'block';
        }
    }
}

function editCredentials(userId) {
    document.getElementById('reset_user_id').value = userId;
    document.getElementById('new_username').value = '';
    document.getElementById('new_password').value = '';
    document.getElementById('resetModal').style.display = 'block';
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input name="action" value="delete_user"><input name="user_id" value="${userId}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function closeModal() {
    document.getElementById('resetModal').style.display = 'none';
}

window.addEventListener('DOMContentLoaded', function() {
    toggleFields();
});
</script>

<style>
.modal {
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 400px;
    border-radius: 8px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: black;
}

.success-message {
    background-color: #e6ffed;
    border: 1px solid #a3f7bf;
    color: #1f5f33;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 20px;
}
</style>

<?php include 'footer.php'; ?>