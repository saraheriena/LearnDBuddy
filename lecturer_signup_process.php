<?php
include "db.php";

// Handle lecturer signup
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $security_question = $_POST['security_question'];
    $security_answer = trim($_POST['security_answer']);

    // Cek sama ada email dah digunakan
    $check = $conn->prepare("SELECT * FROM lecturers WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already registered as lecturer!'); window.location='signup.php';</script>";
        exit;
    }

    $query = "INSERT INTO lecturers (fullname, email, password, security_question, security_answer)
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $fullname, $email, $password, $security_question, $security_answer);

    if ($stmt->execute()) {
        echo "<script>alert('Lecturer account created successfully! Please login now.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Error while creating lecturer account.'); window.location='signup.php';</script>";
    }
}
?>
