<?php
session_start();
include "db.php";

// initialize attempts & lock time
if (!isset($_SESSION['attempts_student'])) $_SESSION['attempts_student'] = 0;
if (!isset($_SESSION['lock_student'])) $_SESSION['lock_student'] = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_SESSION['attempts_student'] >= 3) {
        $diff = time() - $_SESSION['lock_student'];
        if ($diff < 60) {
            $remaining = 60 - $diff;
            header("Location: student_login.php?error=⛔ Too many attempts! Try again in {$remaining}s.");
            exit;
        } else {
            $_SESSION['attempts_student'] = 0;
            $_SESSION['lock_student'] = 0;
        }
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM students WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['class_id'] = $row['class_id'];
            $_SESSION['attempts_student'] = 0;
            $_SESSION['lock_student'] = 0;
            header("Location: student_dashboard.php");
            exit;
        } else {
            $_SESSION['attempts_student']++;
            if ($_SESSION['attempts_student'] >= 3) {
                $_SESSION['lock_student'] = time();
                header("Location: student_login.php?error=⛔ Too many attempts! Try again in 60s.");
            } else {
                header("Location: student_login.php?error=⚠️ Invalid password. Attempt {$_SESSION['attempts_student']} of 3.");
            }
            exit;
        }
    } else {
        header("Location: student_login.php?error=⚠️ Email not found!");
        exit;
    }
}
?>
