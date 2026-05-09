<?php
include 'db.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ===== ADD =====
if ($action === 'add') {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $roll_no = mysqli_real_escape_string($conn, $_POST['roll_no']);
    $class   = mysqli_real_escape_string($conn, $_POST['class']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);
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

    $sql = "INSERT INTO students (name, roll_no, class, email, phone, date_of_birth, address, photo)
            VALUES ('$name', '$roll_no', '$class', '$email', '$phone', '$date_of_birth', '$address', '$photo')";
    mysqli_query($conn, $sql);
    header("Location: manage_students.php");
    exit;
}

// ===== UPDATE =====
if ($action === 'update') {
    $id      = intval($_POST['id']);
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $roll_no = mysqli_real_escape_string($conn, $_POST['roll_no']);
    $class   = mysqli_real_escape_string($conn, $_POST['class']);
    $email   = mysqli_real_escape_string($conn, $_POST['email']);
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);
    $date_of_birth = $_POST['date_of_birth'];
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Handle photo upload
    $photo_sql = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $photo = $target_dir . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
        $photo_sql = ", photo='$photo'";
    }

    $sql = "UPDATE students
            SET name='$name', roll_no='$roll_no', class='$class',
                email='$email', phone='$phone', date_of_birth='$date_of_birth',
                address='$address' $photo_sql
            WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: manage_students.php");
    exit;
}

// ===== DELETE =====
if ($action === 'delete') {
    $id = intval($_GET['id']);
    mysqli_query($conn, "DELETE FROM students WHERE id=$id");
    header("Location: manage_students.php");
    exit;
}
?>