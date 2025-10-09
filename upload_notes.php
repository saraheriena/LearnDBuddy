<?php
session_start();
include 'db.php';

if (!isset($_SESSION['lecturer_id'])) {
  header("Location: index.php");
  exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = $_POST['title'];
  $file = $_FILES['file'];
  $class_id = $_POST['class_id'];

  if ($file['error'] === 0) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir);

    $fileName = basename($file['name']);
    $targetFile = $targetDir . time() . "_" . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
      $stmt = $conn->prepare("INSERT INTO notes (lecturer_id, class_id, title, file_path) VALUES (?, ?, ?, ?)");
      $stmt->bind_param("iiss", $_SESSION['lecturer_id'], $class_id, $title, $targetFile);
      $stmt->execute();
      $msg = "Note uploaded successfully!";
    } else {
      $msg = "Failed to upload file!";
    }
  } else {
    $msg = "Error uploading file!";
  }
}

$class_query = $conn->query("SELECT * FROM classes");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Notes - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
  <div class="header-left">
    <img src="logo.png" alt="LearnDBuddy Logo" class="logo" style="height:50px;">
    <h1>Upload Notes</h1>
  </div>
  <a href="lecturer_dashboard.php" class="logout">Back</a>
</header>

<main class="dashboard">
  <div class="card" style="width:500px;">
    <i class="fas fa-upload"></i>
    <h2>Upload PDF or Video</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="text" name="title" placeholder="Note Title" required>
      <select name="class_id" required>
        <option value="">-- Select Class --</option>
        <?php while ($row = $class_query->fetch_assoc()): ?>
          <option value="<?= $row['class_id']; ?>"><?= htmlspecialchars($row['class_name']); ?></option>
        <?php endwhile; ?>
      </select>
      <input type="file" name="file" accept=".pdf,video/*" required>
      <button type="submit">Upload</button>
    </form>
  </div>
</main>

<?php if ($msg): ?>
  <div id="toast" class="toast show">
    <?= htmlspecialchars($msg); ?>
  </div>
  <script>
    setTimeout(() => document.getElementById('toast').classList.remove('show'), 4000);
  </script>
<?php endif; ?>
</body>
</html>
