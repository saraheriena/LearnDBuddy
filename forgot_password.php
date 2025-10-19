<?php
session_start();
include "db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password - LearnDBuddy</title>
<style>
body { font-family: Arial; background:#f2f6ff; display:flex; justify-content:center; align-items:center; height:100vh; }
.box { background:white; padding:40px; border-radius:12px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:360px; text-align:center; }
h2 { color:#007bff; margin-bottom:20px; }
input, button { width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px; font-size:15px; box-sizing:border-box; }
button { background:#007bff; color:white; border:none; font-weight:bold; cursor:pointer; }
button:hover { background:#0056b3; }
.error-msg { color:red; font-size:13px; margin-top:5px; }
.success-msg { color:green; font-size:14px; margin-bottom:8px; }
</style>
</head>
<body>
<div class="box">
    <h2>Forgot Password</h2>
    <form action="forgot_password_process.php" method="post">
        <input type="email" name="email" placeholder="Enter your registered email" required>
        <button type="submit" name="check_email">Check Email</button>
    </form>
    <?php if (isset($_GET['error'])): ?>
        <div class="error-msg"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
</div>
</body>
</html>