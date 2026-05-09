<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Manage Teachers';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action == 'add' || $action == 'update') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $phone = mysqli_real_escape_string($conn, $_POST['phone']);
        $subject = mysqli_real_escape_string($conn, $_POST['subject']);
        $photo = '';

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $target_dir = 'uploads/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $photo = $target_dir . basename($_FILES['photo']['name']);
            move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
        }

        if ($action == 'add') {
            $sql = "INSERT INTO teachers (name, email, phone, subject, photo)
                    VALUES ('$name', '$email', '$phone', '$subject', '$photo')";
        } else {
            $id = intval($_POST['id']);
            $sql = "UPDATE teachers SET name='$name', email='$email', phone='$phone', subject='$subject'";
            if ($photo !== '') {
                $sql .= ", photo='$photo'";
            }
            $sql .= " WHERE id=$id";
        }
        mysqli_query($conn, $sql);
        header("Location: manage_teachers.php");
        exit;
    } elseif ($action == 'delete') {
        $id = intval($_POST['id']);
        mysqli_query($conn, "DELETE FROM teachers WHERE id=$id");
        header("Location: manage_teachers.php");
        exit;
    }
}
?>

<?php include 'header.php'; ?>

<div class="manage-section">
    <h2>Manage Teachers</h2>

    <!-- Add/Edit Form -->
    <div class="form-card">
        <h3 id="form-title">Add New Teacher</h3>
        <form action="manage_teachers.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="teacher_id">
            <input type="hidden" name="action" id="form_action" value="add">

            <div class="form-row">
                <input type="text" name="name" id="name" placeholder="Full Name" required>
                <input type="email" name="email" id="email" placeholder="Email Address" required>
            </div>
            <div class="form-row">
                <input type="text" name="phone" id="phone" placeholder="Phone Number">
                <input type="text" name="subject" id="subject" placeholder="Subject" required>
            </div>
            <div class="form-row">
                <label for="photo" style="display:block; margin-bottom:5px; font-weight:500;">Profile Photo</label>
                <input type="file" name="photo" id="photo" accept="image/*" required>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-primary" id="submit-btn">Add Teacher</button>
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
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT * FROM teachers ORDER BY id DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>{$row['id']}</td>
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
    document.getElementById('form_action').value = 'add';
    document.getElementById('submit-btn').textContent = 'Add Teacher';
    document.getElementById('form-title').textContent = 'Add New Teacher';
}
</script>

<?php include 'footer.php'; ?>