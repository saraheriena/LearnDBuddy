<?php
session_start();

// kalau belum login, balik ke login
if (!isset($_SESSION['user_email']) || !isset($_SESSION['role'])) {
  header("Location: index.php");
  exit;
}

// ambil role dan nama
$role = $_SESSION['role'];
if ($role === 'student') {
  $name = $_SESSION['student_name'];
} else {
  $name = $_SESSION['lecturer_name'];
}

// bila user tekan Continue
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if ($role === 'lecturer') {
    header("Location: dashboard.php");
  } else {
    header("Location: student-dashboard.php");
  }
  exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Welcome to LearnDBuddy</title>
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  margin: 0;
  height: 100vh;
  background: linear-gradient(135deg, #e3f2ff, #cde4ff); /* biru lembut gradien */
  display: flex;
  justify-content: center;
  align-items: center;
  color: #333;
}

.container {
  background: #ffffff;
  padding: 45px;
  border-radius: 18px;
  max-width: 520px;
  text-align: center;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  border: 1px solid #e0ebff;
}

h1 {
  font-size: 28px;
  color: #1e90ff;
  margin-bottom: 15px;
}

p {
  font-size: 16px;
  line-height: 1.6;
  color: #555;
}

button {
  margin-top: 25px;
  background: linear-gradient(90deg, #1e90ff, #4facfe);
  color: #fff;
  font-weight: 600;
  border: none;
  padding: 12px 35px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s;
}
button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(30,144,255,0.4);
}

.logo {
  width: 90px;
  height: 90px;
  margin-bottom: 15px;
}

footer {
  margin-top: 25px;
  font-size: 13px;
  color: #888;
}
</style>
</head>
<body>
  <div class="container">
    <img src="logo.png" alt="LearnDBuddy Logo" class="logo">
    <h1>Welcome to LearnDBuddy</h1>
    <p>
      Hi <strong><?= htmlspecialchars($name) ?></strong>! 👋<br><br>
      LearnDBuddy is your interactive learning platform — designed to help you 
      access quizzes, review results, and track performance easily.<br>
      Whether you are a student improving your knowledge or a lecturer managing quizzes,
      everything starts here.
    </p>
    <form method="post">
      <button type="submit">Continue</button>
    </form>
    <footer>
      &copy; <?= date("Y") ?> LearnDBuddy. All rights reserved.
    </footer>
  </div>
</body>
</html>