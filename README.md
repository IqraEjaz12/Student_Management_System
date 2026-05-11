"# Student Management System

A comprehensive web-based student management system built with PHP, MySQL, and JavaScript.

## Features

- **Role-based Access Control**: Admin, Teacher, and Student roles with different interfaces
- **User Management**: Admin can create/manage users and reset passwords
- **Student Management**: Add, edit, delete, and view student information
- **Teacher Management**: Manage teacher profiles and assignments
- **Course Management**: Create and manage courses with teacher assignments
- **Student Enrollment**: Enroll students in courses
- **Grade Management**: Teachers can assign and manage student grades
- **Attendance Tracking**: Mark and track student attendance
- **Fee Management**: Track student fee payments and dues
- **Reports Generation**: Comprehensive reports with CSV export
- **Dashboard Analytics**: Statistics and charts for each role
- **Responsive Design**: Works on desktop and mobile devices

## Installation

1. Install XAMPP or any PHP/MySQL server
2. Copy the project to `htdocs` directory
3. Start Apache and MySQL in XAMPP
4. Open `http://localhost/Student_Management_System` in browser
5. The database and tables will be created automatically
6. Run `test.php` to verify everything is working: `http://localhost/Student_Management_System/test.php`

## Default Login Credentials

### Admin
- **Username:** `admin`
- **Password:** `admin123`

### Teacher (Sample)
- **Username:** `teacher1`
- **Password:** `teacher123`

### Student (Sample)
- **Username:** `student1`
- **Password:** `student123`

## User Roles & Permissions

### Admin
- Full system access
- Manage users (create, reset passwords, delete)
- All CRUD operations on students, teachers, courses
- View all reports
- System configuration

### Teacher
- View assigned courses
- Manage grades for their courses
- Take attendance for their courses
- View student lists for their courses
- Access teacher-specific reports

### Student
- View enrolled courses
- Check grades and attendance
- View fee status
- Access personal dashboard

## Technologies Used

- PHP 7+
- MySQL
- HTML5/CSS3
- JavaScript
- Chart.js for charts

## File Structure

- `index.php` - Landing page
- `login.php` - User authentication
- `dashboard.php` - Admin dashboard
- `teacher_dashboard.php` - Teacher dashboard
- `student_dashboard.php` - Student dashboard
- `manage_users.php` - User management (Admin only)
- `manage_students.php` - Student CRUD operations
- `manage_teachers.php` - Teacher management
- `manage_courses.php` - Course management
- `enroll_students.php` - Student enrollment
- `grades.php` - Grade management
- `attendance.php` - Attendance tracking
- `fees.php` - Fee management
- `reports.php` - Reports page
- `report_*.php` - Individual report pages
- `export_*.php` - CSV export files
- `my_*.php` - Student/Teacher personal views
- `db.php` - Database connection and setup
- `header.php` - Common header with role-based navigation
- `footer.php` - Common footer
- `style.css` - Stylesheet
- `script.js` - JavaScript functions" 
