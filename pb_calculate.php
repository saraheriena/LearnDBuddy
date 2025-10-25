<?php
session_start();
include "db.php";

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

/* --- Validate input --- */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);

    // Ambil semua assessment jenis PB (tanpa Mini Project)
    $assessments = $conn->query("SELECT * FROM assessments 
                                 WHERE type IN ('Quiz 1','Quiz 2','Test','Practical Work 1','Practical Work 2')
                                 ORDER BY assessment_id");

    $total_score = 0;
    $total_weight = 0;

    while ($a = $assessments->fetch_assoc()) {
        $assessment_id = $a['assessment_id'];
        $weight = $a['weightage'];

        // Ambil markah pelajar bagi assessment ini
        $res = $conn->query("SELECT mark_obtained, total_marks FROM pb_results 
                             WHERE student_id=$student_id AND assessment_id=$assessment_id");

        if ($res && $res->num_rows > 0) {
            $r = $res->fetch_assoc();
            $mark = floatval($r['mark_obtained']);
            $total = floatval($r['total_marks']);
            if ($total > 0) {
                // Formula markah (%) × berat
                $percent = ($mark / $total) * 100;
                $weighted = ($percent * $weight) / 100;
                $total_score += $weighted;
                $total_weight += $weight;
            }
        }
    }

    // Pastikan total_weight tak kosong
    if ($total_weight > 0) {
        // Jumlah PB dikira 70% daripada keseluruhan CA
        $final_pb = ($total_score / $total_weight) * 70;
    } else {
        $final_pb = 0;
    }

    // Tentukan kategori
    if ($final_pb < 40) {
        $category = "Below Average";
        $feedback = "Your PB marks result is $final_pb. You need to improve.";
    } elseif ($final_pb < 60) {
        $category = "Average";
        $feedback = "Your PB marks result is $final_pb. Keep studying hard.";
    } elseif ($final_pb < 70) {
        $category = "Above Average";
        $feedback = "Your PB marks result is $final_pb. Nice progress!";
    } elseif ($final_pb < 80) {
        $category = "Good";
        $feedback = "Your PB marks result is $final_pb. Great work!";
    } else {
        $category = "Excellent";
        $feedback = "Your PB marks result is $final_pb. Keep it going!";
    }

    // Simpan result dalam table pb_summary
    $conn->query("CREATE TABLE IF NOT EXISTS pb_summary (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        lecturer_id INT,
        total_pb DECIMAL(5,2),
        category VARCHAR(30),
        feedback TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Semak jika sudah ada rekod pelajar
    $exists = $conn->query("SELECT id FROM pb_summary WHERE student_id=$student_id AND lecturer_id=$lecturer_id");
    if ($exists->num_rows > 0) {
        $conn->query("UPDATE pb_summary 
                      SET total_pb=$final_pb, category='$category', feedback='$feedback'
                      WHERE student_id=$student_id AND lecturer_id=$lecturer_id");
    } else {
        $conn->query("INSERT INTO pb_summary (student_id, lecturer_id, total_pb, category, feedback)
                      VALUES ($student_id, $lecturer_id, $final_pb, '$category', '$feedback')");
    }

    header("Location: view-pb-results.php?success=1");
    exit;
} else {
    header("Location: view-pb-results.php?error=1");
    exit;
}
?>
