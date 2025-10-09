<?php
session_start();
include "db.php";
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT fullname FROM students WHERE student_id=$student_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard - LearnDBuddy</title>
<link rel="stylesheet" href="style.css">
<style>
.dashboard-container {
  max-width: 900px;
  margin: 40px auto;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  padding: 30px;
  text-align: center;
}
.dashboard-container h2 {
  color: #007bff;
}
.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 30px;
}
.menu-grid a {
  background: #f4f7ff;
  padding: 20px;
  border-radius: 10px;
  text-decoration: none;
  color: #333;
  font-weight: bold;
  transition: 0.3s;
}
.menu-grid a:hover {
  background: #007bff;
  color: white;
}
.logout {
  color: red;
  font-weight: bold;
  text-decoration: none;
  position: absolute;
  top: 20px;
  right: 30px;
}
</style>
</head>
<body>

<a href="logout.php" class="logout">Logout</a>

<div class="dashboard-container">
  <h2>Welcome, <?= htmlspecialchars($student['fullname']); ?> 👋</h2>
  <h4>Student Dashboard</h4>

  <div class="menu-grid">
    <a href="view_notes.php">📖 View Notes</a>
    <a href="view_quiz.php">🧩 Take Quiz</a>
    <a href="view_result_student.php">📊 View My Results</a>
  </div>
</div>

</body>
</html>
