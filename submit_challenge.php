<?php
session_start();
include 'db.php';
if (!isset($_SESSION['student_id'])) { header("Location: student_login.php"); exit; }

$student_id = (int)$_SESSION['student_id'];
$assessment_id = isset($_POST['assessment_id']) ? (int)$_POST['assessment_id'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : '';
$elapsed = isset($_POST['elapsed_time']) ? (int)$_POST['elapsed_time'] : 0;
$answers = $_POST['answer'] ?? [];

// load questions
$stmt = $conn->prepare("SELECT question_id, question_text, option_a, option_b, option_c, option_d, correct_answer 
                        FROM assessment_questions WHERE assessment_id = ? ORDER BY question_id ASC");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$res = $stmt->get_result();

$total_q = $res->num_rows;
$correct = 0;
$questions = [];

while ($r = $res->fetch_assoc()) {
    $qid = (int)$r['question_id'];
    $user = isset($answers[$qid]) ? $answers[$qid] : null;
    $is_correct = ($user !== null && $user === $r['correct_answer']);
    if ($is_correct) $correct++;

    $questions[] = [
        'question_id' => $qid,
        'question_text' => $r['question_text'],
        'option_a' => $r['option_a'],
        'option_b' => $r['option_b'],
        'option_c' => $r['option_c'],
        'option_d' => $r['option_d'],
        'correct_answer' => $r['correct_answer'],
        'user_answer' => $user,
        'is_correct' => $is_correct
    ];
}

// kira peratus markah
$percentage = ($total_q > 0) ? round(($correct / $total_q) * 100, 2) : 0;

// masa maksimum
$duration_allowed = in_array($type, ['Practical Work 1','Practical Work 2']) ? 300 : 180;

// penalti kalau lambat
if ($elapsed > $duration_allowed) {
    $overtime = $elapsed - $duration_allowed;
    $penalty = floor($overtime / 10);
    $percentage = max(0, $percentage - $penalty);
}

// simpan keputusan individu
$ins = $conn->prepare("INSERT INTO assessment_results (student_id, assessment_id, score, percentage, time_used, date_taken) VALUES (?, ?, ?, ?, ?, NOW())");
$score_for_store = $correct;
$ins->bind_param("iiiii", $student_id, $assessment_id, $score_for_store, $percentage, $elapsed);
$ins->execute();

// pastikan pb_results wujud
$chk = $conn->prepare("SELECT pb_id FROM pb_results WHERE student_id = ?");
$chk->bind_param("i", $student_id);
$chk->execute();
$gr = $chk->get_result();

if ($gr->num_rows === 0) {
    $create = $conn->prepare("INSERT INTO pb_results (student_id, quiz1, quiz2, test, practical1, practical2, total_pb, category, feedback) VALUES (?,0,0,0,0,0,0,'','')");
    $create->bind_param("i", $student_id);
    $create->execute();
}

// map assessment -> field
$field_map = [
  'Quiz 1' => 'quiz1',
  'Quiz 2' => 'quiz2',
  'Test' => 'test',
  'Practical Work 1' => 'practical1',
  'Practical Work 2' => 'practical2'
];
$field = isset($field_map[$type]) ? $field_map[$type] : null;

if ($field) {
    $allowed = ['quiz1','quiz2','test','practical1','practical2'];
    if (in_array($field, $allowed, true)) {
        $sql = "UPDATE pb_results SET {$field} = ? WHERE student_id = ?";
        $upd = $conn->prepare($sql);
        $upd->bind_param("di", $percentage, $student_id);
        $upd->execute();
    }
}

/* ======= Kira PB berdasarkan weight (tanpa mini project) ======= */
// ambil semula semua komponen pelajar ni
$get = $conn->prepare("SELECT quiz1, quiz2, test, practical1, practical2 FROM pb_results WHERE student_id = ?");
$get->bind_param("i", $student_id);
$get->execute();
$data = $get->get_result()->fetch_assoc();

$q1 = $data['quiz1'] ?? 0;
$q2 = $data['quiz2'] ?? 0;
$test = $data['test'] ?? 0;
$pw1 = $data['practical1'] ?? 0;
$pw2 = $data['practical2'] ?? 0;

// Pengiraan PB ikut weight (tanpa mini project)
$quiz_avg = (($q1 + $q2) / 2); // average of quiz1 & quiz2
$quiz_score = ($quiz_avg / 100) * 15;
$test_score = ($test / 100) * 23;
$pw1_score = ($pw1 / 100) * 16;
$pw2_score = ($pw2 / 100) * 16;

$total_pb = round($quiz_score + $test_score + $pw1_score + $pw2_score, 2); // total 70 max

/* ======= Tentukan kategori berdasarkan PB ======= */
if ($total_pb < 40) {
    $category = 'Below Average';
    $feedback = "Your PB mark is {$total_pb}%. You need to improve your understanding and consistency. Keep practicing!";
} elseif ($total_pb < 60) {
    $category = 'Average';
    $feedback = "Your PB mark is {$total_pb}%. Keep it going — consistent effort will bring improvement.";
} elseif ($total_pb < 70) {
    $category = 'Above Average';
    $feedback = "Your PB mark is {$total_pb}%. Good effort! You're performing above the average.";
} elseif ($total_pb < 80) {
    $category = 'Good';
    $feedback = "Your PB mark is {$total_pb}%. Great job! You are showing solid understanding and effort.";
} else {
    $category = 'Excellent';
    $feedback = "Your PB mark is {$total_pb}%. Outstanding performance — keep up the excellent work!";
}

/* ======= Update table pb_results ======= */
$updateAgg = $conn->prepare("
    UPDATE pb_results 
    SET total_pb = ?, category = ?, feedback = ?
    WHERE student_id = ?
");
$updateAgg->bind_param("dssi", $total_pb, $category, $feedback, $student_id);
$updateAgg->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Result — <?= htmlspecialchars($type ?: 'Assessment') ?></title>
<style>
body{font-family:Segoe UI, sans-serif;background:#eef7ff;padding:30px}
.container{max-width:900px;margin:0 auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,0.06)}
h1{color:#0b71d0;text-align:center}
.summary{display:flex;justify-content:space-around;align-items:center;margin:18px 0;padding:12px;background:#f8fcff;border-radius:8px;border:1px solid #e6f3ff}
.summary .item{font-size:16px}
.q{border-radius:8px;padding:14px;margin-bottom:12px;border:1px solid #eef6ff;background:#fbfdff}
.opt{padding:10px;border-radius:6px;margin:6px 0;border:1px solid #ddd;display:block}
.opt.correct{background:#e9f7ef;border-color:#99d8a8}
.opt.wrong{background:#fff1f0;border-color:#f29b9b}
.meta{font-size:13px;color:#666;margin-top:6px}
.small{font-size:13px;color:#666}
.btn{display:inline-block;padding:10px 16px;background:#0b71d0;color:#fff;border-radius:8px;text-decoration:none;margin-top:16px}
.btn:hover{opacity:0.95}
.badge{display:inline-block;padding:6px 10px;border-radius:999px;background:#f1f4f8;color:#333;font-weight:700}
</style>
</head>
<body>
<div class="container">
  <h1><?= htmlspecialchars($assessment_id ? $type : 'Assessment Result') ?></h1>

  <div class="summary">
    <div class="item">Correct: <span class="badge"><?= $correct ?>/<?= $total_q ?></span></div>
    <div class="item">Percentage: <span class="badge"><?= $percentage ?>%</span></div>
    <div class="item">PB (Weighted): <span class="badge"><?= $total_pb ?>%</span></div>
    <div class="item">Category: <span class="badge"><?= $category ?></span></div>
  </div>

  <p style="text-align:center;color:#333;font-size:16px;margin-top:10px;">
    <?= htmlspecialchars($feedback) ?> (without Mini Project)
  </p>

  <?php foreach ($questions as $idx => $q): ?>
    <div class="q">
      <div style="font-weight:700;margin-bottom:8px">Q<?= $idx+1 ?>. <?= htmlspecialchars($q['question_text']) ?></div>

      <?php
        $opts = ['A'=>'option_a','B'=>'option_b','C'=>'option_c','D'=>'option_d'];
        foreach ($opts as $letter => $col) {
            $text = htmlspecialchars($q[$col]);
            $isUser = ($q['user_answer'] !== null && $q['user_answer'] === $letter);
            $isCorrect = ($q['correct_answer'] === $letter);
            $class = $isCorrect ? 'opt correct' : ($isUser && !$isCorrect ? 'opt wrong' : 'opt');
            echo "<div class='$class'><strong>$letter.</strong> $text" . ($isCorrect ? " <span style='color:green;font-weight:700'>(Correct)</span>" : "") . ($isUser && !$isCorrect ? " <span style='color:red;font-weight:700'>(Your answer)</span>" : "") . "</div>";
        }
      ?>

      <div class="meta small">
        <?php if ($q['user_answer'] === null): ?>
          You did not answer this question.
        <?php elseif ($q['is_correct']): ?>
          <span style="color:green;font-weight:700">You answered correctly.</span>
        <?php else: ?>
          <span style="color:red;font-weight:700">Your answer was incorrect.</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>

  <div style="text-align:center">
    <a class="btn" href="challenge-yourself.php">⬅ Back to Challenges</a>
  </div>
</div>
</body>
</html>
