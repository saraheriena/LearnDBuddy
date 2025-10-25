<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = (int)$_SESSION['student_id'];

// Dapatkan class student
$s = $conn->prepare("SELECT class_id FROM students WHERE student_id=?");
$s->bind_param("i", $student_id);
$s->execute();
$st = $s->get_result()->fetch_assoc();
$class_id = $st['class_id'] ?? null;

// Ambil semua assessment untuk kelas tu
$sql = "SELECT * FROM assessments WHERE (class_id=? OR class_id IS NULL OR class_id=0) ORDER BY type ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Challenge Yourself - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { font-family: Arial, sans-serif; background: #f5f8ff; }
    header { text-align:center; padding:20px; background:#1e90ff; color:white; }
    .quiz-list { max-width:900px; margin:30px auto; }
    .quiz-item { background:#fff; padding:15px; border-radius:10px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
    .quiz-item h3 { margin:0; }
    button { background:#1e90ff; color:#fff; border:none; padding:10px 14px; border-radius:8px; cursor:pointer; }
    button:hover { background:#0d6efd; }
  </style>
</head>
<body>
  <header>
    <h1>Challenge Yourself</h1>
    <a href="student-dashboard.php" style="color:#fff;text-decoration:none;">← Back</a>
  </header>

  <main class="quiz-list">
    <?php if ($res->num_rows == 0): ?>
      <p style="text-align:center;color:#666;">No challenges available yet.</p>
    <?php else: while($a = $res->fetch_assoc()): ?>
      <?php
        // Default duration (seconds)
        $dur = 300; // default 5 minutes
        switch ($a['type']) {
          case 'Quiz 1': $dur = 180; break;
          case 'Quiz 2': $dur = 180; break;
          case 'Test': $dur = 180; break;
          case 'Practical Work 1': $dur = 300; break;
          case 'Practical Work 2': $dur = 300; break;
        }
      ?>
      <div class="quiz-item">
        <div>
          <h3><?= htmlspecialchars($a['type']) ?></h3>
          <p style="margin:0;color:#666">Timed challenge based on <?= htmlspecialchars($a['type']) ?>.</p>
        </div>
        <div>
          <form method="get" action="start_challenge.php">
            <input type="hidden" name="assessment_id" value="<?= (int)$a['assessment_id'] ?>">
            <input type="hidden" name="duration" value="<?= (int)$dur ?>">
            <button type="submit">Start (<?= gmdate("i:s",$dur) ?>)</button>
          </form>
        </div>
      </div>
    <?php endwhile; endif; ?>
  </main>
</body>
</html>
