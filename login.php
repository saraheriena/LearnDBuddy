<?php
session_start();
include "db.php";

if (!isset($_SESSION['attempts'])) $_SESSION['attempts'] = 0;
if (!isset($_SESSION['lock_time'])) $_SESSION['lock_time'] = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($_SESSION['attempts'] >= 3) {
        $diff = time() - $_SESSION['lock_time'];
        if ($diff < 60) {
            $remaining = 60 - $diff;
            header("Location: index.php?error=⛔ Too many attempts! Try again in {$remaining}s.");
            exit;
        } else {
            $_SESSION['attempts'] = 0;
            $_SESSION['lock_time'] = 0;
        }
    }

    // Choose table based on role
    if ($role == 'lecturer') {
        $sql = "SELECT * FROM lecturers WHERE email=? LIMIT 1";
    } else {
        $sql = "SELECT * FROM students WHERE email=? LIMIT 1";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $row = $res->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $role;
            $_SESSION['attempts'] = 0;
            $_SESSION['lock_time'] = 0;

            if ($role == 'lecturer') {
                $_SESSION['lecturer_id'] = $row['lecturer_id'];
                header("Location: dashboard.php");
            } else {
                $_SESSION['student_id'] = $row['student_id'];
                header("Location: student_dashboard.php");
            }
            exit;
        } else {
            $_SESSION['attempts']++;
            if ($_SESSION['attempts'] >= 3) {
                $_SESSION['lock_time'] = time();
                header("Location: index.php?error=⛔ Too many attempts! Try again in 60s.");
            } else {
                header("Location: index.php?error=⚠️ Invalid password. Attempt {$_SESSION['attempts']} of 3.");
            }
            exit;
        }
    } else {
        header("Location: index.php?error=⚠️ Email not found!");
        exit;
    }
}
?>
