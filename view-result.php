<?php
session_start();
include "db.php";

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

// Ambil semua kelas & quiz untuk dropdown
$classes = $conn->query("SELECT * FROM classes WHERE lecturer_id=$lecturer_id");
$quizzes = $conn->query("SELECT * FROM quizzes WHERE class_id IS NULL OR class_id IN (SELECT class_id FROM classes WHERE lecturer_id=$lecturer_id)");
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
let chart; // Global chart instance

// --- Fungsi untuk load chart ---
function loadChart() {
  const classId = document.getElementById('classFilter').value;
  const quizId = document.getElementById('quizFilter').value;

  fetch(`view-result-graph.php?class_id=${classId}&quiz_id=${quizId}`)
    .then(res => res.json())
    .then(data => {
      const ctx = document.getElementById('classChart').getContext('2d');
      if (chart) chart.destroy(); // Reset chart sebelum buat baru

      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: data.datasets
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              max: 100,
              title: { display: true, text: 'Average (%)' }
            }
          },
          plugins: {
            title: { display: true, text: 'Average Quiz Score per Topic' },
            legend: { display: true }
          }
        }
      });
    });
}

// --- Fungsi untuk load table (macam sebelum ni) ---
function loadResults() {
  const classId = document.getElementById('classFilter').value;
  const quizId = document.getElementById('quizFilter').value;

  fetch(`view-result-data.php?class_id=${classId}&quiz_id=${quizId}`)
    .then(res => res.text())
    .then(html => {
      document.getElementById('resultTable').innerHTML = html;
    });
}

// --- Event listeners ---
document.getElementById('classFilter').addEventListener('change', () => {
  loadChart();
  loadResults();
});
document.getElementById('quizFilter').addEventListener('change', () => {
  loadChart();
  loadResults();
});

// Load awal
loadChart();
loadResults();
</script>
</body>
</html>
