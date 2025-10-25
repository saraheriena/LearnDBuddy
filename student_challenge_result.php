<?php
session_start();
include 'db.php';
if (!isset($_SESSION['student_id'])) {
  header("Location: student_login.php");
  exit;
}

$student_id = (int)$_SESSION['student_id'];

/* --- Ambil maklumat pelajar --- */
$info = $conn->prepare("
  SELECT s.fullname, s.matric_no, c.class_name, r.*
  FROM students s
  LEFT JOIN classes c ON s.class_id = c.class_id
  LEFT JOIN pb_results r ON s.student_id = r.student_id
  WHERE s.student_id = ?
");
$info->bind_param("i",$student_id);
$info->execute();
$data = $info->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Challenge Results</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{background:#f4f9ff;font-family:'Segoe UI',sans-serif;margin:0}
header{background:linear-gradient(90deg,#1e90ff,#4facfe);color:white;padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
header h1{margin:0;font-size:22px}
.logout{background:#ff4d4d;color:#fff;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:600}
.page{max-width:900px;margin:auto;padding:30px}
.card{background:#fff;border-radius:14px;box-shadow:0 6px 16px rgba(0,0,0,.06);padding:25px;margin-bottom:24px}
.card-title{font-size:18px;font-weight:bold;color:#1e90ff;margin-bottom:12px;display:flex;align-items:center;gap:10px}
table{width:100%;border-collapse:collapse;font-size:15px;background:#fff;border-radius:12px;overflow:hidden}
thead th{background-color:#1e90ff;color:white;padding:10px}
td,th{padding:10px;border:1px solid #eef1f6;text-align:center}
.feedback-box{background:#e8f4ff;padding:15px;border-radius:10px;margin-top:15px;font-weight:500;}
.chart-box{height:350px;display:flex;justify-content:center;align-items:center;}
</style>
</head>
<body>
<header>
  <h1>My Challenge Results</h1>
  <a href="challenge_choose.php" class="logout">Back</a>
</header>

<div class="page">

  <div class="card">
    <h3 class="card-title">👤 Student Info</h3>
    <p><b>Name:</b> <?= htmlspecialchars($data['fullname'] ?? '-') ?><br>
       <b>Matric No:</b> <?= htmlspecialchars($data['matric_no'] ?? '-') ?><br>
       <b>Class:</b> <?= htmlspecialchars($data['class_name'] ?? '-') ?></p>
  </div>

  <div class="card">
    <h3 class="card-title">📋 Assessment Scores</h3>
    <table>
      <thead>
        <tr>
          <th>Assessment</th>
          <th>Score (%)</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>Quiz 1</td><td><?= $data['quiz1'] ?? 0 ?></td></tr>
        <tr><td>Quiz 2</td><td><?= $data['quiz2'] ?? 0 ?></td></tr>
        <tr><td>Test</td><td><?= $data['test'] ?? 0 ?></td></tr>
        <tr><td>Practical Work 1</td><td><?= $data['practical1'] ?? 0 ?></td></tr>
        <tr><td>Practical Work 2</td><td><?= $data['practical2'] ?? 0 ?></td></tr>
      </tbody>
    </table>

    <div class="feedback-box">
      <p><b>Total PB:</b> <?= $data['total_pb'] ?? 0 ?>%</p>
      <p><b>Category:</b> <?= htmlspecialchars($data['category'] ?? '-') ?></p>
      <p><b>Feedback:</b> <?= htmlspecialchars($data['feedback'] ?? '-') ?></p>
    </div>
  </div>

  <div class="card">
    <h3 class="card-title">📈 Visual Performance</h3>
    <div class="chart-box"><canvas id="pbChart"></canvas></div>
  </div>

</div>

<script>
const ctx = document.getElementById('pbChart');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Quiz 1','Quiz 2','Test','Practical 1','Practical 2'],
    datasets: [{
      label: 'Score (%)',
      data: [
        <?= $data['quiz1'] ?? 0 ?>,
        <?= $data['quiz2'] ?? 0 ?>,
        <?= $data['test'] ?? 0 ?>,
        <?= $data['practical1'] ?? 0 ?>,
        <?= $data['practical2'] ?? 0 ?>
      ],
      backgroundColor: '#1e90ff'
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: { beginAtZero: true, max: 100 }
    }
  }
});
</script>
</body>
</html>
