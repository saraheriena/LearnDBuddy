<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
  header("Location: index.php");
  exit;
}

$class_id = $_SESSION['class_id'] ?? null;

// === Konfigurasi URL projek & folder uploads ===
$projectBaseUrl = '/learnDBuddy'; // ubah ikut folder projek sebenar
$uploadsUrl     = $projectBaseUrl . '/uploads/';

// === Ayat ringkasan untuk setiap topik ===
$topicDescriptions = [
  1 => "Learn the basic concepts of databases, DBMS purpose, components, and how data is stored, managed, and secured.",
  2 => "Understand how data is organized in tables using keys and relational algebra for data manipulation.",
  3 => "Learn to design databases using ERD and apply normalization to remove redundancy and ensure data consistency.",
  4 => "Use SQL commands (DDL, DML) to create, update, and query databases effectively.",
  5 => "Understand transactions, concurrency control, and recovery to maintain data consistency and integrity."
];


// === Utiliti ringkas ===
function short_snippet($text, $len = 110)
{
  $plain = trim(preg_replace('/\s+/', ' ', strip_tags($text ?? '')));
  if (mb_strlen($plain) <= $len) return $plain;
  return mb_substr($plain, 0, $len - 1) . '…';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>View Notes - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* Gaya senarai topik */
    .topics-list {
      max-width: 1100px;
      margin: 12px auto 8px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding: 0 10px;
    }

    .topic-item {
      display: block;
      background: #fff;
      border: 1px solid #b9dafd;
      border-radius: 14px;
      padding: 14px 16px;
      text-decoration: none;
      color: inherit;
      box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
      transition: transform .2s, box-shadow .2s, background .2s, border-color .2s;
    }

    .topic-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(0, 0, 0, .1);
    }

    .topic-row {
      display: flex;
      gap: 10px;
      align-items: flex-start;
    }

    .topic-icon {
      flex: 0 0 auto;
      font-size: 22px;
      color: #1e90ff;
      margin-top: 2px;
    }

    .topic-text {
      flex: 1;
      min-width: 0;
    }

    .topic-title {
      margin: 0 0 6px;
      color: #1e90ff;
      font-weight: 800;
      font-size: 16px;
      line-height: 1.2;
    }

    .topic-desc {
      margin: 0;
      color: #444;
      font-size: 14px;
      line-height: 1.5;
    }
  </style>
</head>

<body>
  <header>
    <div class="header-left">
      <img src="logo.png" alt="LearnDBuddy Logo" class="logo" style="height:50px;">
      <h1>Class Notes</h1>
    </div>
    <a href="student-dashboard.php" class="logout">Back</a>
  </header>

  <main class="dashboard" style="flex-direction:column;align-items:stretch;gap:16px;padding-top:16px;">
    <!-- SENARAI BUTANG TOPIK -->
    <nav class="topics-list">
      <?php for ($t = 1; $t <= 5; $t++): ?>
        <a class="topic-item" href="topic<?= $t ?>.php">
          <div class="topic-row">
            <i class="fas fa-book topic-icon"></i>
            <div class="topic-text">
              <h3 class="topic-title">Topic <?= $t ?></h3>
              <p class="topic-desc">
                <?= htmlspecialchars(short_snippet($topicDescriptions[$t] ?? '', 120), ENT_QUOTES, 'UTF-8') ?>
              </p>
            </div>
          </div>
        </a>
      <?php endfor; ?>
    </nav>
  </main>
</body>
</html>
