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
    // Check if class already exists globally
    $check = $conn->prepare("SELECT * FROM classes WHERE class_name = ?");
    $check->bind_param("s", $class_name);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;

    if (!$exists) {
      // Insert new class (still record who created it)
      $stmt = $conn->prepare("INSERT INTO classes (class_name, lecturer_id) VALUES (?, ?)");
      $stmt->bind_param("si", $class_name, $lecturer_id);
      $stmt->execute();
      header("Location: manage_class.php?msg=✅ Class added successfully");
      exit;
    } else {
      header("Location: manage_class.php?msg=⚠️ Class name already exists");
      exit;
    }
  }
}

// Delete class
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $conn->query("DELETE FROM classes WHERE class_id=$id");
  header("Location: manage_class.php?msg=🗑️ Class deleted");
  exit;
}

// Fetch ALL classes (global list, visible to every lecturer)
$res = $conn->query("
  SELECT c.class_id, c.class_name, l.fullname AS creator_name
  FROM classes c
  LEFT JOIN lecturers l ON c.lecturer_id = l.lecturer_id
  ORDER BY c.class_id DESC
");
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
      <tr>
        <th>Class Name</th>
        <th>Created By</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while($c = $res->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($c['class_name']); ?></td>
          <td><?= htmlspecialchars($c['creator_name'] ?? 'Unknown'); ?></td>
          <td>
            <a href="manage_class.php?delete=<?= $c['class_id']; ?>" 
               onclick="return confirm('Delete this class?')">Delete</a>
          </td>
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
