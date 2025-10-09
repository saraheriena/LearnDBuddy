<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $question = $_POST['security_question'];
    $answer = $_POST['security_answer'];

    if ($role == 'lecturer') {
        $sql = "INSERT INTO lecturers (fullname, email, password, security_question, security_answer)
                VALUES (?, ?, ?, ?, ?)";
    } else {
        $sql = "INSERT INTO students (fullname, email, password, security_question, security_answer)
                VALUES (?, ?, ?, ?, ?)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $fullname, $email, $password, $question, $answer);

    if ($stmt->execute()) {
        echo "<script>window.location='index.php?error=✅ Account created successfully! Please login.';</script>";
    } else {
        echo "<script>window.location='index.php?error=❌ Registration failed!';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
