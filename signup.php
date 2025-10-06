<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lecturer Sign Up - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="login-page">
    <div class="login-container">
      <h2>Lecturer Sign Up</h2>
      <form action="signup_process.php" method="post">
  <input type="text" name="fullname" placeholder="Full Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>

  <select name="security_question" required>
    <option value="" disabled selected>-- Select Security Question --</option>
    <option value="mother_name">What is your mother's name?</option>
    <option value="father_name">What is your father's name?</option>
    <option value="birth_place">Where were you born?</option>
  </select>
  <input type="text" name="security_answer" placeholder="Your Answer" required>

  <button type="submit">Sign Up</button>
</form>

      <p style="margin-top: 10px; font-size: 14px; text-align:center;">
        Already have an account? <a href="index.php" style="color:#1e90ff;">Login here</a>
      </p>
    </div>
  </div>
</body>
</html>
