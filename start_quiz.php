<?php
session_start();
include "db.php";

if (!isset($_SESSION['student_id'])) {
  header("Location: student_login.php");
  exit;
}

$student_id = $_SESSION['student_id'];
$quiz_id = $_GET['quiz_id'] ?? 0;

// Ambil semua soalan ikut quiz
$q = $conn->prepare("SELECT * FROM questions WHERE quiz_id=?");
$q->bind_param("i", $quiz_id);
$q->execute();
$res = $q->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $score = 0;
  $total = count($_POST['answers']);

  foreach ($_POST['answers'] as $qid => $ans) {
    $check = $conn->query("SELECT correct_answer FROM questions WHERE question_id=$qid AND quiz_id=$quiz_id");
    $c = $check->fetch_assoc();
    if (strtoupper($ans) == $c['correct_answer']) $score++;
  }

  $percentage = ($score / $total) * 100;
  $conn->query("INSERT INTO results (student_id, quiz_id, score, total, percentage)
                VALUES ($student_id, $quiz_id, $score, $total, $percentage)");

  echo "<script>alert('Quiz submitted! You scored $score / $total ($percentage%)'); window.location='student_results.php';</script>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Start Quiz - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f5f7fa;
      margin: 0;
    }

    header {
      background: #1e90ff;
      color: white;
      padding: 15px 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    header h1 {
      margin: 0;
      font-size: 20px;
    }

    header a {
      color: white;
      text-decoration: none;
      background: #005bb5;
      padding: 8px 14px;
      border-radius: 6px;
      font-weight: 600;
    }

    header a:hover {
      background: #004799;
    }

    main {
      max-width: 800px;
      margin: 40px auto;
      background: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: left;
    }

    .question-block {
      margin-bottom: 30px;
    }

    .question-block p {
      font-weight: 600;
      color: #333;
      margin-bottom: 12px;
      font-size: 17px;
    }

    .option {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      text-align: left;
      margin: 6px 0;
      padding: 10px 12px;
      border: 1px solid #ddd;
      border-radius: 6px;
      width: 100%;
      background: #fafafa;
      box-sizing: border-box;
      transition: background 0.2s, border-color 0.2s;
    }

    .option:hover {
      background: #eef6ff;
      border-color: #1e90ff;
    }

    .option input[type="radio"] {
      margin-right: 12px;
      transform: scale(1.2);
      accent-color: #1e90ff;
    }

    button {
      background: #1e90ff;
      color: white;
      border: none;
      padding: 12px 22px;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
    }

    button:hover {
      background: #005bb5;
    }
  </style>
</head>
<body>
  <header>
    <h1>Quiz</h1>
    <a href="take_quiz.php">Back</a>
  </header>

  <main>
    <form method="post">
      <?php 
      $no = 1;
      while ($row = $res->fetch_assoc()): ?>
        <div class="question-block">
          <p><?= $no++ ?>. <?= htmlspecialchars($row['question_text']); ?></p>
          <?php foreach (['A','B','C','D'] as $opt): ?>
            <label class="option">
              <input type="radio" name="answers[<?= $row['question_id']; ?>]" value="<?= $opt; ?>" required>
              <?= "$opt. " . htmlspecialchars($row["option_" . strtolower($opt)]); ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endwhile; ?>
      <button type="submit">Submit Quiz</button>
    </form>
  </main>
</body>
</html>
