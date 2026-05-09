<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$page_title = 'Dashboard';

// Get statistics
$student_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students"))['count'];
$teacher_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM teachers"))['count'];
$course_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM courses"))['count'];
$enrollment_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM enrollments"))['count'];
?>

<?php include 'header.php'; ?>

<div class="dashboard">
    <h2>Dashboard</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Students</h3>
            <p class="stat-number"><?php echo $student_count; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Teachers</h3>
            <p class="stat-number"><?php echo $teacher_count; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Courses</h3>
            <p class="stat-number"><?php echo $course_count; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Enrollments</h3>
            <p class="stat-number"><?php echo $enrollment_count; ?></p>
        </div>
    </div>

    <div class="charts-container">
        <div class="chart-card">
            <h3>Students by Class</h3>
            <canvas id="classChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Enrollment Trends</h3>
            <canvas id="enrollmentChart"></canvas>
        </div>
    </div>
</div>

<script>
<?php
// Data for class chart
$class_data = mysqli_query($conn, "SELECT class, COUNT(*) as count FROM students GROUP BY class");
$classes = [];
$counts = [];
while ($row = mysqli_fetch_assoc($class_data)) {
    $classes[] = $row['class'] ?: 'No Class';
    $counts[] = $row['count'];
}
?>

const classChart = new Chart(document.getElementById('classChart'), {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($classes); ?>,
        datasets: [{
            data: <?php echo json_encode($counts); ?>,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
        }]
    }
});

<?php
// Enrollment data (simplified)
$enrollment_data = mysqli_query($conn, "SELECT DATE(enrolled_at) as date, COUNT(*) as count FROM enrollments GROUP BY DATE(enrolled_at) ORDER BY date DESC LIMIT 7");
$dates = [];
$enroll_counts = [];
while ($row = mysqli_fetch_assoc($enrollment_data)) {
    $dates[] = $row['date'];
    $enroll_counts[] = $row['count'];
}
$dates = array_reverse($dates);
$enroll_counts = array_reverse($enroll_counts);
?>

const enrollmentChart = new Chart(document.getElementById('enrollmentChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Enrollments',
            data: <?php echo json_encode($enroll_counts); ?>,
            borderColor: '#36A2EB',
            fill: false
        }]
    }
});
</script>

<?php include 'footer.php'; ?>