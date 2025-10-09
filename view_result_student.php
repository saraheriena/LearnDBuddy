<?php
session_start();
include "db.php";
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student = $conn->query("SELECT fullname FROM students WHERE student_id=$student_id")->fetch_assoc();

$query = "SELECT q.title, r.score 
          FROM results r 
          JOIN quizzes q ON r.quiz_id = q.quiz_id 
          WHERE r.student_id=$student_id
          ORDER BY q.quiz_id ASC";
$results = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Quiz Results</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>My Quiz Results</h1>
  <a href="dashboard_student.php" class="logout">Back</a>
</header>

<main class="form-container">
  <table>
    <thead>
      <tr><th>Quiz Title</th><th>Score</th></tr>
    </thead>
    <tbody>
      <?php if ($results->num_rows > 0): ?>
        <?php while($r = $results->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($r['title']); ?></td>
            <td><?= $r['score'] ?? '-'; ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="2">No results yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</main>
</body>
</html>
