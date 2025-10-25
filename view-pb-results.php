<?php
session_start();
include "db.php";

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

// Ambil senarai kelas untuk dropdown
$class_query = $conn->query("SELECT * FROM classes WHERE lecturer_id = $lecturer_id");

// Semak kelas dipilih
$selected_class = isset($_GET['class_id']) ? $_GET['class_id'] : 'all';

// Query PB results ikut kelas
if ($selected_class == 'all') {
    $sql = "SELECT 
                s.fullname AS student_name, 
                c.class_name, 
                p.quiz1, p.quiz2, p.test, 
                p.practical1, p.practical2, 
                p.total_pb, p.category, p.feedback
            FROM pb_results p
            JOIN students s ON p.student_id = s.student_id
            LEFT JOIN classes c ON s.class_id = c.class_id
            WHERE c.lecturer_id = $lecturer_id
            ORDER BY c.class_name, s.fullname";
} else {
    $sql = "SELECT 
                s.fullname AS student_name, 
                c.class_name, 
                p.quiz1, p.quiz2, p.test, 
                p.practical1, p.practical2, 
                p.total_pb, p.category, p.feedback
            FROM pb_results p
            JOIN students s ON p.student_id = s.student_id
            LEFT JOIN classes c ON s.class_id = c.class_id
            WHERE c.lecturer_id = $lecturer_id AND c.class_id = $selected_class
            ORDER BY s.fullname";
}
$result = $conn->query($sql);

// Kira purata untuk graf
$avg_query = "SELECT 
                AVG(p.quiz1) AS avg_q1, 
                AVG(p.quiz2) AS avg_q2, 
                AVG(p.test) AS avg_test, 
                AVG(p.practical1) AS avg_p1, 
                AVG(p.practical2) AS avg_p2
              FROM pb_results p
              JOIN students s ON p.student_id = s.student_id
              LEFT JOIN classes c ON s.class_id = c.class_id
              WHERE c.lecturer_id = $lecturer_id";
if ($selected_class != 'all') {
    $avg_query .= " AND c.class_id = $selected_class";
}
$avg = $conn->query($avg_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View PB Results - LearnDBuddy</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: Segoe UI, sans-serif; background: #f5faff; margin:0; }
header { background: linear-gradient(90deg,#1e90ff,#4facfe); color:#fff; padding:16px 24px; text-align:center; position:relative; }
.nav { position:absolute; left:20px; top:18px; }
a.btn { background:#1e90ff; color:#fff; padding:8px 14px; text-decoration:none; border-radius:6px; }
a.btn:hover { background:#0f78d1; }

.page-container { max-width:1100px; margin:30px auto; padding:0 20px; }
.card {
    background:#fff;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    padding:20px 25px;
    margin-bottom:25px;
}
.card h2 {
    margin-top:0;
    font-size:20px;
    color:#1e90ff;
    border-bottom:2px solid #e0e0e0;
    padding-bottom:8px;
}
.filter { margin-bottom:15px; text-align:center; }
select { padding:8px; border-radius:6px; border:1px solid #ccc; }

table { width:100%; border-collapse: collapse; margin-top: 10px;}
th, td { padding:10px; border:1px solid #ccc; text-align:center;}
th { background:#1e90ff; color:#fff;}
td.feedback { text-align:left;}
.chart-container { width:90%; margin:20px auto; }
</style>
</head>
<body>
<header>
    <div class="nav">
        <a href="dashboard.php" class="btn">← Back</a>
    </div>
    <h1>PB Results</h1>
</header>

<div class="page-container">

<!-- Filter Card -->
<div class="card">
    <div class="filter">
        <form method="GET" action="">
            <label><strong>Filter by Class:</strong></label>
            <select name="class_id" onchange="this.form.submit()">
                <option value="all" <?= ($selected_class=='all')?'selected':'' ?>>All Classes</option>
                <?php while($c = $class_query->fetch_assoc()): ?>
                    <option value="<?= $c['class_id'] ?>" <?= ($selected_class==$c['class_id'])?'selected':'' ?>>
                        <?= htmlspecialchars($c['class_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>
</div>

<!-- Chart Card -->
<div class="card">
    <h2>Performance Summary</h2>
    <div class="chart-container">
        <canvas id="summaryChart"></canvas>
    </div>
</div>

<!-- Table Card -->
<div class="card">
    <h2>Student PB Results</h2>
    <?php if($result->num_rows > 0): ?>
    <table>
    <thead>
    <tr>
    <th>No</th>
    <th>Student</th>
    <th>Class</th>
    <th>Quiz 1</th>
    <th>Quiz 2</th>
    <th>Test</th>
    <th>Practical 1</th>
    <th>Practical 2</th>
    <th>Total</th>
    <th>Category</th>
    <th>Feedback</th>
    </tr>
    </thead>
    <tbody>
    <?php $i=1; while($row = $result->fetch_assoc()): ?>
    <tr>
    <td><?= $i++ ?></td>
    <td><?= htmlspecialchars($row['student_name']) ?></td>
    <td><?= htmlspecialchars($row['class_name']) ?></td>
    <td><?= $row['quiz1'] ?></td>
    <td><?= $row['quiz2'] ?></td>
    <td><?= $row['test'] ?></td>
    <td><?= $row['practical1'] ?></td>
    <td><?= $row['practical2'] ?></td>
    <td><?= round($row['total_pb'],2) ?></td>
    <td><?= $row['category'] ?></td>
    <td class="feedback"><?= htmlspecialchars($row['feedback']) ?></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
    </table>
    <?php else: ?>
    <p style="text-align:center;">No records found.</p>
    <?php endif; ?>
</div>
</div>

<!-- Chart Script -->
<script>
const ctx = document.getElementById('summaryChart');
const data = {
    labels: ['Quiz 1', 'Quiz 2', 'Test', 'Practical 1', 'Practical 2'],
    datasets: [{
        label: 'Average Marks (%)',
        data: [
            <?= round($avg['avg_q1'] ?? 0, 2) ?>,
            <?= round($avg['avg_q2'] ?? 0, 2) ?>,
            <?= round($avg['avg_test'] ?? 0, 2) ?>,
            <?= round($avg['avg_p1'] ?? 0, 2) ?>,
            <?= round($avg['avg_p2'] ?? 0, 2) ?>
        ],
        backgroundColor: ['#0096ff','#0096ff','#0096ff','#0096ff','#0096ff'],
        borderColor: '#0074d9',
        borderWidth: 1
    }]
};
new Chart(ctx, {
    type: 'bar',
    data: data,
    options: {
        scales: { y: { beginAtZero: true, max: 100 } },
        plugins: { 
            legend: { display: false },
            tooltip: { 
                callbacks: { 
                    label: ctx => `${ctx.parsed.y.toFixed(1)}%`
                }
            } 
        }
    }
});
</script>
</body>
</html>
