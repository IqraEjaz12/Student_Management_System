<?php
$host     = "localhost";
$user     = "root";
$password = "";        // XAMPP ka default password blank hota hai
$database = "student_db";

$conn = mysqli_connect($host, $user, $password);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$db_selected = mysqli_select_db($conn, $database);
if (!$db_selected) {
    if (!mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        die("Database creation failed: " . mysqli_error($conn));
    }
    mysqli_select_db($conn, $database);
}

// Create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher', 'student') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE,
        name VARCHAR(100) NOT NULL,
        roll_no VARCHAR(20) UNIQUE NOT NULL,
        class VARCHAR(50),
        email VARCHAR(100),
        phone VARCHAR(20),
        date_of_birth DATE,
        address TEXT,
        photo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE,
        phone VARCHAR(20),
        subject VARCHAR(100),
        photo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        teacher_id INT,
        FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        course_id INT,
        enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        UNIQUE(student_id, course_id)
    )",
    "CREATE TABLE IF NOT EXISTS grades (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        course_id INT,
        grade VARCHAR(10),
        semester VARCHAR(50),
        grade_date DATE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        course_id INT,
        date DATE,
        status ENUM('present', 'absent', 'late'),
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        UNIQUE(student_id, course_id, date)
    )",
    "CREATE TABLE IF NOT EXISTS fees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        amount DECIMAL(10,2),
        due_date DATE,
        paid BOOLEAN DEFAULT FALSE,
        paid_date DATE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $sql) {
    mysqli_query($conn, $sql);
}

// Add missing columns for user mapping and teacher photos if not present
$check_teacher_user_id = mysqli_query($conn, "SHOW COLUMNS FROM teachers LIKE 'user_id'");
if (mysqli_num_rows($check_teacher_user_id) == 0) {
    mysqli_query($conn, "ALTER TABLE teachers ADD COLUMN user_id INT UNIQUE NULL");
}

$check_teacher_photo = mysqli_query($conn, "SHOW COLUMNS FROM teachers LIKE 'photo'");
if (mysqli_num_rows($check_teacher_photo) == 0) {
    mysqli_query($conn, "ALTER TABLE teachers ADD COLUMN photo VARCHAR(255) NULL");
}

$check_student_user_id = mysqli_query($conn, "SHOW COLUMNS FROM students LIKE 'user_id'");
if (mysqli_num_rows($check_student_user_id) == 0) {
    mysqli_query($conn, "ALTER TABLE students ADD COLUMN user_id INT UNIQUE NULL");
}

// Insert default admin user if not exists
$admin_check = mysqli_query($conn, "SELECT id FROM users WHERE username='admin'");
if (mysqli_num_rows($admin_check) == 0) {
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('admin', '$hashed_password', 'admin')");
}

// Insert sample teacher if not exists
$teacher_check = mysqli_query($conn, "SELECT id FROM users WHERE username='teacher1'");
if (mysqli_num_rows($teacher_check) == 0) {
    $hashed_password = password_hash('teacher123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('teacher1', '$hashed_password', 'teacher')");
    $teacher_id = mysqli_insert_id($conn);
    mysqli_query($conn, "INSERT INTO teachers (user_id, name, email, subject, photo) VALUES ($teacher_id, 'John Smith', 'john@example.com', 'Mathematics', '')");
}

// Insert sample student if not exists
$student_check = mysqli_query($conn, "SELECT id FROM users WHERE username='student1'");
if (mysqli_num_rows($student_check) == 0) {
    $hashed_password = password_hash('student123', PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('student1', '$hashed_password', 'student')");
    $student_id = mysqli_insert_id($conn);
    mysqli_query($conn, "INSERT INTO students (user_id, name, roll_no, email, class, photo) VALUES ($student_id, 'Alice Johnson', 'STU001', 'alice@example.com', 'Class 10', '')");
}
?>