<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
  header("Location: index.php");
  exit;
}

$class_id = $_SESSION['class_id'];
$notes = $conn->query("SELECT * FROM notes WHERE class_id = '$class_id' ORDER BY note_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Notes - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
  <div class="header-left">
    <img src="logo.png" alt="LearnDBuddy Logo" class="logo" style="height:50px;">
    <h1>Class Notes</h1>
  </div>
  <a href="student-dashboard.php" class="logout">Back</a>
</header>

<main class="dashboard">
  <?php if ($notes->num_rows > 0): ?>
    <?php while ($row = $notes->fetch_assoc()): ?>
      <div class="card">
        <i class="fas fa-file-alt"></i>
        <h2><?= htmlspecialchars($row['title']); ?></h2>

        <?php if (preg_match('/\.pdf$/i', $row['file_path'])): ?>
          <embed src="<?= $row['file_path']; ?>" type="application/pdf" width="100%" height="300px">
        <?php else: ?>
          <video controls width="100%">
            <source src="<?= $row['file_path']; ?>">
          </video>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No notes uploaded yet for your class.</p>
  <?php endif; ?>
</main>
</body>
</html>
