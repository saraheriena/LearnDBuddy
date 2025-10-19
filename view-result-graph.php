<?php
include "db.php";
session_start();

$lecturer_id = $_SESSION['lecturer_id'];
$class_id = $_GET['class_id'] ?? 'all';
$quiz_id = $_GET['quiz_id'] ?? 'all';

// ==========================
// CASE 1: Semua kuiz dipilih (average per topic)
// ==========================
if ($quiz_id == 'all') {
    $query = "
        SELECT 
            c.class_name, 
            q.title AS topic, 
            AVG(r.percentage) AS avg_score
        FROM results r
        JOIN students s ON r.student_id = s.student_id
        JOIN classes c ON s.class_id = c.class_id
        JOIN quizzes q ON r.quiz_id = q.quiz_id
        WHERE c.lecturer_id = $lecturer_id
    ";

    if ($class_id != 'all') {
        $query .= " AND c.class_id = " . intval($class_id);
    }

    $query .= "
        GROUP BY c.class_name, q.title
        ORDER BY c.class_name, q.quiz_id
    ";

    $res = $conn->query($query);
    $data = [];
    while ($row = $res->fetch_assoc()) {
        $data[$row['class_name']][$row['topic']] = round($row['avg_score'], 2);
    }

    // dapatkan semua topic sebagai label
    $labels = [];
    foreach ($data as $cls => $topics) {
        foreach (array_keys($topics) as $t) {
            if (!in_array($t, $labels)) $labels[] = $t;
        }
    }

} 
// ==========================
// CASE 2: Satu kuiz (plot semua markah student ikut kelas)
// ==========================
else {
    $query = "
        SELECT 
            c.class_name, 
            q.title AS topic, 
            r.percentage
        FROM results r
        JOIN students s ON r.student_id = s.student_id
        JOIN classes c ON s.class_id = c.class_id
        JOIN quizzes q ON r.quiz_id = q.quiz_id
        WHERE c.lecturer_id = $lecturer_id
          AND r.quiz_id = " . intval($quiz_id);

    if ($class_id != 'all') {
        $query .= " AND c.class_id = " . intval($class_id);
    }

    $query .= " ORDER BY c.class_name, q.quiz_id";
    $res = $conn->query($query);

    $data = [];
    while ($row = $res->fetch_assoc()) {
        $topic = $row['topic'];
        $cls = $row['class_name'];
        $mark = round($row['percentage'], 2);
        $data[$cls][$topic][] = $mark;
    }

    // bila satu quiz — kita tunjuk average juga
    $labels = [];
    foreach ($data as $cls => $topics) {
        foreach (array_keys($topics) as $t) {
            if (!in_array($t, $labels)) $labels[] = $t;
        }
    }

    // ubah jadi purata
    foreach ($data as $cls => $topics) {
        foreach ($topics as $topic => $marks) {
            $data[$cls][$topic] = round(array_sum($marks) / count($marks), 2);
        }
    }
}

// ==========================
// Dataset untuk Chart.js
// ==========================
$colors = ['#1e90ff', '#ff7f50', '#3cb371', '#ffb347', '#9370db', '#ff69b4'];
$i = 0;
$datasets = [];

foreach ($data as $class => $topics) {
    $marks = [];
    foreach ($labels as $topic) {
        $marks[] = isset($topics[$topic]) ? $topics[$topic] : 0;
    }

    $datasets[] = [
        'label' => $class,
        'data' => $marks,
        'borderColor' => $colors[$i % count($colors)],
        'backgroundColor' => $colors[$i % count($colors)],
        'fill' => false,
        'tension' => 0.3,
        'pointRadius' => 5
    ];
    $i++;
}

header('Content-Type: application/json');
echo json_encode([
    'labels' => array_values($labels),
    'datasets' => $datasets
]);
?>
