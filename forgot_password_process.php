<?php
session_start();
include "db.php";

// mapping key → text user-friendly
$question_map = [
    'birth_place' => "What is your birth place?",
    'father_name' => "What is your father's name?",
    'pet_name' => "What is your first pet's name?"
];

// Step 1: Check email
if (isset($_POST['check_email'])) {
    $email = trim($_POST['email']);

    $sql = "SELECT email, security_question, security_answer, 'lecturer' AS role FROM lecturers WHERE email=? 
            UNION 
            SELECT email, security_question, security_answer, 'student' AS role FROM students WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_role'] = $row['role'];
        $_SESSION['reset_question'] = $row['security_question'];
        $question_text = $question_map[$_SESSION['reset_question']] ?? "Security Question";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - LearnDBuddy</title>
<style>
body{font-family:Arial;background:#f2f6ff;display:flex;justify-content:center;align-items:center;height:100vh;}
.box{background:white;padding:40px;border-radius:12px;box-shadow:0 0 10px rgba(0,0,0,0.1);width:360px;text-align:center;}
input,button{width:100%;padding:10px;margin:8px 0;border:1px solid #ccc;border-radius:6px;font-size:15px;}
button{background:#007bff;color:white;font-weight:bold;border:none;cursor:pointer;}
button:hover{background:#0056b3;}
.small-text{font-size:13px;color:#555;margin-bottom:5px;}
.error-text{font-size:13px;color:red;text-align:left;}
.valid-text{font-size:13px;color:green;text-align:left;}
button:disabled{background:#ccc;cursor:not-allowed;}
</style>
</head>
<body>
<div class="box">
    <h2>Reset Password</h2>
    <form method="post" id="resetForm">
        <p><b>Security Question:</b><br><?= htmlspecialchars($question_text) ?></p>
        <input type="text" name="security_answer" placeholder="Your Answer" required>
        <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
        <div id="passwordHint" class="small-text">Password must be at least 8 characters, include uppercase, lowercase, number & symbol.</div>
        <div id="passwordMsg"></div>
        <button type="submit" name="reset_password" id="resetBtn" disabled>Reset Password</button>
    </form>
</div>

<script>
// client-side validation
const passwordInput = document.getElementById("new_password");
const msg = document.getElementById("passwordMsg");
const resetBtn = document.getElementById("resetBtn");

const pattern = /^(?=.[a-z])(?=.[A-Z])(?=.\d)(?=.[\W_]).{8,}$/;

passwordInput.addEventListener("input", () => {
    const value = passwordInput.value;
    if (value.length === 0) {
        msg.textContent = "";
        resetBtn.disabled = true;
        return;
    }
    if (pattern.test(value)) {
        msg.textContent = "✅ Strong password";
        msg.className = "valid-text";
        resetBtn.disabled = false;
    } else {
        msg.textContent = "❌ Must have uppercase, lowercase, number & symbol (min 8 chars)";
        msg.className = "error-text";
        resetBtn.disabled = true;
    }
});
</script>
</body>
</html>
<?php
        exit;
    } else {
        header("Location: forgot_password.php?error=❌ Email not found.");
        exit;
    }
}

// Step 2: Reset password (server-side validation)
if (isset($_POST['reset_password'])) {
    if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_role'])) {
        header("Location: forgot_password.php?error=Session expired. Try again.");
        exit;
    }

    $email = $_SESSION['reset_email'];
    $role = $_SESSION['reset_role'];
    $answer = trim($_POST['security_answer']);
    $new_password = trim($_POST['new_password']);

    // server-side password validation
    if (!preg_match('/^(?=.[a-z])(?=.[A-Z])(?=.\d)(?=.[\W_]).{8,}$/', $new_password)) {
        echo "<script>alert('❌ Invalid password! Must include uppercase, lowercase, number & symbol (min 8 chars).'); window.history.back();</script>";
        exit;
    }

    $table = ($role === 'lecturer') ? 'lecturers' : 'students';

    $stmt = $conn->prepare("SELECT * FROM $table WHERE email=? AND security_answer=? LIMIT 1");
    $stmt->bind_param("ss", $email, $answer);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE $table SET password=? WHERE email=?");
        $update->bind_param("ss", $hashed, $email);
        $update->execute();

        session_destroy();
        echo "<script>alert('✅ Password reset successful! Please login again.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('❌ Security answer incorrect!'); window.location='forgot_password.php';</script>";
    }
}
?>