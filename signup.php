<?php
include "db.php";
$class_query = $conn->query("SELECT class_id, class_name FROM classes");
include "signup_process.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up - LearnDBuddy</title>
<style>
body { font-family: Arial; background:#f2f6ff; display:flex; justify-content:center; align-items:center; height:100vh; }
.signup-box { background:white; padding:40px; border-radius:12px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:360px; text-align:center; }
h2 { color:#007bff; margin-bottom:20px; }
input, select, button { width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px; font-size:15px; box-sizing:border-box; }
select { background-color: white; appearance: auto; }
button { background:#007bff; color:white; border:none; font-weight:bold; cursor:pointer; }
button:hover { background:#0056b3; }
.extra-field { display:none; }
.error-msg { color:red; font-size:13px; margin:2px 0 5px 0; text-align:left; }
.success-msg { color:green; font-size:14px; margin-bottom:8px; text-align:left; }
</style>
</head>
<body>
<div class="signup-box">
    <h2>Create Account</h2>

    <?php if (!empty($success_message)): ?>
        <div class="success-msg"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <form id="signupForm" method="post">
        <select name="role" id="role" required>
            <option value="">-- Select Role --</option>
            <option value="lecturer" <?= (isset($role) && $role=="lecturer")?"selected":"" ?>>Lecturer</option>
            <option value="student" <?= (isset($role) && $role=="student")?"selected":"" ?>>Student</option>
        </select>
        <div class="error-msg"><?= htmlspecialchars($errors['role']) ?></div>

        <input type="text" name="name" placeholder="Full Name" value="<?= htmlspecialchars($name ?? '') ?>" required>
        <div class="error-msg"><?= htmlspecialchars($errors['name']) ?></div>

        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>" required>
        <div class="error-msg"><?= htmlspecialchars($errors['email']) ?></div>

        <input type="password" name="password" id="password" placeholder="Password" required>
        <div class="error-msg"><?= htmlspecialchars($errors['password']) ?></div>

        <div id="studentFields" class="extra-field">
    <input type="text" name="matric_no" placeholder="Matric Number"
        value="<?= htmlspecialchars($matric_no ?? '') ?>"
        <?= (isset($role) && $role=="student") ? 'required' : '' ?>>

    <select name="class_id" id="class_id" <?= (isset($role) && $role=="student") ? 'required' : '' ?>>
        <option value="">-- Select Class --</option>
        <?php while ($row = $class_query->fetch_assoc()): ?>
            <option value="<?= $row['class_id'] ?>" <?= (isset($class_id) && $class_id==$row['class_id']) ? "selected" : "" ?>>
                <?= htmlspecialchars($row['class_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>


        <select name="security_question" required>
            <option value="">-- Select Security Question --</option>
            <option value="birth_place" <?= (isset($question) && $question=="birth_place")?"selected":"" ?>>What is your birth place?</option>
            <option value="father_name" <?= (isset($question) && $question=="father_name")?"selected":"" ?>>What is your father's name?</option>
            <option value="pet_name" <?= (isset($question) && $question=="pet_name")?"selected":"" ?>>What is your first pet's name?</option>
        </select>
        <input type="text" name="security_answer" placeholder="Your Answer" value="<?= htmlspecialchars($answer ?? '') ?>" required>
        <div class="error-msg"><?= htmlspecialchars($errors['security']) ?></div>

        <button type="submit">Sign Up</button>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>

<script>
const roleSelect = document.getElementById("role");
const studentFields = document.getElementById("studentFields");

roleSelect.addEventListener("change", () => {
    if (roleSelect.value === "student") {
        studentFields.style.display = "block";
    } else {
        studentFields.style.display = "none";
        document.getElementsByName("matric_no")[0].value = "";
        document.getElementById("class_id").selectedIndex = 0;
    }
});

// show fields if page reload with student selected
if(roleSelect.value === "student"){
    studentFields.style.display = "block";
}
</script>
</body>
</html>