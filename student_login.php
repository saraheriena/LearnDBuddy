<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Login - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
  <div class="login-container">
    <div class="logo-header" style="text-align:center; margin-bottom:15px;">
      <img src="logo.png" alt="LearnDBuddy Logo" width="80" height="80">
    </div>

    <h2>Student Login</h2>
    <form action="student_login_process.php" method="post">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>

    <p style="margin-top: 10px; font-size: 14px; text-align:center;">
      Don’t have an account? <a href="student_signup.php" style="color:#1e90ff;">Sign up here</a><br>
      <a href="index.php" style="color:red;">Login as Lecturer</a>
    </p>
  </div>

  <?php if (isset($_GET['error'])): ?>
  <div id="toast" class="toast show">
    <?= htmlspecialchars($_GET['error']); ?>
  </div>
  <script>
    setTimeout(()=>document.getElementById('toast').classList.remove('show'),4000);
  </script>
  <?php endif; ?>
</body>
</html>
