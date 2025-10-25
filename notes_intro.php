<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
  header("Location: index.php");
  exit;
}

$student_name = $_SESSION['student_name'] ?? 'Student';

// Deskripsi CLO
$cloDescriptions = [
  "CLO1" => "Apply fundamental of Database Management System (DBMS), relational data model and normalization concepts in database development process.",
  "CLO2" => "Show a well-structured database using the database query to manipulate a database with an appropriate commercial DBMS in solving an organization’s requirements."
];

// Topik dan CLO yang diliputi
$topicsCLO = [
  1 => ["CLO1"],
  2 => ["CLO1", "CLO2"],
  3 => ["CLO1", "CLO2"],
  4 => ["CLO1", "CLO2"],
  5 => ["CLO1", "CLO2"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notes Introduction - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f7fa;
      margin: 0;
      padding: 0;
    }

    header {
      background: #1e90ff;
      color: #fff;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .logout {
      background: #fff;
      color: #1e90ff;
      padding: 8px 15px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }

    main {
      max-width: 1100px;
      margin: 30px auto;
      padding: 0 16px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .topic-card {
      background: #fff;
      border: 1px solid #cfe4ff;
      border-radius: 14px;
      padding: 18px 20px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
      transition: 0.2s;
      display: flex;
      align-items: flex-start;
      gap: 15px;
    }

    .topic-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .topic-card i {
      font-size: 26px;
      color: #1e90ff;
      margin-top: 4px;
    }

    .topic-info h3 {
      margin: 0 0 8px;
      color: #1e90ff;
      font-size: 18px;
    }

    .topic-info p {
      margin-top: 6px;
      color: #333;
      font-size: 14.5px;
    }

    .clo-list span {
      background: #e8f3ff;
      color: #1e90ff;
      font-weight: bold;
      margin-right: 8px;
      padding: 4px 10px;
      border-radius: 8px;
      cursor: pointer;
      position: relative;
    }

    /* Tooltip (hover text) */
    .clo-list span:hover::after {
      content: attr(data-desc);
      position: absolute;
      top: 125%;
      left: 0;
      background: #333;
      color: #fff;
      padding: 8px 10px;
      border-radius: 6px;
      font-size: 13px;
      width: 280px;
      white-space: normal;
      box-shadow: 0 3px 10px rgba(0,0,0,0.15);
      z-index: 10;
    }

    .next-btn {
      background: #1e90ff;
      color: white;
      text-decoration: none;
      text-align: center;
      padding: 12px 18px;
      border-radius: 8px;
      font-weight: bold;
      width: 180px;
      margin: 20px auto 0;
      display: block;
      transition: background 0.2s;
    }

    .next-btn:hover {
      background: #187bcd;
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="logo.png" alt="LearnDBuddy Logo" class="logo" style="height:50px;">
      <h1>Notes Overview</h1>
    </div>
    <a href="student-dashboard.php" class="logout">Back</a>
  </header>

  <main>
    <?php foreach ($topicsCLO as $topic => $clos): ?>
      <div class="topic-card">
        <i class="fas fa-book"></i>
        <div class="topic-info">
          <h3>Topic <?= $topic ?></h3>
          <div class="clo-list">
            <?php foreach ($clos as $clo): ?>
              <span data-desc="<?= htmlspecialchars($cloDescriptions[$clo]) ?>">
                <?= $clo ?>
              </span>
            <?php endforeach; ?>
          </div>
          <p>This topic has covered <?= implode(', ', $clos) ?>.</p>
        </div>
      </div>
    <?php endforeach; ?>

    <a href="view_notes.php" class="next-btn">Next →</a>
  </main>
</body>
</html>
