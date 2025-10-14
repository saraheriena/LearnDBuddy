<?php
session_start();
include 'db.php';

if (!isset($_SESSION['lecturer_id'])) {
  header("Location: index.php");
  exit;
}

$edit_mode = false;
$edit_note = null;

// DELETE NOTE
if (isset($_GET['delete'])) {
  $note_id = intval($_GET['delete']);
  $file_query = $conn->prepare("SELECT file_path FROM notes WHERE note_id=? AND lecturer_id=?");
  $file_query->bind_param("ii", $note_id, $_SESSION['lecturer_id']);
  $file_query->execute();
  $file_result = $file_query->get_result()->fetch_assoc();

  if ($file_result) {
    $file_path = $file_result['file_path'];
    $del_stmt = $conn->prepare("DELETE FROM notes WHERE note_id=? AND lecturer_id=?");
    $del_stmt->bind_param("ii", $note_id, $_SESSION['lecturer_id']);
    $del_stmt->execute();
    if (file_exists($file_path)) unlink($file_path);
  }
  header("Location: edit_notes.php");
  exit;
}

// LOAD EDIT DATA
if (isset($_GET['edit'])) {
  $edit_id = intval($_GET['edit']);
  $edit_mode = true;
  $edit_stmt = $conn->prepare("SELECT * FROM notes WHERE note_id=? AND lecturer_id=?");
  $edit_stmt->bind_param("ii", $edit_id, $_SESSION['lecturer_id']);
  $edit_stmt->execute();
  $edit_note = $edit_stmt->get_result()->fetch_assoc();
}

// UPLOAD OR UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $title = trim($_POST['title']);
  $class_id = $_POST['class_id'];
  $topic = $_POST['topic'] ?? null;
  $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
  $file = $_FILES['file'];

  // === UPDATE MODE ===
  if ($note_id > 0) {
    $old_stmt = $conn->prepare("SELECT file_path FROM notes WHERE note_id=? AND lecturer_id=?");
    $old_stmt->bind_param("ii", $note_id, $_SESSION['lecturer_id']);
    $old_stmt->execute();
    $old_file = $old_stmt->get_result()->fetch_assoc();
    $targetFile = $old_file['file_path'];

    // upload fail baru
    if (!empty($file['name']) && $file['error'] === 0) {
      $targetDir = "uploads/";
      if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
      $fileName = time() . "_" . basename($file['name']);
      $targetFile = $targetDir . $fileName;
      move_uploaded_file($file['tmp_name'], $targetFile);
      if (file_exists($old_file['file_path'])) unlink($old_file['file_path']);
    }

    if ($class_id === "all") {
      // delete lama, insert semula untuk semua kelas
      $conn->query("DELETE FROM notes WHERE note_id=$note_id AND lecturer_id=" . $_SESSION['lecturer_id']);
      $all_classes = $conn->query("SELECT class_id FROM classes");
      while ($c = $all_classes->fetch_assoc()) {
        $stmt = $conn->prepare("INSERT INTO notes (lecturer_id, class_id, title, file_path, topic) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $_SESSION['lecturer_id'], $c['class_id'], $title, $targetFile, $topic);
        $stmt->execute();
      }
    } else {
      // update biasa
      $stmt = $conn->prepare("UPDATE notes SET title=?, class_id=?, file_path=?, topic=? WHERE note_id=? AND lecturer_id=?");
      $stmt->bind_param("sisiii", $title, $class_id, $targetFile, $topic, $note_id, $_SESSION['lecturer_id']);
      $stmt->execute();
    }

    header("Location: edit_notes.php");
    exit;
  }

  // === UPLOAD BARU ===
  if ($file['error'] === 0) {
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . "_" . basename($file['name']);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
      if ($class_id === "all") {
        $all_classes = $conn->query("SELECT class_id FROM classes");
        while ($c = $all_classes->fetch_assoc()) {
          $stmt = $conn->prepare("INSERT INTO notes (lecturer_id, class_id, title, file_path, topic) VALUES (?, ?, ?, ?, ?)");
          $stmt->bind_param("iissi", $_SESSION['lecturer_id'], $c['class_id'], $title, $targetFile, $topic);
          $stmt->execute();
        }
      } else {
        $stmt = $conn->prepare("INSERT INTO notes (lecturer_id, class_id, title, file_path, topic) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $_SESSION['lecturer_id'], $class_id, $title, $targetFile, $topic);
        $stmt->execute();
      }
    }
  }

  header("Location: edit_notes.php");
  exit;
}

// FETCH DATA
$class_query = $conn->query("SELECT * FROM classes");
$notes_query = $conn->prepare("
  SELECT n.*, c.class_name
  FROM notes n
  JOIN classes c ON n.class_id = c.class_id
  WHERE n.lecturer_id = ?
  ORDER BY n.note_id DESC
");
$notes_query->bind_param("i", $_SESSION['lecturer_id']);
$notes_query->execute();
$notes_result = $notes_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Notes - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { font-family: Arial, sans-serif; background: #f5f7fa; margin: 0; padding: 0; display: flex; flex-direction: column; min-height: 100vh; }
    header { background: #1e90ff; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
    main.dashboard { display: flex; flex-direction: column; align-items: center; margin: 40px auto; width: 100%; max-width: 700px; }
    .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); padding: 25px; width: 100%; text-align: center; margin-bottom: 30px; }
    form { display: flex; flex-direction: column; gap: 12px; align-items: stretch; }
    input[type="text"], select, input[type="file"] { padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
    button { background: #1e90ff; color: #fff; border: none; padding: 10px 18px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: background 0.2s ease; }
    button:hover { background: #187bcd; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #1e90ff; color: #fff; }
    td a { color: #1e90ff; text-decoration: none; margin-right: 10px; }
    td a:hover { text-decoration: underline; }
  </style>
</head>
<body>
<header>
  <h1>Upload Notes</h1>
  <a href="dashboard.php" style="color:#fff;text-decoration:none;font-weight:bold;">Back</a>
</header>

<main class="dashboard">
  <div class="card">
    <i class="fas fa-upload"></i>
    <h2><?= $edit_mode ? "Edit Note" : "Upload PDF or Video" ?></h2>

    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="note_id" value="<?= $edit_mode ? $edit_note['note_id'] : '' ?>">

      <input type="text" name="title" placeholder="Note Title" required value="<?= $edit_mode ? htmlspecialchars($edit_note['title']) : '' ?>">

      <select name="topic" required>
        <option value="">-- Select Topic --</option>
        <?php for ($i=1; $i<=5; $i++): ?>
          <option value="<?= $i ?>" <?= ($edit_mode && $edit_note['topic']==$i)?'selected':''; ?>>Topic <?= $i ?></option>
        <?php endfor; ?>
      </select>

      <select name="class_id" required>
        <option value="">-- Select Class --</option>
        <option value="all">All Classes</option>
        <?php
        $class_query->data_seek(0);
        while ($row = $class_query->fetch_assoc()):
          $selected = ($edit_mode && $edit_note['class_id'] == $row['class_id']) ? 'selected' : '';
        ?>
          <option value="<?= $row['class_id']; ?>" <?= $selected; ?>><?= htmlspecialchars($row['class_name']); ?></option>
        <?php endwhile; ?>
      </select>

      <input type="file" name="file" accept=".pdf,video/*">
      <?php if ($edit_mode && !empty($edit_note['file_path'])): ?>
        <div style="text-align:left;font-size:14px;">Current File:
          <a href="<?= htmlspecialchars($edit_note['file_path']); ?>" target="_blank">View existing file</a>
        </div>
      <?php endif; ?>

      <button type="submit"><?= $edit_mode ? "Update Note" : "Upload" ?></button>
    </form>
  </div>

  <div class="notes-list" style="background:#fff;border-radius:12px;box-shadow:0 2px 6px rgba(0,0,0,0.1);width:100%;padding:20px;">
    <h3>Uploaded Notes</h3>
    <table>
      <tr>
        <th>No</th>
        <th>Title</th>
        <th>Topic</th>
        <th>Class</th>
        <th>File</th>
        <th>Action</th>
      </tr>
      <?php 
      $no = 1;
      if ($notes_result->num_rows > 0):
        while ($note = $notes_result->fetch_assoc()): ?>
          <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($note['title']); ?></td>
            <td><?= htmlspecialchars($note['topic']); ?></td>
            <td><?= htmlspecialchars($note['class_name']); ?></td>
            <td><a href="<?= htmlspecialchars($note['file_path']); ?>" target="_blank">View</a></td>
            <td>
              <a href="?edit=<?= $note['note_id']; ?>">Edit</a> | 
              <a href="?delete=<?= $note['note_id']; ?>" onclick="return confirm('Delete this note?');">Delete</a>
            </td>
          </tr>
        <?php endwhile;
      else: ?>
        <tr><td colspan="6" style="text-align:center;">No notes uploaded yet.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</main>
</body>
</html>
