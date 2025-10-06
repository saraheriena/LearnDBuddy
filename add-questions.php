<?php
session_start();
include "db.php";
if (!isset($_SESSION['lecturer_id'])) header("Location: index.php");

if (!isset($_GET['quiz_id'])) {
    header("Location: edit-quiz.php");
    exit;
}
$quiz_id = intval($_GET['quiz_id']);

// Insert new question
if (isset($_POST['add_question'])) {
    $q_text = mysqli_real_escape_string($conn, $_POST['question_text']);
    $a = mysqli_real_escape_string($conn, $_POST['option_a']);
    $b = mysqli_real_escape_string($conn, $_POST['option_b']);
    $c = mysqli_real_escape_string($conn, $_POST['option_c']);
    $d = mysqli_real_escape_string($conn, $_POST['option_d']);
    $correct = mysqli_real_escape_string($conn, $_POST['correct_answer']);

    mysqli_query($conn, "INSERT INTO questions 
        (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer) 
        VALUES ($quiz_id, '$q_text', '$a', '$b', '$c', '$d', '$correct')");
}

// Delete question
if (isset($_GET['delete_question'])) {
    $qid = intval($_GET['delete_question']);
    mysqli_query($conn, "DELETE FROM questions WHERE question_id=$qid AND quiz_id=$quiz_id");
    header("Location: add-questions.php?quiz_id=$quiz_id");
    exit;
}

// Get quiz info
$quiz = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM quizzes WHERE quiz_id=$quiz_id"));

// Get all questions for this quiz
$questions = mysqli_query($conn, "SELECT * FROM questions WHERE quiz_id=$quiz_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Questions - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header><h1>Add Questions for Quiz: <?php echo htmlspecialchars($quiz['title']); ?></h1></header>
  <main class="form-container">

    <!-- FORM Tambah Soalan -->
    <h3>Add New Question</h3>
    <form method="post">
      <textarea name="question_text" placeholder="Enter question text" required></textarea><br>
      <input type="text" name="option_a" placeholder="Option A" required><br>
      <input type="text" name="option_b" placeholder="Option B" required><br>
      <input type="text" name="option_c" placeholder="Option C" required><br>
      <input type="text" name="option_d" placeholder="Option D" required><br>
      <label>Correct Answer:</label>
      <select name="correct_answer" required>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
      </select><br><br>
      <button type="submit" name="add_question">Add Question</button>
    </form>
    <hr>

    <!-- SENARAI Soalan -->
    <h3>Questions List</h3>
    <table>
      <thead>
        <tr><th>No</th><th>Question</th><th>Correct</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php $i=1; while($row=mysqli_fetch_assoc($questions)){ ?>
          <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo htmlspecialchars($row['question_text']); ?></td>
            <td><?php echo $row['correct_answer']; ?></td>
            <td>
              <a href="update-question.php?id=<?php echo $row['question_id']; ?>&quiz_id=<?php echo $quiz_id; ?>"><button>Update</button></a>
              <a href="add-questions.php?quiz_id=<?php echo $quiz_id; ?>&delete_question=<?php echo $row['question_id']; ?>" onclick="return confirm('Delete this question?');"><button>Delete</button></a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>

    <br>
    <a href="edit-quiz.php"><button>Finish & Back to Quiz List</button></a>

  </main>
</body>
</html>
