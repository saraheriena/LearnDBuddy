<?php
session_start();
include 'db.php';

if (!isset($_SESSION['lecturer_id'])) {
  header("Location: index.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lecturer Dashboard - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <!-- Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <header>
    <div class="header-left">
      <img src="logo.png" alt="LearnDBuddy Logo" class="logo" style="height:50px;">
      <h1>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></h1>
    </div>
    <a href="logout.php" class="logout">Logout</a>
  </header>

  <main class="dashboard">

    <!-- Manage Classes -->
    <div class="card">
      <i class="fas fa-chalkboard-teacher"></i>
      <h2>Manage Classes</h2>
      <p>Create, edit, and delete student classes.</p>
      <a href="manage_class.php"><button>Go</button></a>
    </div>

    <!-- Manage Notes -->
    <div class="card">
      <i class="fas fa-file-alt"></i>
      <h2>Manage Notes</h2>
      <p>Upload or delete PDF/video notes for students.</p>
      <a href="edit_notes.php"><button>Go</button></a>
    </div>

    <!-- Edit Quiz -->
    <div class="card">
      <i class="fas fa-edit"></i>
      <h2>Edit Quizzes</h2>
      <p>Create, update, or delete quizzes for your classes.</p>
      <a href="edit-quiz.php"><button>Go</button></a>
    </div>

    <!-- View Student Results -->
    <div class="card">
      <i class="fas fa-chart-bar"></i>
      <h2>View Student Results</h2>
      <p>View performance and quiz analytics by class.</p>
      <a href="view-result.php"><button>Go</button></a>
    </div>

  </main>

  <!-- Natural Toast Notification -->
  <?php if (isset($_GET['msg'])): ?>
    <div id="toast" class="toast show">
      <?= htmlspecialchars($_GET['msg']); ?>
    </div>
    <script>
      setTimeout(() => document.getElementById('toast').classList.remove('show'), 4000);
    </script>
  <?php endif; ?>
</body>
</html>
