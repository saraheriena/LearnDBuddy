<?php
session_start();
include 'db.php';
if (!isset($_SESSION['lecturer_id'])) {
  header("Location: index.php");
  exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

// Add class
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_class'])) {
  $class_name = trim($_POST['class_name']);
  if (!empty($class_name)) {
    $stmt = $conn->prepare("INSERT INTO classes (class_name, lecturer_id) VALUES (?, ?)");
    $stmt->bind_param("si", $class_name, $lecturer_id);
    $stmt->execute();
    header("Location: manage_class.php?msg=✅ Class added successfully");
    exit;
  }
}

// Delete class
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->query("DELETE FROM classes WHERE class_id=$id");
  header("Location: manage_class.php?msg=🗑️ Class deleted");
  exit;
}

// Fetch classes
$res = $conn->query("SELECT * FROM classes WHERE lecturer_id=$lecturer_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Classes - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>Manage Classes</h1>
  <a href="dashboard.php" class="logout">Back</a>
</header>
<main class="table-container">
  <form method="post" style="margin-bottom:15px;">
    <input type="text" name="class_name" placeholder="Enter new class name" required>
    <button type="submit" name="add_class">Add Class</button>
  </form>

  <table>
    <thead>
      <tr><th>ID</th><th>Class Name</th><th>Action</th></tr>
    </thead>
    <tbody>
      <?php while($c = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $c['class_id']; ?></td>
          <td><?= htmlspecialchars($c['class_name']); ?></td>
          <td><a href="manage_class.php?delete=<?= $c['class_id']; ?>" onclick="return confirm('Delete this class?')">Delete</a></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</main>

<?php if (isset($_GET['msg'])): ?>
  <div id="toast" class="toast show"><?= htmlspecialchars($_GET['msg']); ?></div>
  <script>setTimeout(()=>document.getElementById('toast').classList.remove('show'),4000);</script>
<?php endif; ?>
</body>
</html>
