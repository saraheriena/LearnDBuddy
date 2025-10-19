<?php
session_start();
include "db.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$quiz_id = intval($_GET['quiz_id'] ?? 0);

// Dapatkan class_id student
$student_class = $conn->prepare("SELECT class_id FROM students WHERE student_id=?");
$student_class->bind_param("i", $student_id);
$student_class->execute();
$class_id = $student_class->get_result()->fetch_assoc()['class_id'] ?? null;
if (!$class_id) $class_id = NULL;

// Ambil semua soalan ikut quiz
$q = $conn->prepare("SELECT * FROM questions WHERE quiz_id=?");
$q->bind_param("i", $quiz_id);
$q->execute();
$res = $q->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $score = 0;
    $total = count($_POST['answers']);

    foreach ($_POST['answers'] as $qid => $ans) {
        $check = $conn->prepare("SELECT correct_answer FROM questions WHERE question_id=? AND quiz_id=?");
        $check->bind_param("ii", $qid, $quiz_id);
        $check->execute();
        $c_res = $check->get_result()->fetch_assoc();

        if (strtoupper($ans) === $c_res['correct_answer']) $score++;

        $stmt = $conn->prepare("INSERT INTO student_answers (student_id, quiz_id, question_id, answer) VALUES (?, ?, ?, ?)");
        $ans_upper = strtoupper($ans);
        $stmt->bind_param("iiis", $student_id, $quiz_id, $qid, $ans_upper);
        $stmt->execute();
    }

    $percentage = ($score / $total) * 100;

    $insert_result = $conn->prepare("
        INSERT INTO results (student_id, quiz_id, class_id, score, total, percentage) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert_result->bind_param("iiiidd", $student_id, $quiz_id, $class_id, $score, $total, $percentage);
    $insert_result->execute();

    echo "<script>
            alert('Quiz submitted! You scored $score / $total (" . round($percentage,2) . "%)');
            window.location='review_quiz.php?quiz_id=$quiz_id';
          </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Start Quiz - LearnDBuddy</title>
</head>
<body style="font-family:'Segoe UI',sans-serif; background:#f5f7fa; margin:0;">
<header style="background:#1e90ff; color:white; padding:15px 25px; display:flex; justify-content:space-between; align-items:center;">
  <h1 style="margin:0; font-size:20px;">Quiz</h1>
  <a href="take_quiz.php" style="color:white; text-decoration:none; background:#005bb5; padding:8px 14px; border-radius:6px; font-weight:600;">Back</a>
</header>
<main style="max-width:800px; margin:40px auto; background:white; padding:30px 40px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1); text-align:left;">
<form method="post">
<?php 
$no = 1;
while ($row = $res->fetch_assoc()): ?>
  <div style="margin-bottom:30px;">
    <p style="font-weight:600; color:#333; margin-bottom:12px; font-size:17px;">
        <?= $no++ ?>. <?= htmlspecialchars($row['question_text']); ?>
    </p>
    <?php foreach (['A','B','C','D'] as $opt): ?>
      <label style="
        display:block;
        position:relative;
        padding:12px 16px;
        margin:8px 0;
        border:1px solid #ddd;
        border-radius:8px;
        background:#fafafa;
        cursor:pointer;
        transition:0.2s;
      " 
      onmouseover="this.style.background='#eef6ff'; this.style.borderColor='#1e90ff';" 
      onmouseout="this.style.background='#fafafa'; this.style.borderColor='#ddd';">
        <input type="radio" 
               name="answers[<?= $row['question_id']; ?>]" 
               value="<?= $opt; ?>" required 
               style="position:absolute; left:12px; top:50%; transform:translateY(-50%); width:18px; height:18px; accent-color:#1e90ff;">
        <span style="margin-left:32px; font-size:15px; font-weight:500; color:#333;">
          <?= "$opt. " . htmlspecialchars($row["option_" . strtolower($opt)]); ?>
        </span>
      </label>
    <?php endforeach; ?>
  </div>
<?php endwhile; ?>
<button type="submit" style="
  background:#1e90ff; color:white; border:none; padding:12px 22px; font-size:16px; border-radius:8px; cursor:pointer; font-weight:600; margin-top:10px;"
  onmouseover="this.style.background='#005bb5';" onmouseout="this.style.background='#1e90ff';">
  Submit Quiz
</button>
</form>
</main>
</body>
</html>