<?php
include "db.php"; // sambungan database

header("Content-Type: application/json");

// dapatkan student_id dari Android (POST/GET)
$student_id = $_GET['student_id'] ?? 0;

if ($student_id > 0) {
    // ambil maklumat student + result
    $sql = "SELECT s.student_id, s.fullname, s.email,
                   r.result_id, r.score, r.total, r.percentage,
                   q.title AS quiz_title
            FROM students s
            LEFT JOIN results r ON s.student_id = r.id_student
            LEFT JOIN quizzes q ON r.id_quiz = q.quiz_id
            WHERE s.student_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    if (!empty($data)) {
        echo json_encode(["status" => "success", "data" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "No data found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid student_id"]);
}
?>
