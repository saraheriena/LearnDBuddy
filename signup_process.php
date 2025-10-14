<?php
include 'db.php';

// Array untuk simpan error per field
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
    $role = $_POST['role'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password_plain = trim($_POST['password'] ?? '');
    $question = $_POST['security_question'] ?? '';
    $answer = trim($_POST['security_answer'] ?? '');
    $matric_no = trim($_POST['matric_no'] ?? '');
    $class_id = $_POST['class_id'] ?? '';

    // Role validation
    if (empty($role)) {
        $errors['role'] = "Please select a role.";
    }

    // Name validation
    if (empty($name)) {
        $errors['name'] = "Full name is required.";
    }

    // Email validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Valid email is required.";
    }

    // Password validation
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password_plain)) {
    $errors['password'] = "Password must be 8+ chars, include uppercase, lowercase, number & symbol.";
    }

    // Security question validation
    if (empty($question) || empty($answer)) {
        $errors['security'] = "Please select a security question and provide an answer.";
    }

    // Student-specific validation
    if ($role === 'student') {
        if (empty($matric_no)) {
            $errors['matric_no'] = "Matric number is required.";
        }

        if (empty($class_id)) {
            $errors['class_id'] = "Please select a class.";
        }
    }

    // Email duplicate check across BOTH tables (lecturers + students)
    if (empty($errors['email'])) {
        // gunakan UNION untuk cari di kedua-dua jadual
        $checkSql = "SELECT email FROM lecturers WHERE email = ? UNION SELECT email FROM students WHERE email = ?";
        if ($checkEmail = $conn->prepare($checkSql)) {
            // bind dua kali (sama email)
            $checkEmail->bind_param("ss", $email, $email);
            $checkEmail->execute();
            $checkEmail->store_result();

            if ($checkEmail->num_rows > 0) {
                $errors['email'] = "This email is already registered.";
            }
            $checkEmail->close();
        } else {
            // fallback: jika prepare gagal (jarang), set generic error
            $errors['email'] = "Error checking email uniqueness. Try again.";
        }
    }

    // Jika tiada error, insert ke database
    if (!array_filter($errors)) {
        $password = password_hash($password_plain, PASSWORD_DEFAULT);

        if ($role === 'lecturer') {
            $stmt = $conn->prepare("INSERT INTO lecturers (fullname,email,password,security_question,security_answer) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $name, $email, $password, $question, $answer);
        } else {
            $stmt = $conn->prepare("INSERT INTO students (fullname,email,password,matric_no,class_id,security_question,security_answer) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssssss", $name, $email, $password, $matric_no, $class_id, $question, $answer);
        }

        if ($stmt->execute()) {
            // berjaya
            echo "<script>
                alert('✅ Account created successfully! Please login now.');
                window.location.href = 'login.php';
            </script>";
            $stmt->close();
            exit;
        } else {
            $errors['password'] = "Registration failed: " . $stmt->error;
            $stmt->close();
        }
    }
}
?>