<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['student_id'])) {
  header("Location: student_login.php");
  exit;
}

// Ambil nama student
$student_name = $_SESSION['fullname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <header>
    <div class="header-left">
      <img src="logo.png" alt="LearnDBuddy Logo" class="logo" style="height:50px;">
      <h1>Welcome, <?php echo htmlspecialchars($student_name); ?></h1>
    </div>
    <a href="logout.php" class="logout">Logout</a>
  </header>

  <main class="dashboard">

    <!-- View Notes -->
    <div class="card">
      <i class="fas fa-book-open"></i>
      <h2>View Notes</h2>
      <p>Access all lecture notes shared by your lecturer.</p>
      <a href="view_notes.php"><button>Go</button></a>
    </div>

    <!-- Take Quizzes -->
    <div class="card">
      <i class="fas fa-question-circle"></i>
      <h2>Take Quizzes</h2>
      <p>Attempt quizzes prepared by your lecturer for your class.</p>
      <a href="take_quiz.php"><button>Start</button></a>
    </div>

    <!-- View Results -->
    <div class="card">
      <i class="fas fa-chart-line"></i>
      <h2>My Results</h2>
      <p>Track your quiz scores and performance improvement.</p>
      <a href="student_results.php"><button>View</button></a>
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
