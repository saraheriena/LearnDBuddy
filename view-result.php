<?php
session_start();
include "db.php";
if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

/* --- Ambil semua kelas & quiz untuk dropdown --- */
$classes = $conn->query("SELECT * FROM classes WHERE lecturer_id=$lecturer_id");
$quizzes = $conn->query("SELECT * FROM quizzes WHERE class_id IS NULL OR class_id IN (SELECT class_id FROM classes WHERE lecturer_id=$lecturer_id)");

/* --- Purata markah per kelas (untuk graf line) --- */
$class_avg = $conn->query("
    SELECT c.class_name, q.title, AVG(r.percentage) AS avg_score
    FROM results r
    JOIN students s ON r.student_id = s.student_id
    JOIN classes c ON s.class_id = c.class_id
    JOIN quizzes q ON r.quiz_id = q.quiz_id
    WHERE c.lecturer_id=$lecturer_id
    GROUP BY c.class_id, q.quiz_id
    ORDER BY c.class_id, q.quiz_id
");

$data = [];
while ($row = $class_avg->fetch_assoc()) {
    $class = $row['class_name'];
    $topic = $row['title'];
    $data[$class][$topic] = round($row['avg_score'], 2);
}

$topics = ["Topic 1","Topic 2","Topic 3","Topic 4","Topic 5"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Results - LearnDBuddy</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body{font-family:Segoe UI, sans-serif;background:#f4f9ff;margin:0}
  header{background:linear-gradient(90deg,#1e90ff,#4facfe);color:#fff;padding:16px 24px;
         display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 8px rgba(0,0,0,.1)}
  header h1{margin:0;font-size:22px}
  .logout{background:#ff4d4d;color:#fff;padding:8px 14px;border-radius:6px;text-decoration:none;font-weight:600}
  .form-container{max-width:1000px;margin:40px auto;background:#fff;padding:20px 30px;
                  border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,.08)}
  #classChart{width:100%;height:420px}
  .filter-row {
    margin: 20px 0;
    display: flex;
    gap: 20px;
    align-items: center;
  }
  table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
  }
  table th, table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: center;
  }
  table th {
    background-color: #f4f4f4;
  }
</style>
</head>
<body>
<header>
  <h1>Class Performance (Line Chart)</h1>
  <a href="dashboard.php" class="logout">Back</a>
</header>

<main class="form-container">
  <!-- LINE CHART -->
  <canvas id="classChart"></canvas>

  <!-- DROPDOWN FILTER -->
  <div class="filter-row">
    <label>Class:</label>
    <select id="classFilter">
      <option value="all">All Classes</option>
      <?php while($c = $classes->fetch_assoc()) { ?>
        <option value="<?= $c['class_id']; ?>"><?= htmlspecialchars($c['class_name']); ?></option>
      <?php } ?>
    </select>

    <label>Quiz:</label>
    <select id="quizFilter">
      <option value="all">All Quizzes</option>
      <?php while($q = $quizzes->fetch_assoc()) { ?>
        <option value="<?= $q['quiz_id']; ?>"><?= htmlspecialchars($q['title']); ?></option>
      <?php } ?>
    </select>
  </div>

  <!-- TABLE RESULT -->
  <div id="resultTable"></div>
</main>

<script>
const topics = <?= json_encode($topics) ?>;
const rawData = <?= json_encode($data) ?>;
const colors = ['#1e90ff','#ff7f50','#3cb371','#ffb347','#9370db','#ff69b4'];

// --- Line Chart ---
const datasets = Object.keys(rawData).map((cls,i)=>({
  label: cls,
  data: topics.map(t=>rawData[cls][t]||0),
  borderColor: colors[i%colors.length],
  backgroundColor: colors[i%colors.length],
  fill:false,
  tension:.3,
  pointRadius:5
}));

new Chart(document.getElementById('classChart'),{
  type:'line',
  data:{labels:topics,datasets},
  options:{
    responsive:true,
    scales:{y:{beginAtZero:true,max:100,title:{display:true,text:'Average %'}}},
    plugins:{title:{display:true,text:'Average Quiz Score per Topic'}}
  }
});

// --- Table Result (AJAX) ---
function loadResults() {
    const classId = document.getElementById('classFilter').value;
    const quizId = document.getElementById('quizFilter').value;

    fetch(`view-result-data.php?class_id=${classId}&quiz_id=${quizId}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('resultTable').innerHTML = html;
        });
}

document.getElementById('classFilter').addEventListener('change', loadResults);
document.getElementById('quizFilter').addEventListener('change', loadResults);

// Load table on page load
loadResults();
</script>
</body>
</html>
