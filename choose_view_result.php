<?php
session_start();
include 'db.php';
if (!isset($_SESSION['lecturer_id'])) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Choose Results View - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: Segoe UI, sans-serif;
      background: #f4f9ff;
      margin: 0;
    }
    header {
      background: linear-gradient(90deg, #1e90ff, #4facfe);
      color: #fff;
      padding: 16px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .logout {
      background: #ff4d4d;
      color: #fff;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
    }
    .wrap {
      max-width: 600px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h2 {
      margin-top: 0;
      color: #333;
    }
    a.btn {
      display: inline-block;
      margin: 12px;
      padding: 14px 22px;
      border-radius: 8px;
      text-decoration: none;
      background: #1e90ff;
      color: #fff;
      font-weight: 700;
      transition: background 0.2s;
    }
    a.btn:hover {
      background: #005bb5;
    }
  </style>
</head>
<body>
  <header>
    <h1>Choose Results View</h1>
    <a href="dashboard.php" class="logout">Back</a>
  </header>

  <div class="wrap">
    <h2>Which results do you want to see?</h2>
    <p>Quiz results or Assessment results.</p>
    <a class="btn" href="view-result.php">Quiz Results</a>
    <a class="btn" href="view-pb-results.php">Assessment Results</a>
  </div>
</body>
</html>
