<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lecturer Login - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
  <div class="login-container">
   <div class="logo-header" style="text-align:center; margin-bottom:15px;">
  <img src="logo.png" alt="LearnDBuddy Logo" width="80" height="80">
</div>



    <h2>Login</h2>
    <form action="login.php" method="post">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>

    <p style="margin-top: 10px; font-size: 14px; text-align:center;">
      Didn’t sign up yet? <a href="signup.php" style="color:#1e90ff;">Sign up here</a><br>
      <a href="forgot_password.php" style="color:red;">Forgot Password?</a>
    </p>
  </div>

  <?php if (isset($_GET['error'])): ?>
  <script>
    window.onload = () => {
      alert("<?php echo addslashes($_GET['error']); ?>");
    };
  </script>
  <?php endif; ?>
</body>

</html>
