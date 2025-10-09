<?php
session_start();
include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($role == "lecturer") {
        // ===== Lecturer Login =====
        $stmt = $conn->prepare("SELECT * FROM lecturers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['lecturer_id'] = $row['lecturer_id'];
                $_SESSION['lecturer_name'] = $row['name'];
                header("Location: lecturer-dashboard.php");
                exit;
            } else {
                header("Location: index.php?error=Invalid password");
                exit;
            }
        } else {
            header("Location: index.php?error=Lecturer not found");
            exit;
        }

    } elseif ($role == "student") {
        // ===== Student Login =====
        $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $_SESSION['student_id'] = $row['student_id'];
                $_SESSION['student_name'] = $row['name'];
                $_SESSION['class_id'] = $row['class_id']; // ✅ penting untuk view_notes.php

                header("Location: student-dashboard.php");
                exit;
            } else {
                header("Location: index.php?error=Invalid password");
                exit;
            }
        } else {
            header("Location: index.php?error=Student not found");
            exit;
        }

    } else {
        header("Location: index.php?error=Please select a role");
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
?>
