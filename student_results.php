<?php
session_start();
include "db.php";
if (!isset($_SESSION['student_id'])) {
  header("Location: login.php");
  exit;
}

$student_id = (int)$_SESSION['student_id'];

/* --- Dapatkan maklumat pelajar lengkap --- */
$info = $conn->prepare("
  SELECT s.fullname, s.matric_no, c.class_name
  FROM students s
  LEFT JOIN classes c ON s.class_id = c.class_id
  WHERE s.student_id = ?
");
$info->bind_param("i",$student_id);
$info->execute();
$stinfo = $info->get_result()->fetch_assoc();
$student_name = $stinfo['fullname'] ?? 'Student';
$matric = $stinfo['matric_no'] ?? '-';
$class_name = $stinfo['class_name'] ?? '-';

/* --- Result untuk graf & jadual --- */
$sql = "
  SELECT q.title, r.score, r.total,
         COALESCE(r.percentage, ROUND((r.score/r.total)*100,2)) AS pct
  FROM results r
  JOIN quizzes q ON r.quiz_id = q.quiz_id
  WHERE r.student_id=?
  ORDER BY q.quiz_id ASC
";
$stmt=$conn->prepare($sql);
$stmt->bind_param("i",$student_id);
$stmt->execute();
$res=$stmt->get_result();

$labels=[];$percent=[];
while($r=$res->fetch_assoc()){
  $labels[]=$r['title'];
  $percent[]=(float)$r['pct'];
  $rows[]=$r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Quiz Results</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body{background:#f4f9ff;font-family:'Segoe UI',sans-serif;margin:0}
header{background:linear-gradient(90deg,#1e90ff,#4facfe);color:white;
       padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
header h1{margin:0;font-size:22px}
.logout{background:#ff4d4d;color:#fff;padding:8px 14px;border-radius:6px;
        text-decoration:none;font-weight:600}
.page{max-width:1100px;margin:auto;padding:24px}
.card{background:#fff;border-radius:14px;box-shadow:0 6px 16px rgba(0,0,0,.06);padding:20px;margin-bottom:24px}
.card-title{font-size:18px;font-weight:bold;color:#1e90ff;margin-bottom:12px;display:flex;align-items:center;gap:10px}
table{width:100%;border-collapse:collapse;font-size:15px;background:#fff;border-radius:12px;overflow:hidden}
thead th{background-color:#1e90ff;color:white;padding:10px}
td,th{padding:10px;border:1px solid #eef1f6;text-align:center}
td:first-child,th:first-child{text-align:left}
.chart-box{height:360px}
</style>
</head>
<body>
<header>
  <h1>My Quiz Results</h1>
  <a href="student-dashboard.php" class="logout">Back</a>
</header>

<div class="page">

  <!-- 🟢 Line Graph -->
  <div class="card">
    <h3 class="card-title"><i class="fas fa-chart-line"></i> My Quiz Performance</h3>
    <?php if(!empty($labels)): ?>
      <div class="chart-box"><canvas id="quizLineChart"></canvas></div>
    <?php else: ?>
      <p style="color:#666;">No results yet.</p>
    <?php endif; ?>
  </div>

  <!-- 🟦 Student Info + Table -->
  <div class="card">
    <h3 class="card-title"><i class="fas fa-user"></i> Student Info</h3>
    <ul style="padding-left:18px;line-height:1.7;">
      <li><strong>Name:</strong> <?= htmlspecialchars($student_name) ?></li>
      <li><strong>Matric No:</strong> <?= htmlspecialchars($matric) ?></li>
      <li><strong>Class:</strong> <?= htmlspecialchars($class_name) ?></li>
    </ul>
    <hr style="margin:20px 0;">
    <h3 class="card-title"><i class="fas fa-table"></i> Quiz Results</h3>
    <table>
      <thead>
        <tr><th>Quiz Title</th><th>Score</th><th>Total</th><th>Percentage</th></tr>
      </thead>
      <tbody>
      <?php if(!empty($rows)): foreach($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['title']) ?></td>
          <td><?= htmlspecialchars($r['score']) ?></td>
          <td><?= htmlspecialchars($r['total']) ?></td>
          <td><?= number_format($r['pct'],2) ?>%</td>
        </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="4">No results yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if(!empty($labels)): ?>
<script>
new Chart(document.getElementById('quizLineChart'),{
  type:'line',
  data:{
    labels:<?=json_encode($labels)?>,
    datasets:[{
      label:'My Score (%)',
      data:<?=json_encode($percent)?>,
      borderColor:'#1e90ff',
      backgroundColor:'#1e90ff',
      fill:false,
      tension:.3,
      pointRadius:5
    }]
  },
  options:{
    responsive:true,
    scales:{y:{beginAtZero:true,max:100,title:{display:true,text:'%'}}},
    plugins:{title:{display:true,text:'Performance Across Topics'}}
  }
});
</script>
<?php endif; ?>
</body>
</html>
