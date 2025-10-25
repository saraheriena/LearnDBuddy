<?php
session_start();
include 'db.php';
if (!isset($_SESSION['student_id'])) {
  header("Location: student_login.php");
  exit;
}
$student_name = $_SESSION['student_name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Challenge Options - LearnDBuddy</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
body{background:#f4f9ff;font-family:'Segoe UI',sans-serif;margin:0}
header{background:linear-gradient(90deg,#1e90ff,#4facfe);color:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
header h1{margin:0;font-size:22px}
.logout{background:#ff4d4d;color:#fff;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:600}
.main{max-width:800px;margin:auto;padding:40px;display:grid;grid-template-columns:1fr 1fr;gap:30px;}
.card{background:#fff;padding:30px;border-radius:14px;text-align:center;box-shadow:0 6px 16px rgba(0,0,0,.08);}
.card i{font-size:50px;color:#1e90ff;margin-bottom:16px;}
.card h2{color:#1e90ff;}
button{background:#1e90ff;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:600;}
button:hover{background:#005bb5;}
</style>
</head>
<body>
<header>
  <h1>Hello, <?= htmlspecialchars($student_name) ?></h1>
  <a href="student-dashboard.php" class="logout">Back</a>
</header>

<div class="main">
  <div class="card">
    <h2>View Quiz Result </h2>
    <p>View your quiz marks.</p>
    <a href="student_results.php"><button>Start Now</button></a>
  </div>

  <div class="card">
    <h2>View Challenge Results</h2>
    <p>View your performance and feedback from completed challenges.</p>
    <a href="student_challenge_result.php"><button>View Results</button></a>
  </div>
</div>
</body>
</html>
