<?php
include "db.php";

// Handle student signup
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $matric_no = trim($_POST['matric_no']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : NULL;
    $security_question = $_POST['security_question'];
    $security_answer = trim($_POST['security_answer']);

    // Cek sama ada email dah digunakan
    $check = $conn->prepare("SELECT * FROM students WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already registered as student!'); window.location='signup.php';</script>";
        exit;
    }

    $query = "INSERT INTO students (fullname, matric_no, email, password, security_question, security_answer, class_id)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $fullname, $matric_no, $email, $password, $security_question, $security_answer, $class_id);

    if ($stmt->execute()) {
        echo "<script>alert('Student account created successfully! Please login now.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Error while creating student account.'); window.location='signup.php';</script>";
    }
}
?>
