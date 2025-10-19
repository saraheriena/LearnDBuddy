<?php
session_start();
include "db.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$quiz_id = intval($_GET['quiz_id'] ?? 0);
if ($quiz_id <= 0) die("Invalid quiz ID.");

// --- Ambil result latest student untuk quiz ni ---
$result_q = $conn->prepare("SELECT * FROM results WHERE student_id=? AND quiz_id=? ORDER BY result_id DESC LIMIT 1");
$result_q->bind_param("ii", $student_id, $quiz_id);
$result_q->execute();
$result_res = $result_q->get_result();

if ($result_res->num_rows == 0) {
    die("No result found for this quiz. Make sure you have submitted the quiz first.");
}
$result = $result_res->fetch_assoc();

// --- Ambil soalan & jawapan student ---
$questions_q = $conn->prepare("SELECT * FROM questions WHERE quiz_id=?");
$questions_q->bind_param("i", $quiz_id);
$questions_q->execute();
$questions_res = $questions_q->get_result();

$answers_q = $conn->prepare("SELECT question_id, answer FROM student_answers WHERE student_id=? AND quiz_id=?");
$answers_q->bind_param("ii", $student_id, $quiz_id);
$answers_q->execute();
$answers_res = $answers_q->get_result();

$student_answers = [];
while ($row = $answers_res->fetch_assoc()) {
    $student_answers[$row['question_id']] = $row['answer'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Review Quiz</title>
<style>
body { font-family:'Segoe UI', sans-serif; background:#f5f7fa; margin:0; }
header { background:#1e90ff; color:white; padding:15px 25px; display:flex; justify-content:space-between; align-items:center;}
header h1 { margin:0; font-size:20px; }
header a.button { color:white; text-decoration:none; background:#28a745; padding:8px 14px; border-radius:6px; font-weight:600; }
header a.button:hover { background:#218838; }
main { max-width:800px; margin:40px auto; background:white; padding:30px 40px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
.question-block { margin-bottom:25px; }
.question-block p { font-weight:600; color:#333; font-size:17px; }
.option { display:flex; padding:10px 12px; border-radius:6px; margin:6px 0; }
.correct { background:#d4edda; border:1px solid #28a745; }
.wrong { background:#f8d7da; border:1px solid #dc3545; }
.option span { margin-left:10px; }
.my-result-btn { display:inline-block; margin-top:20px; background:#1e90ff; color:white; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:600; }
.my-result-btn:hover { background:#005bb5; }
</style>
</head>
<body>
<header>
  <h1>Review Quiz</h1>
</header>
<main>
<h2>Your Score: <?= $result['score']; ?> / <?= $result['total']; ?> (<?= round($result['percentage'],2); ?>%)</h2>
<hr>

<?php while ($q = $questions_res->fetch_assoc()):
    $qid = $q['question_id'];
    $student_ans = $student_answers[$qid] ?? '';
    $correct_ans = $q['correct_answer'];
    $is_correct = strtoupper($student_ans) == $correct_ans;
?>
<div class="question-block">
  <p><?= htmlspecialchars($q['question_text']); ?></p>
  <?php foreach (['A','B','C','D'] as $opt):
      $opt_text = $q['option_'.strtolower($opt)];
      $cls = '';
      if ($opt == $correct_ans) $cls = 'correct';
      elseif ($opt == strtoupper($student_ans) && !$is_correct) $cls = 'wrong';
  ?>
  <div class="option <?= $cls; ?>">
    <strong><?= $opt; ?>.</strong> <span><?= htmlspecialchars($opt_text); ?></span>
  </div>
  <?php endforeach; ?>
  <p><strong>Your Answer:</strong> <?= $student_ans ?: 'Not Answered'; ?></p>
  <?php if (!$is_correct): ?>
  <p><strong>Correct Answer:</strong> <?= $correct_ans; ?></p>
  <?php endif; ?>
</div>
<?php endwhile; ?>

<!-- Button untuk pergi ke My Results -->
<a href="student_results.php" class="my-result-btn">My Results</a>
</main>
</body>
</html>