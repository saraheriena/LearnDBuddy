<?php
session_start();
include "db.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$assessment_id = isset($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : 0;

// load assessment
$stmt = $conn->prepare("SELECT * FROM assessments WHERE assessment_id = ?");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$assessment = $stmt->get_result()->fetch_assoc();

if (!$assessment) {
    die("<p style='text-align:center;color:red;font-family:Segoe UI;'>❌ Invalid assessment.</p>");
}

$type = $assessment['type'];
$duration = in_array($type, ['Practical Work 1', 'Practical Work 2']) ? 300 : 180; // seconds

// load questions
$qstmt = $conn->prepare("SELECT * FROM assessment_questions WHERE assessment_id = ? ORDER BY question_id ASC");
$qstmt->bind_param("i", $assessment_id);
$qstmt->execute();
$questions = $qstmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($assessment['title']) ?> — <?= htmlspecialchars($type) ?></title>
<style>
body{font-family:Segoe UI, sans-serif;background:#f4f8ff;margin:0;}
header{background:#0b71d0;color:#fff;padding:14px;text-align:center}
.container{max-width:900px;margin:28px auto;background:#fff;padding:22px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,0.06)}
.timer{ text-align:right;font-weight:700;color:#c0392b; margin-bottom:12px }
.question{padding:14px;border-radius:8px;margin-bottom:14px;background:#fbfdff;border:1px solid #eef6ff}
.q-title{font-weight:700;margin-bottom:8px}
.opt{display:block;padding:10px;border-radius:6px;margin-bottom:8px;border:1px solid #ddd;cursor:pointer}
.opt input{margin-right:8px}
button{background:#0b71d0;color:#fff;border:none;padding:10px 18px;border-radius:8px;cursor:pointer;font-weight:600}
button:hover{opacity:0.95}
.note{font-size:13px;color:#666;margin-bottom:12px}
</style>

<script>
let duration = <?= (int)$duration ?>;
let elapsed = 0;

function startTimer(){
  const timerEl = document.getElementById('timer');
  const form = document.getElementById('quizForm');
  const iv = setInterval(()=>{
    let m = Math.floor(duration/60);
    let s = duration%60;
    timerEl.textContent = `⏱ Time left: ${m}:${s < 10 ? '0'+s : s}`;
    duration--;
    elapsed++;
    document.getElementById('elapsed_time').value = elapsed;
    if(duration < 0){
      clearInterval(iv);
      alert("Time's up — submitting automatically.");
      form.submit();
    }
  },1000);
}

window.onload = startTimer;
</script>
</head>
<body>
<header>
  <h2><?= htmlspecialchars($assessment['title']) ?> — <?= htmlspecialchars($type) ?></h2>
</header>

<div class="container">
  <div class="timer" id="timer">⏱ Time left: --:--</div>
  <p class="note">Please answer all questions. If time runs out your answers will be auto-submitted. Late penalty applies per rules.</p>

  <?php if ($questions->num_rows > 0): ?>
  <form id="quizForm" method="post" action="submit_challenge.php">
    <input type="hidden" name="assessment_id" value="<?= $assessment_id ?>">
    <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
    <input type="hidden" id="elapsed_time" name="elapsed_time" value="0">

    <?php $i=1; while($row = $questions->fetch_assoc()): ?>
      <div class="question">
        <div class="q-title">Q<?= $i++ ?>. <?= htmlspecialchars($row['question_text']) ?></div>
        <label class="opt"><input type="radio" name="answer[<?= $row['question_id'] ?>]" value="A" required> <?= htmlspecialchars($row['option_a']) ?></label>
        <label class="opt"><input type="radio" name="answer[<?= $row['question_id'] ?>]" value="B"> <?= htmlspecialchars($row['option_b']) ?></label>
        <label class="opt"><input type="radio" name="answer[<?= $row['question_id'] ?>]" value="C"> <?= htmlspecialchars($row['option_c']) ?></label>
        <label class="opt"><input type="radio" name="answer[<?= $row['question_id'] ?>]" value="D"> <?= htmlspecialchars($row['option_d']) ?></label>
      </div>
    <?php endwhile; ?>

    <div style="text-align:center;margin-top:12px"><button type="submit">Submit Answers</button></div>
  </form>
  <?php else: ?>
    <p style="text-align:center;color:#666">No questions available for this assessment.</p>
  <?php endif; ?>
</div>
</body>
</html>
