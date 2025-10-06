<?php
session_start();
include "db.php";
if (!isset($_SESSION['lecturer_id'])) header("Location: index.php");

// CREATE Quiz
if (isset($_POST['create_quiz'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    mysqli_query($conn, "INSERT INTO quizzes (title) VALUES ('$title')");
    $quiz_id = mysqli_insert_id($conn); // dapatkan id quiz baru
    header("Location: add-questions.php?quiz_id=$quiz_id"); 
    exit;
}

// DELETE Quiz
if (isset($_GET['delete_quiz'])) {
    $quiz_id = intval($_GET['delete_quiz']);
    mysqli_query($conn, "DELETE FROM quizzes WHERE quiz_id=$quiz_id");
    header("Location: edit-quiz.php");
    exit;
}

// UPDATE Quiz
if (isset($_POST['update_quiz'])) {
    $quiz_id = intval($_POST['quiz_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    mysqli_query($conn, "UPDATE quizzes SET title='$title' WHERE quiz_id=$quiz_id");
    header("Location: edit-quiz.php");
    exit;
}

// Get all quizzes
$quiz_query = "SELECT * FROM quizzes";
$quizzes = mysqli_query($conn, $quiz_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Quiz - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Edit Quiz</h1>
    <a href="dashboard.php" class="logout">Back</a>
  </header>

  <main class="form-container">

    <!-- CREATE Quiz -->
    <h3>Create New Quiz</h3>
    <form method="post">
      <input type="text" name="title" placeholder="Enter quiz title" required>
      <button type="submit" name="create_quiz">Create Quiz</button>
    </form>
    <hr>

    <!-- LIST Semua Quiz -->
    <h3>All Quizzes</h3>
    <table>
      <thead>
        <tr><th>ID</th><th>Title</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php while($q = mysqli_fetch_assoc($quizzes)) { ?>
          <tr>
            <td><?php echo $q['quiz_id']; ?></td>
            <td><?php echo $q['title']; ?></td>
            <td>
              <!-- Update Form -->
              <form method="post" style="display:inline;">
                <input type="hidden" name="quiz_id" value="<?php echo $q['quiz_id']; ?>">
                <input type="text" name="title" value="<?php echo $q['title']; ?>">
                <button type="submit" name="update_quiz">Update</button>
              </form>
              <a href="edit-quiz.php?delete_quiz=<?php echo $q['quiz_id']; ?>" 
                 onclick="return confirm('Are you sure?');">
                <button type="button">Delete</button>
              </a>
              <a href="add-questions.php?quiz_id=<?php echo $q['quiz_id']; ?>">
                <button type="button">View/Add Questions</button>
              </a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>

    <!-- Back Button bawah -->
    <div style="text-align:center; margin-top:20px;">
      <a href="dashboard.php"><button type="button">Back</button></a>
    </div>

  </main>
</body>
</html>
