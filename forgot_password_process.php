<?php
include "db.php";

if (isset($_POST['check_email'])) {
    $email = $_POST['email'];

    $sql = "SELECT * FROM lecturers WHERE email=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        session_start();
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_question'] = $row['security_question'];

        // Paparkan form untuk jawab security question
        echo "
        <form method='post'>
          <p>Security Question: <b>{$_SESSION['reset_question']}</b></p>
          <input type='text' name='security_answer' placeholder='Your Answer' required>
          <input type='password' name='new_password' placeholder='Enter New Password' required>
          <button type='submit' name='reset_password'>Reset Password</button>
        </form>
        ";
    } else {
        header("Location: forgot_password.php?error=❌ Email not found.");
    }
}

if (isset($_POST['reset_password'])) {
    session_start();
    $email = $_SESSION['reset_email'];
    $answer = $_POST['security_answer'];
    $newPass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    include "db.php";
    $sql = "SELECT * FROM lecturers WHERE email=? AND security_answer=? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $answer);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $update = $conn->prepare("UPDATE lecturers SET password=? WHERE email=?");
        $update->bind_param("ss", $newPass, $email);
        $update->execute();
        echo "<script>alert('✅ Password reset successful! Please login again.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('❌ Security answer incorrect!'); window.location='forgot_password.php';</script>";
    }
}
?>
