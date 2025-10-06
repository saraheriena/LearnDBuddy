<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $question = $_POST['security_question'];
    $answer = $_POST['security_answer'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO lecturers (fullname, email, password, security_question, security_answer) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $fullname, $email, $hashedPassword, $question, $answer);

    if ($stmt->execute()) {
        echo "<script>alert('Sign up successful! Please login now.'); window.location='index.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
