<?php
session_start();
include "db.php";
if (!isset($_SESSION['lecturer_id'])) header("Location: index.php");

$lecturer_id = $_SESSION['lecturer_id'];

// CREATE Quiz
if (isset($_POST['create_quiz'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $class_id = $_POST['class_id'];

    // Kalau pilih "All Classes", simpan NULL
    if ($class_id == "0" || $class_id == "") {
        $class_id_sql = "NULL";
    } else {
        $class_id_sql = intval($class_id);
    }

    $sql = "INSERT INTO quizzes (title, class_id) VALUES ('$title', $class_id_sql)";
    if (!mysqli_query($conn, $sql)) {
        die("Error inserting quiz: " . mysqli_error($conn));
    }

    $quiz_id = mysqli_insert_id($conn);
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
    $class_id = $_POST['class_id'];

    if ($class_id == "0" || $class_id == "") {
        $class_id_sql = "NULL";
    } else {
        $class_id_sql = intval($class_id);
    }

    $sql = "UPDATE quizzes SET title='$title', class_id=$class_id_sql WHERE quiz_id=$quiz_id";
    if (!mysqli_query($conn, $sql)) {
        die("Error updating quiz: " . mysqli_error($conn));
    }

    header("Location: edit-quiz.php");
    exit;
}

// Ambil semua kelas lecturer
$classes = mysqli_query($conn, "SELECT * FROM classes WHERE lecturer_id=$lecturer_id");

// Get all quizzes (termasuk yang class_id NULL)
$quiz_query = "SELECT q.*, c.class_name 
               FROM quizzes q 
               LEFT JOIN classes c ON q.class_id=c.class_id";
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
      <select name="class_id" required>
        <option value="">-- Select Class --</option>
        <option value="0">All Classes</option>
        <?php while($c = mysqli_fetch_assoc($classes)) { ?>
          <option value="<?= $c['class_id']; ?>"><?= htmlspecialchars($c['class_name']); ?></option>
        <?php } ?>
      </select>
      <button type="submit" name="create_quiz">Create Quiz</button>
    </form>
    <hr>

    <!-- LIST Semua Quiz -->
    <h3>All Quizzes</h3>
    <table>
      <thead>
        <tr><th>ID</th><th>Title</th><th>Class</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php while($q = mysqli_fetch_assoc($quizzes)) { ?>
          <tr>
            <td><?= $q['quiz_id']; ?></td>
            <td><?= htmlspecialchars($q['title']); ?></td>
            <td>
              <?= is_null($q['class_id']) ? 'All Classes' : htmlspecialchars($q['class_name'] ?? 'Unassigned'); ?>
            </td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="quiz_id" value="<?= $q['quiz_id']; ?>">
                <input type="text" name="title" value="<?= htmlspecialchars($q['title']); ?>">
                <select name="class_id">
                  <option value="0" <?= is_null($q['class_id']) ? 'selected' : ''; ?>>All Classes</option>
                  <?php
                  $classList = mysqli_query($conn, "SELECT * FROM classes WHERE lecturer_id=$lecturer_id");
                  while ($c = mysqli_fetch_assoc($classList)) {
                      $sel = ($c['class_id'] == $q['class_id']) ? 'selected' : '';
                      echo "<option value='{$c['class_id']}' $sel>{$c['class_name']}</option>";
                  }
                  ?>
                </select>
                <button type="submit" name="update_quiz">Update</button>
              </form>
              <a href="edit-quiz.php?delete_quiz=<?= $q['quiz_id']; ?>" onclick="return confirm('Delete this quiz?');">
                <button type="button">Delete</button>
              </a>
              <a href="add-questions.php?quiz_id=<?= $q['quiz_id']; ?>">
                <button type="button">View/Add Questions</button>
              </a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>

    <div style="text-align:center; margin-top:20px;">
      <a href="dashboard.php"><button type="button">Back</button></a>
    </div>
  </main>
</body>
</html>
