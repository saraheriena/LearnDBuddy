<?php
session_start();
include "db.php";

// --- Setup percubaan login ---
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['lock_time'])) $_SESSION['lock_time'] = 0;

// Reset lock bila masa habis
if ($_SESSION['lock_time'] > 0 && time() >= $_SESSION['lock_time']) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lock_time'] = 0;
}

$locked = false;
$remaining = 0;
$error_message = '';
$email_value = '';

// Semak kalau masih dikunci
if ($_SESSION['lock_time'] > time()) {
    $locked = true;
    $remaining = $_SESSION['lock_time'] - time();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !$locked) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $email_value = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

    // Semak lecturer dulu
    $stmt = $conn->prepare("SELECT * FROM lecturers WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    // Jika tiada lecturer, semak student pula
    if ($res->num_rows === 0) {
        $stmt = $conn->prepare("SELECT * FROM students WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
    }

    // Jika masih tiada — maksudnya email tak wujud langsung
    if ($res->num_rows === 0) {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lock_time'] = time() + 120; // 2 minit
            $locked = true;
            $remaining = 120;
            $error_message = "🚫 Too many failed attempts. Account locked for 2 minutes.";
        } else {
            $left = 3 - $_SESSION['login_attempts'];
            $error_message = "⚠ Email does not exist. Attempts left: $left";
        }
    } else {
        $row = $res->fetch_assoc();

        // ✅ Kalau password betul
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_email'] = $row['email'];
            $_SESSION['role'] = isset($row['class_id']) ? 'student' : 'lecturer';

            if ($_SESSION['role'] === 'student') {
                $_SESSION['student_id'] = $row['student_id'];
                $_SESSION['student_name'] = $row['fullname'];
            } else {
                $_SESSION['lecturer_id'] = $row['lecturer_id'];
                $_SESSION['lecturer_name'] = $row['fullname'];
            }

            $_SESSION['login_attempts'] = 0;
            $_SESSION['lock_time'] = 0;
            header("Location: intro.php");
            exit;
        } 
        // ❌ Kalau password salah
        else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['lock_time'] = time() + 120; // kunci 2 minit
                $locked = true;
                $remaining = 120;
                $error_message = "🚫 Too many failed attempts. Account locked for 2 minutes.";
            } else {
                $left = 3 - $_SESSION['login_attempts'];
                $error_message = "❌ Invalid email or password. Attempts left: $left";
            }
        }
    }
}

// Pastikan nilai lock dikemaskini walaupun reload
if ($_SESSION['lock_time'] > time()) {
    $locked = true;
    $remaining = $_SESSION['lock_time'] - time();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .login-container { max-width:420px; margin:60px auto; background:#fff; padding:26px; border-radius:10px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
    input[type="email"], input[type="password"] { width:100%; padding:12px 14px; margin:10px 0; border-radius:8px; border:1px solid #d6e2f0; background:#f5f9ff; box-sizing:border-box; }
    button[type="submit"] { width:100%; padding:12px; border-radius:8px; border:0; background:#1e90ff; color:#fff; font-weight:600; cursor:pointer; }
    .field-error { color:#c72b2b; font-size:13px; margin-top:6px; }
    .locked-info { color:#c72b2b; font-weight:600; text-align:center; margin-bottom:10px; }
  </style>
</head>
<body class="login-page">
  <div class="login-container">
    <div class="logo-header" style="text-align:center; margin-bottom:6px;">
      <img src="logo.png" alt="LearnDBuddy Logo" width="80" height="80">
    </div>

    <h2 style="text-align:center; color:#1e90ff; margin:6px 0 14px;">Login</h2>

    <?php if ($locked): ?>
      <p class="locked-info">
        🚫 Too many failed attempts. Please wait <span id="countdown"><?= $remaining ?></span> seconds.
      </p>
    <?php endif; ?>

    <form action="index.php" method="post" id="loginForm" autocomplete="off">
      <input type="email" id="email" name="email" placeholder="Email" required value="<?= $email_value ?>">
      <input type="password" id="password" name="password" placeholder="Password" required <?= $locked ? 'disabled' : '' ?>>

      <?php if (!empty($error_message)): ?>
        <div class="field-error"><small><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></small></div>
      <?php endif; ?>

      <button type="submit" id="loginBtn" <?= $locked ? 'disabled' : '' ?>>Login</button>
    </form>

    <p style="margin-top: 14px; font-size: 14px; text-align:center;">
      Don’t have an account? <a href="signup.php" style="color:#1e90ff;">Sign up here</a><br>
      <a href="forgot_password.php" style="color:red;">Forgot Password?</a>
    </p>
  </div>

  <script>
    (function(){
      var locked = <?= $locked ? 'true' : 'false' ?>;
      if (locked) {
        var remain = <?= (int)$remaining ?>;
        var span = document.getElementById('countdown');
        var pass = document.getElementById('password');
        var btn = document.getElementById('loginBtn');
        if (pass) pass.disabled = true;
        if (btn) btn.disabled = true;
        var iv = setInterval(function(){
          remain--;
          if (span) span.textContent = remain;
          if (remain <= 0) {
            clearInterval(iv);
            location.reload();
          }
        }, 1000);
      }
    })();
  </script>
</body>
</html>