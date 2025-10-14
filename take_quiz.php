<?php
session_start();
include "db.php";

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$class_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT class_id FROM students WHERE student_id=$student_id"))['class_id'];

// Ambil semua quiz untuk class dia dan juga All Classes
$sql = "SELECT * FROM quizzes WHERE class_id=$class_id OR class_id IS NULL";
$quizzes = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Take Quiz - LearnDBuddy</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>Available Quizzes</h1>
  <a href="student-dashboard.php" class="logout">Back</a>
</header>
<main class="table-container">
<table>
<thead>
  <tr>
    <th>No.</th>
    <th>Title</th>
    <th>Topic</th>
    <th>Action</th>
  </tr>
</thead>
<tbody>
<?php
$no = 1;
if (mysqli_num_rows($quizzes) > 0):
  while($row = mysqli_fetch_assoc($quizzes)): ?>
  <tr>
    <td><?= $no++; ?></td>
    <td><?= htmlspecialchars($row['title']); ?></td>
    <td><?= $row['topic'] ? 'Topic '.$row['topic'] : '-'; ?></td>
    <td><a href="start_quiz.php?quiz_id=<?= $row['quiz_id']; ?>">Start Quiz</a></td>
  </tr>
<?php endwhile; else: ?>
<tr><td colspan="4">No quizzes available.</td></tr>
<?php endif; ?>
</tbody>
</table>
</main>
</body>
</html>
