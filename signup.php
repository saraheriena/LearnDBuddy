<?php
include "db.php";

// ambil semua kelas dari table classes
$class_query = $conn->query("SELECT class_id, class_name FROM classes");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - LearnDBuddy</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f6ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .signup-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 360px;
            text-align: center;
        }
        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .extra-field {
            display: none;
        }
        p {
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="signup-box">
    <h2>Create Account</h2>
    <form id="signupForm" method="post" action="lecturer_signup_process.php">
        <!-- Role selection -->
        <select name="role" id="role" required>
            <option value="">-- Select Role --</option>
            <option value="lecturer">Lecturer</option>
            <option value="student">Student</option>
        </select>

        <input type="text" name="fullname" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>

        <!-- Student-only fields -->
        <div id="studentFields" class="extra-field">
            <input type="text" name="matric_no" placeholder="Matric Number">
            <select name="class_id">
                <option value="">-- Select Class --</option>
                <?php while ($row = $class_query->fetch_assoc()): ?>
                    <option value="<?= $row['class_id']; ?>"><?= htmlspecialchars($row['class_name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Soalan keselamatan bawah sekali -->
        <select name="security_question" required>
            <option value="">-- Select Security Question --</option>
            <option value="birth_place">What is your birth place?</option>
            <option value="father_name">What is your father's name?</option>
            <option value="pet_name">What is your first pet's name?</option>
        </select>

        <input type="text" name="security_answer" placeholder="Your Answer" required>

        <button type="submit">Sign Up</button>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>

<script>
const roleSelect = document.getElementById("role");
const studentFields = document.getElementById("studentFields");
const form = document.getElementById("signupForm");

roleSelect.addEventListener("change", function() {
    if (this.value === "student") {
        studentFields.style.display = "block";
        form.action = "student_signup_process.php";
    } else if (this.value === "lecturer") {
        studentFields.style.display = "none";
        form.action = "lecturer_signup_process.php";
    } else {
        studentFields.style.display = "none";
    }
});
</script>

</body>
</html>
