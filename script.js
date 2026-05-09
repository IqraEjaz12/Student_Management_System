// Search function — table mein search karna
function searchStudents() {
    const input = document.getElementById("searchInput").value.toUpperCase();
    const rows  = document.querySelectorAll("#studentsTable tbody tr");

    rows.forEach(row => {
        const text = row.innerText.toUpperCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

// Edit button pe click hone par form mein data aajata hai
function fillForm(id, name, roll_no, cls, email, phone) {
    document.getElementById("student_id").value    = id;
    document.getElementById("name").value          = name;
    document.getElementById("roll_no").value       = roll_no;
    document.getElementById("class").value         = cls;
    document.getElementById("email").value         = email;
    document.getElementById("phone").value         = phone;
    document.getElementById("form_action").value   = "update";
    document.getElementById("submit-btn").textContent = "Update Student";
    document.getElementById("form-title").textContent = "Edit Student";

    // Page ke upar scroll karo
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Form clear karna
function clearForm() {
    document.getElementById("student_id").value    = "";
    document.getElementById("name").value          = "";
    document.getElementById("roll_no").value       = "";
    document.getElementById("class").value         = "";
    document.getElementById("email").value         = "";
    document.getElementById("phone").value         = "";
    document.getElementById("form_action").value   = "add";
    document.getElementById("submit-btn").textContent = "Add Student";
    document.getElementById("form-title").textContent = "Add New Student";
}

// Dashboard Charts
document.addEventListener('DOMContentLoaded', function() {
    // Students by Class Chart
    const classCtx = document.getElementById('classChart');
    if (classCtx) {
        new Chart(classCtx, {
            type: 'pie',
            data: {
                labels: ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 5'],
                datasets: [{
                    data: [12, 19, 15, 8, 6],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // Enrollment Trends Chart
    const enrollmentCtx = document.getElementById('enrollmentChart');
    if (enrollmentCtx) {
        new Chart(enrollmentCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Enrollments',
                    data: [10, 15, 20, 25, 30, 35],
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});