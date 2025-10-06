<?php
session_start();
include "db.php";
if (!isset($_SESSION['lecturer_id'])) header("Location: index.php");

if (!isset($_GET['id']) || !isset($_GET['quiz_id'])) {
    header("Location: edit-quiz.php");
    exit;
}

$question_id = intval($_GET['id']);
$quiz_id = intval($_GET['quiz_id']);

// Dapatkan data soalan
$q = mysqli_query($conn, "SELECT * FROM questions WHERE question_id=$question_id AND quiz_id=$quiz_id");
if (mysqli_num_rows($q) == 0) {
    echo "Question not found!";
    exit;
}
$question = mysqli_fetch_assoc($q);

// Update question bila lecturer submit
if (isset($_POST['update_question'])) {
    $q_text = mysqli_real_escape_string($conn, $_POST['question_text']);
    $a = mysqli_real_escape_string($conn, $_POST['option_a']);
    $b = mysqli_real_escape_string($conn, $_POST['option_b']);
    $c = mysqli_real_escape_string($conn, $_POST['option_c']);
    $d = mysqli_real_escape_string($conn, $_POST['option_d']);
    $correct = mysqli_real_escape_string($conn, $_POST['correct_answer']);

    mysqli_query($conn, "UPDATE questions 
        SET question_text='$q_text',
            option_a='$a',
            option_b='$b',
            option_c='$c',
            option_d='$d',
            correct_answer='$correct'
        WHERE question_id=$question_id AND quiz_id=$quiz_id");

    header("Location: add-questions.php?quiz_id=$quiz_id");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Question - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header><h1>Update Question</h1></header>
  <main class="form-container">
    <form method="post">
      <label>Question:</label><br>
      <textarea name="question_text" required><?php echo htmlspecialchars($question['question_text']); ?></textarea><br>

      <label>Option A:</label><br>
      <input type="text" name="option_a" value="<?php echo htmlspecialchars($question['option_a']); ?>" required><br>

      <label>Option B:</label><br>
      <input type="text" name="option_b" value="<?php echo htmlspecialchars($question['option_b']); ?>" required><br>

      <label>Option C:</label><br>
      <input type="text" name="option_c" value="<?php echo htmlspecialchars($question['option_c']); ?>" required><br>

      <label>Option D:</label><br>
      <input type="text" name="option_d" value="<?php echo htmlspecialchars($question['option_d']); ?>" required><br>

      <label>Correct Answer:</label>
      <select name="correct_answer" required>
        <option value="A" <?php if($question['correct_answer']=="A") echo "selected"; ?>>A</option>
        <option value="B" <?php if($question['correct_answer']=="B") echo "selected"; ?>>B</option>
        <option value="C" <?php if($question['correct_answer']=="C") echo "selected"; ?>>C</option>
        <option value="D" <?php if($question['correct_answer']=="D") echo "selected"; ?>>D</option>
      </select><br><br>

      <button type="submit" name="update_question">Update</button>
      <a href="add-questions.php?quiz_id=<?php echo $quiz_id; ?>"><button type="button">Cancel</button></a>
    </form>
  </main>
</body>
</html>
