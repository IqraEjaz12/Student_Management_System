// Search function — table mein search karna
function searchStudent() {
    const input = document.getElementById("searchInput").value.toUpperCase();
    const rows  = document.querySelectorAll("#studentTable tbody tr");

    rows.forEach(row => {
        const text = row.innerText.toUpperCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

// Edit button pe click hone par form mein data aajata hai
function fillForm(id, name, roll_no, cls, email, phone) {
    document.getElementById("sid").value          = id;
    document.getElementById("name").value         = name;
    document.getElementById("roll_no").value      = roll_no;
    document.getElementById("class").value        = cls;
    document.getElementById("email").value        = email;
    document.getElementById("phone").value        = phone;
    document.getElementById("form-action").value  = "update";
    document.getElementById("submit-btn").textContent = "Update Student";
    document.getElementById("form-title").textContent = "Edit Student";

    // Page ke upar scroll karo
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Form clear karna
function clearForm() {
    document.getElementById("sid").value          = "";
    document.getElementById("name").value         = "";
    document.getElementById("roll_no").value      = "";
    document.getElementById("class").value        = "";
    document.getElementById("email").value        = "";
    document.getElementById("phone").value        = "";
    document.getElementById("form-action").value  = "add";
    document.getElementById("submit-btn").textContent = "Add Student";
    document.getElementById("form-title").textContent = "Add New Student";
}