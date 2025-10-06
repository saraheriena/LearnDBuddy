<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
  <div class="login-container">
    <h2>Forgot Password</h2>
    <form action="forgot_password_process.php" method="post">
  <input type="email" name="email" placeholder="Enter your email" required>
  <button type="submit" name="check_email">Next</button>
</form>


    <p style="margin-top: 10px; font-size: 14px; text-align:center;">
      Remembered? <a href="index.php" style="color:#1e90ff;">Back to Login</a>
    </p>
  </div>

  <!-- Popup untuk error -->
  <?php if (isset($_GET['error'])): ?>
  <div class="popup show">
    ⚠️ <?php echo htmlspecialchars($_GET['error']); ?>
  </div>
  <script>
    setTimeout(() => {
      document.querySelector(".popup").style.display = "none";
    }, 4000);
  </script>
  <?php endif; ?>
</body>
</html>
