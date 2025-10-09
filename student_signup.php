<?php
include "db.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $matric_no = $_POST['matric_no'];
    $class_id = $_POST['class_id'];
    $security_question = $_POST['security_question'];
    $security_answer = $_POST['security_answer'];

    $check = mysqli_query($conn, "SELECT * FROM students WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "INSERT INTO students(fullname,email,password,matric_no,class_id,security_question,security_answer)
                VALUES('$fullname','$email','$password','$matric_no','$class_id','$security_question','$security_answer')";
        if (mysqli_query($conn, $sql)) {
            header("Location: login.php?success=1");
            exit;
        } else {
            $error = "Registration failed!";
        }
    }
}

$class_sql = "SELECT * FROM classes ORDER BY class_name";
$class_res = mysqli_query($conn, $class_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Signup - LearnDBuddy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Student Signup</h2>
    <?php if(isset($error)): ?><p class="error"><?= $error; ?></p><?php endif; ?>

    <form method="post">
        <label>Full Name:</label>
        <input type="text" name="fullname" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Matric Number:</label>
        <input type="text" name="matric_no" required>

        <label>Class:</label>
        <select name="class_id" required>
            <option value="">-- Select Class --</option>
            <?php while($c = mysqli_fetch_assoc($class_res)): ?>
                <option value="<?= $c['class_id']; ?>"><?= htmlspecialchars($c['class_name']); ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Soalan keselamatan bawah sekali -->
        <label>Security Question:</label>
        <input type="text" name="security_question" placeholder="e.g. What is your pet's name?" required>

        <label>Security Answer:</label>
        <input type="text" name="security_answer" required>

        <button type="submit">Sign Up</button>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>
</body>
</html>
