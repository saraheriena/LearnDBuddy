<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
  header("Location: index.php");
  exit;
}

$class_id = $_SESSION['class_id'] ?? null;

// ================================
// Konfigurasi URL & path uploads
// ================================
$projectBaseUrl = '/learnDBuddy';   // <-- TUKAR jika folder projek lain
$uploadsUrl     = $projectBaseUrl . '/uploads/';
$uploadsDir     = __DIR__ . '/uploads';

// ================================
// Ambil parameter ?topic=1..5
// ================================
$topic = isset($_GET['topic']) ? (int)$_GET['topic'] : 0;
if ($topic < 1 || $topic > 5) {
  header("Location: view_notes.php");
  exit;
}

// ================================
// Semak kewujudan lajur 'topic'
// ================================
$hasTopicColumn = false;
$check = $conn->query("SHOW COLUMNS FROM notes LIKE 'topic'");
if ($check && $check->num_rows > 0) $hasTopicColumn = true;

// ================================
// Fallback: detect topik dari tajuk
// ================================
function detect_topic_from_title($title) {
  if (preg_match('/\b(?:topic|topik)\s*(\d)\b/i', $title, $m)) {
    $n = (int)$m[1];
    if ($n >= 1 && $n <= 5) return $n;
  }
  return null;
}

// ================================
// Ambil semua nota untuk TOPIK ini
// ================================
if ($hasTopicColumn) {
  $stmt = $conn->prepare("
    SELECT note_id, title, file_path
    FROM notes
    WHERE class_id = ? AND topic = ?
    ORDER BY note_id DESC
  ");
  $stmt->bind_param("ii", $class_id, $topic);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
  $stmt = $conn->prepare("
    SELECT note_id, title, file_path
    FROM notes
    WHERE class_id = ?
    ORDER BY note_id DESC
  ");
  $stmt->bind_param("i", $class_id);
  $stmt->execute();
  $all = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $rows = array_values(array_filter($all, function($r) use ($topic) {
    $t = detect_topic_from_title($r['title'] ?? '');
    return $t === $topic;
  }));
}

// ================================
// Pisahkan PDF vs VIDEO
// ================================
function is_pdf($filePath) {
  return (bool)preg_match('/\.pdf$/i', $filePath ?? '');
}

$pdfNotes = [];
$videoNotes = [];
foreach ($rows as $r) {
  if (is_pdf($r['file_path'] ?? '')) {
    $pdfNotes[] = $r;
  } else {
    $videoNotes[] = $r;
  }
}
$pdfCount = count($pdfNotes);

// ================================
// Ambil quiz ikut topik ini
// ================================
$quizStmt = $conn->prepare("SELECT quiz_id, title FROM quizzes WHERE topic = ?");
$quizStmt->bind_param("i", $topic);
$quizStmt->execute();
$quizRes = $quizStmt->get_result();
$quizzes = $quizRes->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Topic <?= htmlspecialchars($topic) ?></title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .btn-download, .btn-primary{
      display:inline-block; padding:10px 14px; border-radius:8px; text-decoration:none;
      font-weight:600; border:none;
    }
    .btn-download{ background:#e9f3ff; border:1px solid #b9dafd; color:#1e90ff; }
    .btn-download:hover{ background:#d7eaff; }
    .btn-primary{ background:#1e90ff; color:#fff; }
    .btn-primary:hover{ background:#0b66c2; }
    .file-list{ list-style:none; margin:0; padding:0; }
    .file-item{ display:flex; justify-content:space-between; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #eee; }
    .file-item:last-child{ border-bottom:none; }
    .file-title{ margin:0; font-weight:600; color:#1e90ff; }
    .section-title{ margin:0 0 10px; color:#1e90ff; font-size:18px; font-weight:700; display:flex; align-items:center; gap:8px;}
    .topic-wrap{ width:100%; max-width:1100px; margin:0 auto; display:flex; flex-direction:column; gap:16px; }
  </style>
</head>
<body>
<header>
  <div class="header-left">
    <img src="logo.png" alt="LearnDBuddy Logo" class="logo" style="height:50px;">
    <h1>Topic <?= htmlspecialchars($topic) ?></h1>
  </div>
  <a href="view_notes.php" class="logout">Back</a>
</header>

<main class="dashboard" style="flex-direction:column;align-items:stretch;gap:16px;padding-top:16px;">
  <div class="topic-wrap">

    <!-- Kad: PDF Files -->
    <div class="form-container">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
        <h3 class="section-title" style="margin:0;">
          <i class="fas fa-file-pdf"></i> PDF Files
        </h3>
      </div>

      <?php if ($pdfCount > 0): ?>
        <ul class="file-list">
          <?php foreach ($pdfNotes as $p): 
            $title = $p['title'] ?? 'Untitled';
            $path  = $p['file_path'] ?? '';
            $url   = $uploadsUrl . rawurlencode(basename($path));
          ?>
            <li class="file-item">
              <p class="file-title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></p>
              <span>
                <a class="btn-download" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" download>
                  <i class="fas fa-download"></i> Download
                </a>
                <a class="btn-primary" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                  <i class="fas fa-eye"></i> View
                </a>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="margin:10px 0 0;color:#666;">No note available for this topic yet.</p>
      <?php endif; ?>
    </div>

    <!-- Kad: Video Notes -->
    <div class="form-container">
      <h3 class="section-title"><i class="fas fa-video"></i> Video Notes</h3>
      <?php if (!empty($videoNotes)): ?>
        <section class="dashboard" style="justify-content:center;gap:24px;padding:0;flex-wrap:wrap;">
          <?php foreach ($videoNotes as $v): 
            $title = $v['title'] ?? 'Untitled';
            $path  = $v['file_path'] ?? '';
            $url   = $uploadsUrl . rawurlencode(basename($path));
          ?>
            <div class="card" style="width:320px;">
              <i class="fas fa-file-video"></i>
              <h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>
              <video controls preload="metadata" style="width:100%;border-radius:8px;">
                <source src="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                Your browser does not support the video tag.
              </video>
            </div>
          <?php endforeach; ?>
        </section>
      <?php else: ?>
        <p style="margin:0;color:#666;">No video available for this topic yet</p>
      <?php endif; ?>
    </div>

    <!-- Kad: Quiz untuk topik ini -->
    <div class="form-container" style="text-align:center;">
      <h3 class="section-title" style="justify-content:center;">
        <i class="fas fa-question-circle"></i> Ready to test your knowledge
      </h3>

      <?php if (!empty($quizzes)): ?>
        <ul style="list-style:none; padding:0; margin:10px 0;">
          <?php foreach ($quizzes as $q): ?>
            <li style="margin-bottom:8px;">
              <a href="start_quiz.php?quiz_id=<?= $q['quiz_id']; ?>" 
                 class="btn-primary" 
                 style="display:inline-block; text-decoration:none;">
                 <?= htmlspecialchars($q['title']); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p style="color:gray;">No quiz available for this topic yet.</p>
      <?php endif; ?>
    </div>
  </div>
</main>
</body>
</html>
