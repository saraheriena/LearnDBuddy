<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
  header("Location: student_login.php");
  exit;
}

$student_id = $_SESSION['student_id'];
$quiz_id = $_POST['quiz_id'];
$answers = $_POST['answer'] ?? [];

$score = 0;
$total = count($answers);

// Semak jawapan betul
foreach ($answers as $qid => $ans) {
  $query = "SELECT correct_answer FROM questions WHERE question_id = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $qid);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();

  if ($result && $result['correct_answer'] === $ans) {
    $score++;
  }
}

// Kira percentage
$percentage = ($total > 0) ? ($score / $total) * 100 : 0;

// Simpan ke table results
$stmt = $conn->prepare("INSERT INTO results (student_id, quiz_id, score, total, percentage) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iiiid", $student_id, $quiz_id, $score, $total, $percentage);
$stmt->execute();

header("Location: dashboard_student.php?msg=✅ Quiz submitted! Your score: $score/$total ($percentage%)");
exit;
?>
