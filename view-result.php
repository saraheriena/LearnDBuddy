<?php
session_start();
include "db.php";
if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

$query = "SELECT s.fullname, c.class_name, 
    MAX(CASE WHEN q.title LIKE '%1%' THEN r.score END) AS 'Topic 1',
    MAX(CASE WHEN q.title LIKE '%2%' THEN r.score END) AS 'Topic 2',
    MAX(CASE WHEN q.title LIKE '%3%' THEN r.score END) AS 'Topic 3',
    MAX(CASE WHEN q.title LIKE '%4%' THEN r.score END) AS 'Topic 4',
    MAX(CASE WHEN q.title LIKE '%5%' THEN r.score END) AS 'Topic 5'
FROM results r
JOIN students s ON r.student_id = s.student_id
JOIN quizzes q ON r.quiz_id = q.quiz_id
JOIN classes c ON s.class_id = c.class_id
WHERE c.lecturer_id=$lecturer_id
GROUP BY s.student_id, c.class_name";
$results = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Results - LearnDBuddy</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>Quiz Results</h1>
  <a href="dashboard.php" class="logout">Back</a>
</header>

<main class="form-container">
  <table>
    <thead>
      <tr>
        <th>Student Name</th>
        <th>Class</th>
        <th>Topic 1</th>
        <th>Topic 2</th>
        <th>Topic 3</th>
        <th>Topic 4</th>
        <th>Topic 5</th>
      </tr>
    </thead>
    <tbody>
      <?php while($r = $results->fetch_assoc()) { ?>
      <tr>
        <td><?= htmlspecialchars($r['fullname']); ?></td>
        <td><?= htmlspecialchars($r['class_name']); ?></td>
        <td><?= $r['Topic 1'] ?? '-'; ?></td>
        <td><?= $r['Topic 2'] ?? '-'; ?></td>
        <td><?= $r['Topic 3'] ?? '-'; ?></td>
        <td><?= $r['Topic 4'] ?? '-'; ?></td>
        <td><?= $r['Topic 5'] ?? '-'; ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</main>
</body>
</html>
