<?php
session_start();
include "db.php";

if (!isset($_SESSION['student_id'])) {
  header("Location: login.php");
  exit;
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT q.title, r.score, r.total, r.percentage
        FROM results r
        JOIN quizzes q ON r.quiz_id=q.quiz_id
        WHERE r.student_id=$student_id";
$res = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Results - LearnDBuddy</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>My Quiz Results</h1>
  <a href="student-dashboard.php" class="logout">Back</a>
</header>
<main class="table-container">
<table>
<thead><tr><th>Topic</th><th>Score</th><th>Total</th><th>Percentage</th></tr></thead>
<tbody>
<?php if (mysqli_num_rows($res) > 0): ?>
<?php while($r = mysqli_fetch_assoc($res)): ?>
<tr>
<td><?= htmlspecialchars($r['title']); ?></td>
<td><?= $r['score']; ?></td>
<td><?= $r['total']; ?></td>
<td><?= $r['percentage']; ?>%</td>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="4">No results yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</main>
</body>
</html>
