<?php
include "db.php";
session_start();

$lecturer_id = $_SESSION['lecturer_id'] ?? 0;
$class_id = $_GET['class_id'] ?? 'all';
$quiz_id  = $_GET['quiz_id'] ?? 'all';
$ai       = isset($_GET['ai']) ? intval($_GET['ai']) : 0;

// Query asas
$sql = "
    SELECT s.student_id, s.fullname, s.matric_no, c.class_name, q.title AS quiz_title, r.score
    FROM results r
    JOIN students s ON r.student_id = s.student_id
    JOIN quizzes q ON r.quiz_id = q.quiz_id
    JOIN classes c ON s.class_id = c.class_id
    WHERE c.lecturer_id = $lecturer_id
";

// 🧠 Tapisan AI Mode
if ($ai == 1) {
    $sql .= " AND q.ai_mode = 1"; // hanya tunjuk AI Challenge quiz
} else {
    $sql .= " AND (q.ai_mode = 0 OR q.ai_mode IS NULL)"; // hanya quiz biasa
}

// Tapis ikut class kalau bukan "all"
if ($class_id != 'all') {
    $sql .= " AND c.class_id = " . intval($class_id);
}

// Tapis ikut quiz kalau bukan "all"
if ($quiz_id != 'all') {
    $sql .= " AND q.quiz_id = " . intval($quiz_id);
}

$sql .= " ORDER BY c.class_name, s.fullname";
$result = $conn->query($sql);

// Papar result
if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<thead><tr>
            <th>No.</th>
            <th>Student Name</th>
            <th>Matric No</th>
            <th>Class</th>
            <th>Quiz</th>
            <th>Score</th>
          </tr></thead><tbody>";

    $no = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>$no</td>
                <td>" . htmlspecialchars($row['fullname']) . "</td>
                <td>" . htmlspecialchars($row['matric_no']) . "</td>
                <td>" . htmlspecialchars($row['class_name']) . "</td>
                <td>" . htmlspecialchars($row['quiz_title']) . "</td>
                <td>" . htmlspecialchars($row['score']) . "</td>
              </tr>";
        $no++;
    }

    echo "</tbody></table>";
} else {
    echo "<p style='text-align:center;'>No results found.</p>";
}
?>
