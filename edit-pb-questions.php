<?php
session_start();
include 'db.php';
if (!isset($_SESSION['student_id'])) { header("Location: student_login.php"); exit; }

$student_id = (int)$_SESSION['student_id'];

// Dapatkan class student
$s = $conn->prepare("SELECT class_id FROM students WHERE student_id=?");
$s->bind_param("i",$student_id); $s->execute();
$st = $s->get_result()->fetch_assoc();
$class_id = $st['class_id'] ?? null;

// Ambil semua PB assessment dari lecturer (class-specific / global)
$sql = "SELECT * FROM assessments WHERE (class_id IS NULL OR class_id=? OR class_id=0) ORDER BY assessment_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Challenge Yourself - LearnDBuddy</title>
<link rel="stylesheet" href="style.css">
<style>
.container{max-width:900px;margin:24px auto}
.card{background:#fff;padding:14px;border-radius:10px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
button{background:#1e90ff;color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer}
</style>
</head>
<body>
<header>
  <h2 style="text-align:center;">Available PB Challenges</h2>
  <p style="text-align:center;"><a href="student-dashboard.php">← Back to Dashboard</a></p>
</header>

<div class="container">
<?php if ($res->num_rows == 0): ?>
  <p style="text-align:center;color:#666;">No assessments available yet.</p>
<?php else: while($a = $res->fetch_assoc()): ?>
  <?php
  // Tetapkan masa ikut jenis
  $dur = 300;
  if (stripos($a['type'],'Quiz 1')!==false) $dur=300;
  elseif (stripos($a['type'],'Quiz 2')!==false) $dur=600;
  elseif (stripos($a['type'],'Test')!==false) $dur=900;
  elseif (stripos($a['type'],'Practical Work')!==false) $dur=1200;
  ?>
  <div class="card">
    <div>
      <h3><?= htmlspecialchars($a['title']) ?> (<?= $a['type'] ?>)</h3>
      <p style="margin:0;color:#666;">Duration: <?= gmdate("i:s",$dur) ?></p>
    </div>
    <form method="get" action="start_challenge.php">
      <input type="hidden" name="assessment_id" value="<?= $a['assessment_id'] ?>">
      <input type="hidden" name="duration" value="<?= $dur ?>">
      <button type="submit">Start</button>
    </form>
  </div>
<?php endwhile; endif; ?>
</div>
</body>
</html>
