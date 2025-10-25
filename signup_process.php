<?php
include 'db.php';

$errors = [
    'role' => '',
    'name' => '',
    'email' => '',
    'password' => '',
    'matric_no' => '',
    'class_id' => '',
    'security' => ''
];

$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = trim($_POST['role']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $matric_no = $_POST['matric_no'] ?? '';
    $class_id = $_POST['class_id'] ?? '';
    $question = $_POST['security_question'];
    $answer = trim($_POST['security_answer']);

    // Validation
    if (empty($role)) {
        $errors['role'] = 'Please select a role.';
    }

    if (empty($name)) {
        $errors['name'] = 'Full name is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } else {
        // Email domain validation based on role
        if ($role === 'student' && !preg_match('/@student\.ptss\.edu\.my$/', $email)) {
            $errors['email'] = 'Student email must end with @student.ptss.edu.my';
        } elseif ($role === 'lecturer' && !preg_match('/@ptss\.edu\.my$/', $email)) {
            $errors['email'] = 'Lecturer email must end with @ptss.edu.my';
        }

        // Check if email already exists in either table
        $exists = false;

        // Check students
        $check1 = $conn->prepare("SELECT email FROM students WHERE email = ?");
        $check1->bind_param("s", $email);
        $check1->execute();
        $result1 = $check1->get_result();
        if ($result1->num_rows > 0) $exists = true;
        $check1->close();

        // Check lecturers
        $check2 = $conn->prepare("SELECT email FROM lecturers WHERE email = ?");
        $check2->bind_param("s", $email);
        $check2->execute();
        $result2 = $check2->get_result();
        if ($result2->num_rows > 0) $exists = true;
        $check2->close();

        if ($exists) {
            $errors['email'] = 'Email already registered.';
        }
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }

    if ($role === 'student') {
        if (empty($matric_no)) {
            $errors['matric_no'] = 'Matric number is required.';
        }
        if (empty($class_id)) {
            $errors['class_id'] = 'Please select a class.';
        }
    }

    if (empty($answer)) {
        $errors['security'] = 'Security answer is required.';
    }

    // If no errors, proceed
    if (!array_filter($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($role === 'student') {
            $stmt = $conn->prepare("INSERT INTO students (fullname, email, password, matric_no, class_id, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $name, $email, $hashed_password, $matric_no, $class_id, $question, $answer);
        } elseif ($role === 'lecturer') {
            $stmt = $conn->prepare("INSERT INTO lecturers (fullname, email, password, security_question, security_answer) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashed_password, $question, $answer);
        }

        if ($stmt->execute()) {
            echo "<script>
                    alert('✅ Account created successfully! You can now login.');
                    window.location.href = 'login.php';
                  </script>";
            exit();
        } else {
            $errors['role'] = 'Something went wrong. Please try again.';
        }

        $stmt->close();
    }
}
?>