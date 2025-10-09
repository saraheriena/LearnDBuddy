<?php
session_start();
include "db.php";
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT * FROM students WHERE student_id=$student_id")->fetch_assoc();
$class_id = $student['class_id'];

$quizzes = $conn->query("SELECT * FROM quizzes WHERE class_id=$class_id OR class_id=0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Available Quizzes</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>Available Quizzes</h1>
  <a href="dashboard.php" class="logout">Back</a>
</header>

<main class="form-container">
  <table>
    <thead>
      <tr><th>Quiz Title</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php while($q = $quizzes->fetch_assoc()) { ?>
        <tr>
          <td><?= htmlspecialchars($q['title']); ?></td>
          <td><a href="take_quiz.php?quiz_id=<?= $q['quiz_id']; ?>">Take Quiz</a></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</main>
</body>
</html>
