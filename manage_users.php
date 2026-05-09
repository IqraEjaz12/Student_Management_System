<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'User Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add_user') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);

        $photo = '';
        if (($role === 'teacher' || $role === 'student') && isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $target_dir = 'uploads/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $photo = $target_dir . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
        }

        if (($role === 'teacher' || $role === 'student') && empty($photo)) {
            header("Location: manage_users.php?error=photo_required");
            exit;
        }

        // Insert into users table
        $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
        mysqli_query($conn, $sql);
        $user_id = mysqli_insert_id($conn);

        // Insert into respective table if needed
        if ($role == 'teacher') {
            $sql = "INSERT INTO teachers (user_id, name, email, photo) VALUES ($user_id, '$name', '$email', '$photo')";
        } elseif ($role == 'student') {
            $roll_no = mysqli_real_escape_string($conn, $_POST['roll_no']);
            $sql = "INSERT INTO students (user_id, name, roll_no, email, photo) VALUES ($user_id, '$name', '$roll_no', '$email', '$photo')";
        } else {
            $sql = "";
        }
        if ($sql) {
            mysqli_query($conn, $sql);
        }

        header("Location: manage_users.php");
        exit;
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

// Get all users
$users_result = mysqli_query($conn, "SELECT u.*, t.name as teacher_name, s.name as student_name, COALESCE(t.email, s.email) AS email
                                     FROM users u
                                     LEFT JOIN teachers t ON u.id = t.user_id
                                     LEFT JOIN students s ON u.id = s.user_id
                                     ORDER BY u.id");
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>User Management</h2>

    <!-- Add User Form -->
    <div class="form-card">
        <h3>Add New User</h3>
        <form action="manage_users.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_user">

            <div class="form-row">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-row">
                <select name="role" id="role" required onchange="toggleFields()">
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            <div class="form-row">
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="roll_no" id="roll_no" placeholder="Roll No (for students)" style="display:none;">
            </div>
            <div class="form-row">
                <label for="photo" style="display:block; margin-bottom:5px; font-weight:500;">Profile Photo</label>
                <input type="file" name="photo" id="photo" accept="image/*">
            </div>

            <button type="submit" class="btn-primary">Add User</button>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($user = mysqli_fetch_assoc($users_result)) {
                    $name = '';
                    $email = '';

                    if ($user['role'] == 'teacher') {
                        $name = $user['teacher_name'];
                        $email = $user['email'] ?? '';
                    } elseif ($user['role'] == 'student') {
                        $name = $user['student_name'];
                        $email = $user['email'] ?? '';
                    }

                    echo "<tr>";
                    echo "<td>{$user['id']}</td>";
                    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                    echo "<td>" . ucfirst($user['role']) . "</td>";
                    echo "<td>" . htmlspecialchars($name) . "</td>";
                    echo "<td>" . htmlspecialchars($email) . "</td>";
                    echo "<td>
                        <button onclick='editCredentials({$user['id']})' class='btn-edit'>Reset Credentials</button>
                        <button onclick='deleteUser({$user['id']})' class='btn-delete'>Delete</button>
                    </td>";
                    echo "</tr>";
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
    const rollNo = document.getElementById('roll_no');
    const photo = document.getElementById('photo');
    rollNo.style.display = role === 'student' ? 'block' : 'none';
    photo.required = role === 'teacher' || role === 'student';
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
</style>

<?php include 'footer.php'; ?>