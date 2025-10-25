<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1) cuba cari di lecturers dahulu
    $stmt = $conn->prepare("SELECT lecturer_id, fullname, password FROM lecturers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['lecturer_id'] = $row['lecturer_id'];
            $_SESSION['lecturer_name'] = $row['fullname'];
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = 'lecturer';

            // ✅ ubah sini → masuk ke intro.php dulu
            header("Location: intro.php");
            exit;
        } else {
            header("Location: index.php?error=Invalid password");
            exit;
        }
    }

    // 2) jika bukan lecturer, cuba students
    $stmt = $conn->prepare("SELECT student_id, fullname, password, class_id FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['student_name'] = $row['fullname'];
            $_SESSION['user_email'] = $email;
            $_SESSION['role'] = 'student';
            if (isset($row['class_id'])) {
                $_SESSION['class_id'] = $row['class_id'];
            }

            // ✅ ubah sini → masuk ke intro.php dulu
            header("Location: intro.php");
            exit;
        } else {
            header("Location: index.php?error=Invalid password");
            exit;
        }
    }

    // 3) tak jumpa dalam kedua-dua jadual
    header("Location: index.php?error=Account not found");
    exit;

} else {
    header("Location: index.php");
    exit;
}
?>
