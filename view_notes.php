<?php
session_start();
include 'db.php';

if (!isset($_SESSION['student_id'])) {
  header("Location: index.php");
  exit;
}

$class_id = $_SESSION['class_id'] ?? null;

// === Konfigurasi URL projek & folder uploads (SESUAIKAN) ===
$projectBaseUrl = '/learnDBuddy';           // <-- tukar jika folder projek lain
$uploadsUrl     = $projectBaseUrl . '/uploads/';

// === Baca penapis topik dari URL: ?topic=1..5 atau all ===
$topicParam   = isset($_GET['topic']) ? strtolower(trim($_GET['topic'])) : 'all';
$validTopics  = ['1', '2', '3', '4', '5', 'all'];
if (!in_array($topicParam, $validTopics, true)) $topicParam = 'all';

// === Semak jika jadual ada lajur 'topic' ===
$hasTopicColumn = false;
$check = $conn->query("SHOW COLUMNS FROM notes LIKE 'topic'");
if ($check && $check->num_rows > 0) $hasTopicColumn = true;

// === Build query ikut kewujudan lajur topic & penapis ===
if ($hasTopicColumn && $topicParam !== 'all') {
  $stmt = $conn->prepare("
    SELECT note_id, title, file_path, topic
    FROM notes
    WHERE class_id = ? AND topic = ?
    ORDER BY note_id DESC
  ");
  $t = (int)$topicParam;
  $stmt->bind_param("ii", $class_id, $t);
} else {
  $stmt = $conn->prepare("
    SELECT note_id, title, file_path
    FROM notes
    WHERE class_id = ?
    ORDER BY note_id DESC
  ");
  $stmt->bind_param("i", $class_id);
}

$stmt->execute();
$result = $stmt->get_result();

// === Fallback (jika tiada lajur topic): detect dari title "Topic/Topik N" ===
function detect_topic_from_title($title)
{
  if (preg_match('/\b(?:topic|topik)\s*(\d)\b/i', $title, $m)) {
    $n = (int)$m[1];
    if ($n >= 1 && $n <= 5) return $n;
  }
  return null;
}

// === Ambil semua baris ke array & tapis manual jika perlu ===
$rows = $result->fetch_all(MYSQLI_ASSOC);
if (!$hasTopicColumn && $topicParam !== 'all') {
  $rows = array_values(array_filter($rows, function ($r) use ($topicParam) {
    $t = detect_topic_from_title($r['title'] ?? '');
    return $t !== null && (string)$t === $topicParam;
  }));
}

// === Ayat untuk dipaparkan dalam butang topik (edit ikut suka) ===
$topicDescriptions = [
  1 => "Ringkasan / ayat untuk Topik 1. Contoh: Pengenalan konsep asas...",
  2 => "Ringkasan / ayat untuk Topik 2. Contoh: Objektif pembelajaran...",
  3 => "Ringkasan / ayat untuk Topik 3. Contoh: Nota penting & kata kunci...",
  4 => "Ringkasan / ayat untuk Topik 4. Contoh: Aktiviti & pautan rujukan...",
  5 => "Ringkasan / ayat untuk Topik 5. Contoh: Ulangkaji & latihan..."
];

// Utiliti potong ayat untuk muat dalam butang
function short_snippet($text, $len = 110)
{
  $plain = trim(preg_replace('/\s+/', ' ', strip_tags($text ?? '')));
  if (mb_strlen($plain) <= $len) return $plain;
  return mb_substr($plain, 0, $len - 1) . '…';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>View Notes - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Gaya untuk SENARAI BUTANG PANJANG + grid kad -->
  <style>
    /* Senarai butang panjang (vertical list) */
    .topics-list {
      max-width: 1100px;
      margin: 12px auto 8px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding: 0 10px;
    }

    .topic-item {
      display: block;
      background: #fff;
      border: 1px solid #b9dafd;
      border-radius: 14px;
      padding: 14px 16px;
      text-decoration: none;
      color: inherit;
      box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
      transition: transform .2s, box-shadow .2s, background .2s, border-color .2s;
    }

    .topic-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(0, 0, 0, .1);
    }

    .topic-item.active {
      background: #e9f3ff;
      border-color: #1e90ff;
      box-shadow: 0 6px 16px rgba(30, 144, 255, .15);
    }

    .topic-row {
      display: flex;
      gap: 10px;
      align-items: flex-start;
    }

    .topic-icon {
      flex: 0 0 auto;
      font-size: 22px;
      color: #1e90ff;
      margin-top: 2px;
    }

    .topic-text {
      flex: 1;
      min-width: 0;
    }

    .topic-title {
      margin: 0 0 6px;
      color: #1e90ff;
      font-weight: 800;
      font-size: 16px;
      line-height: 1.2;
    }

    .topic-desc {
      margin: 0;
      color: #444;
      font-size: 14px;
      line-height: 1.5;
    }

    /* Grid nota (kad) */
    .notes-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 24px;
      width: 100%;
      max-width: 1100px;
      margin: 12px auto 30px;
      padding: 0 10px;
    }

    .no-notes {
      text-align: center;
      color: #666;
      margin: 0 0 20px;
    }
  </style>
</head>

<body>
  <header>
    <div class="header-left">
      <img src="logo.png" alt="LearnDBuddy Logo" class="logo" style="height:50px;">
      <h1>Class Notes</h1>
    </div>
    <a href="student-dashboard.php" class="logout">Back</a>
  </header>

  <main class="dashboard" style="flex-direction:column;align-items:stretch;gap:16px;padding-top:16px;">

    <!-- SENARAI BUTANG PANJANG (Topic 1–5 → page khas) -->
    <nav class="topics-list">
      <?php for ($t = 1; $t <= 5; $t++): ?>
        <a class="topic-item" href="topic<?= $t ?>.php">
          <div class="topic-row">
            <i class="fas fa-book topic-icon"></i>
            <div class="topic-text">
              <h3 class="topic-title">Topic <?= $t ?></h3>
              <p class="topic-desc">
                <?= htmlspecialchars(short_snippet($topicDescriptions[$t] ?? '', 120), ENT_QUOTES, 'UTF-8') ?>
              </p>
            </div>
          </div>
        </a>
      <?php endfor; ?>
    </nav>
    <!-- PAPARAN GRID NOTA (All / ?topic=all) -->
    <?php if (count($rows) > 0): ?>
      <section class="notes-grid">
        <?php foreach ($rows as $row): ?>
          <?php
          $title   = $row['title'] ?? 'Untitled';
          $name    = $row['file_path'] ?? '';
          $fileUrl = $uploadsUrl . rawurlencode(basename($name));
          $isPdf   = preg_match('/\.pdf$/i', $name);

          ?>
          <div class="card">
            <i class="fas fa-file-alt"></i>
            <h2><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h2>

            <?php if ($isPdf): ?>
              <embed src="<?= htmlspecialchars($fileUrl, ENT_QUOTES, 'UTF-8'); ?>"
                type="application/pdf" width="100%" height="300">
            <?php else: ?>
              <video controls width="100%">
                <source src="<?= htmlspecialchars($fileUrl, ENT_QUOTES, 'UTF-8'); ?>">
                Your browser does not support the video tag.
              </video>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </section>
    <?php else: ?>
      <p class="no-notes">No notes for this selection yet.</p>
    <?php endif; ?>
  </main>
</body>

</html>