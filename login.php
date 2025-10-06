<?php
session_start();
$conn = new mysqli("localhost", "root", "", "learndbuddy");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// initialize attempts & lock time
if (!isset($_SESSION['attempts'])) $_SESSION['attempts'] = 0;
if (!isset($_SESSION['lock_time'])) $_SESSION['lock_time'] = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // check block
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

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM lecturers WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['lecturer_id'] = $row['lecturer_id'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['attempts'] = 0;
            $_SESSION['lock_time'] = 0;
            header("Location: dashboard.php");
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
