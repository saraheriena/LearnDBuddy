<?php
session_start();
include "db.php";

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

if (!isset($_GET['pb_id'])) {
    header("Location: view-pb-result.php");
    exit;
}

$pb_id = intval($_GET['pb_id']);

// ambil data PB berdasarkan ID
$sql = "SELECT 
            p.pb_id, 
            s.fullname AS student_name, 
            c.class_name, 
            p.quiz1, p.quiz2, p.test, 
            p.practical1, p.practical2,
            p.total_pb, p.category, p.feedback
        FROM pb_results p
        JOIN students s ON p.student_id = s.student_id
        LEFT JOIN classes c ON s.class_id = c.class_id
        WHERE p.pb_id = $pb_id AND c.lecturer_id = $lecturer_id
        LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<p style='color:red;text-align:center;'>Record not found or access denied.</p>";
    exit;
}

$row = $result->fetch_assoc();

// jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz1 = floatval($_POST['quiz1']);
    $quiz2 = floatval($_POST['quiz2']);
    $test = floatval($_POST['test']);
    $practical1 = floatval($_POST['practical1']);
    $practical2 = floatval($_POST['practical2']);
    $feedback = $conn->real_escape_string($_POST['feedback']);

    // kira semula total
    $total_pb = $quiz1 + $quiz2 + $test + $practical1 + $practical2;

    // assign category
    if ($total_pb >= 31) {
        $category = 'Excellent';
    } elseif ($total_pb >= 21) {
        $category = 'Average';
    } else {
        $category = 'Below Average';
    }

    // update dalam db
    $update = "UPDATE pb_results 
               SET quiz1=$quiz1, quiz2=$quiz2, test=$test, 
                   practical1=$practical1, practical2=$practical2, 
                   total_pb=$total_pb, category='$category', feedback='$feedback', 
                   updated_at=NOW()
               WHERE pb_id=$pb_id";

    if ($conn->query($update)) {
        header("Location: view-pb-result.php?msg=updated");
        exit;
    } else {
        echo "<p style='color:red;text-align:center;'>Error updating record!</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit PB Result - LearnDBuddy</title>
<style>
body { font-family: Segoe UI, sans-serif; background:#f5faff; margin:0;}
header { background: linear-gradient(90deg,#1e90ff,#4facfe); color:#fff; padding:16px 24px; display:flex; justify-content:space-between; align-items:center;}
.back { background:#555; color:#fff; padding:8px 14px; border-radius:6px; text-decoration:none;}
.container { max-width:600px; background:white; margin:30px auto; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.1);}
label { font-weight:bold; display:block; margin-top:10px;}
input, textarea { width:100%; padding:8px; margin-top:4px; border:1px solid #ccc; border-radius:5px;}
button { margin-top:15px; background:#1e90ff; color:white; padding:10px 15px; border:none; border-radius:6px; cursor:pointer;}
button:hover { background:#187bcd;}
</style>
</head>
<body>
<header>
<h1>Edit PB Result</h1>
<a href="view-pb-result.php" class="back">Back</a>
</header>

<div class="container">
<form method="post">
<p><strong>Student:</strong> <?= htmlspecialchars($row['student_name']) ?></p>
<p><strong>Class:</strong> <?= htmlspecialchars($row['class_name']) ?></p>

<label>Quiz 1</label>
<input type="number" name="quiz1" value="<?= $row['quiz1'] ?>" step="0.01" required>

<label>Quiz 2</label>
<input type="number" name="quiz2" value="<?= $row['quiz2'] ?>" step="0.01" required>

<label>Test</label>
<input type="number" name="test" value="<?= $row['test'] ?>" step="0.01" required>

<label>Practical Work 1</label>
<input type="number" name="practical1" value="<?= $row['practical1'] ?>" step="0.01" required>

<label>Practical Work 2</label>
<input type="number" name="practical2" value="<?= $row['practical2'] ?>" step="0.01" required>

<label>Feedback</label>
<textarea name="feedback" rows="4"><?= htmlspecialchars($row['feedback']) ?></textarea>

<button type="submit">Update Result</button>
</form>
</div>
</body>
</html>
