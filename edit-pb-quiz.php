<?php
session_start();
include "db.php";

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

/* --- Pastikan table assessments wujud --- */
$conn->query("CREATE TABLE IF NOT EXISTS assessments (
    assessment_id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('Quiz 1', 'Quiz 2', 'Test', 'Practical Work 1', 'Practical Work 2') NOT NULL,
    title VARCHAR(100) NOT NULL,
    class_id INT NULL,
    total_marks INT DEFAULT 10,
    weightage DECIMAL(5,2) DEFAULT 0.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
)");

/* --- CREATE NEW PB ASSESSMENT --- */
if (isset($_POST['create_pb'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $type = $conn->real_escape_string($_POST['type']);
    $class_id = $_POST['class_id'];
    $weightage = $_POST['weightage'];

    $class_sql = ($class_id == "0" || $class_id == "") ? "NULL" : intval($class_id);

    $sql = "INSERT INTO assessments (title, type, class_id, weightage)
            VALUES ('$title', '$type', $class_sql, $weightage)";
    if (!$conn->query($sql)) {
        die("Error inserting PB assessment: " . $conn->error);
    }
    header("Location: edit-pb-quiz.php");
    exit;
}

/* --- DELETE PB ASSESSMENT --- */
if (isset($_GET['delete_pb'])) {
    $assessment_id = intval($_GET['delete_pb']);
    $conn->query("DELETE FROM assessments WHERE assessment_id=$assessment_id");
    header("Location: edit-pb-quiz.php");
    exit;
}

/* --- UPDATE PB ASSESSMENT --- */
if (isset($_POST['update_pb'])) {
    $assessment_id = intval($_POST['assessment_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $type = $conn->real_escape_string($_POST['type']);
    $class_id = $_POST['class_id'];
    $weightage = $_POST['weightage'];

    $class_sql = ($class_id == "0" || $class_id == "") ? "NULL" : intval($class_id);

    $sql = "UPDATE assessments 
            SET title='$title', type='$type', class_id=$class_sql, weightage=$weightage
            WHERE assessment_id=$assessment_id";
    if (!$conn->query($sql)) {
        die("Error updating PB assessment: " . $conn->error);
    }
    header("Location: edit-pb-quiz.php");
    exit;
}

/* --- FETCH DATA --- */
$classes = $conn->query("SELECT * FROM classes WHERE lecturer_id=$lecturer_id");
$pb_list = $conn->query("SELECT a.*, c.class_name 
                         FROM assessments a 
                         LEFT JOIN classes c ON a.class_id=c.class_id 
                         ORDER BY a.assessment_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit PB Assessment - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <style>
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: center;
    }
    input[type="text"], input[type="number"], select {
      width: 95%;
      padding: 6px;
      box-sizing: border-box;
    }
    button {
      background-color: #1e90ff;
      color: #fff;
      border: none;
      padding: 6px 12px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }
    button:hover {
      background-color: #187bcd;
    }
    .btn-danger {
      background-color: #1e90ff;
    }
    .btn-danger:hover {
      background-color: #1e90ff;
    }
    .btn-success {
      background-color: #1e90ff;
    }
    .btn-success:hover {
      background-color: #1e90ff;
    }
  </style>
</head>
<body>
  <header>
    <h1>Edit PB Assessment</h1>
    <a href="dashboard.php" class="logout">Back</a>
  </header>

  <main class="form-container">
    <!-- CREATE PB -->
    <h3>Create New PB Assessment</h3>
    <form method="post">
      <input type="text" name="title" placeholder="Enter assessment title" required>

      <select name="type" required>
        <option value="">-- Select Type --</option>
        <option value="Quiz 1">Quiz 1</option>
        <option value="Quiz 2">Quiz 2</option>
        <option value="Test">Test</option>
        <option value="Practical Work 1">Practical Work 1</option>
        <option value="Practical Work 2">Practical Work 2</option>
      </select>

      <select name="class_id" required>
        <option value="">-- Select Class --</option>
        <option value="0">All Classes</option>
        <?php while($c = $classes->fetch_assoc()) { ?>
          <option value="<?= $c['class_id']; ?>"><?= htmlspecialchars($c['class_name']); ?></option>
        <?php } ?>
      </select>

      <input type="number" name="weightage" step="0.01" placeholder="Weightage (e.g. 10)" required>

      <button type="submit" name="create_pb">Create PB</button>
    </form>
    <hr>

    <!-- LIST Semua PB -->
    <h3>All PB Assessments</h3>
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Type</th>
          <th>Class</th>
          <th>Weightage (%)</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($pb_list->num_rows > 0): ?>
          <?php while($p = $pb_list->fetch_assoc()): ?>
            <tr>
              <form method="post" style="display:flex; align-items:center; gap:5px;">
                <input type="hidden" name="assessment_id" value="<?= $p['assessment_id']; ?>">
                <td><input type="text" name="title" value="<?= htmlspecialchars($p['title']); ?>"></td>
                <td>
                  <select name="type">
                    <?php
                    $types = ['Quiz 1','Quiz 2','Test','Practical Work 1','Practical Work 2'];
                    foreach ($types as $t) {
                        $sel = ($t == $p['type']) ? 'selected' : '';
                        echo "<option value='$t' $sel>$t</option>";
                    }
                    ?>
                  </select>
                </td>
                <td>
                  <select name="class_id">
                    <option value="0" <?= is_null($p['class_id']) ? 'selected' : ''; ?>>All Classes</option>
                    <?php
                    $classList = $conn->query("SELECT * FROM classes WHERE lecturer_id=$lecturer_id");
                    while ($c = $classList->fetch_assoc()) {
                        $sel = ($c['class_id'] == $p['class_id']) ? 'selected' : '';
                        echo "<option value='{$c['class_id']}' $sel>{$c['class_name']}</option>";
                    }
                    ?>
                  </select>
                </td>
                <td><input type="number" name="weightage" step="0.01" value="<?= $p['weightage']; ?>"></td>
                <td>
                  <button type="submit" name="update_pb">Update</button>
                  <a href="edit-pb-quiz.php?delete_pb=<?= $p['assessment_id']; ?>" onclick="return confirm('Delete this PB assessment?');">
                    <button type="button" class="btn-danger">Delete</button>
                  </a>
                  <a href="edit-pb-questions.php?assessment_id=<?= $p['assessment_id']; ?>">
                    <button type="button" class="btn-success">Edit Questions</button>
                  </a>
                </td>
              </form>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="5">No PB assessments found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <div style="text-align:center; margin-top:20px;">
      <a href="view-pb-results.php"><button type="button">View PB Results</button></a>
    </div>
  </main>
</body>
</html>
