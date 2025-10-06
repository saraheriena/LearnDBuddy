<?php
session_start();
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
  <!-- Font Awesome untuk icon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <header>
    <div class="header-left">
      <img src="logo.png" alt="LearnDBuddy Logo" class="logo" style="height:50px; width:auto;">

     <h1 style="font-size:20px;">Welcome, <?php echo $_SESSION['fullname']; ?></h1>
    </div>
    <a href="logout.php" class="logout">Logout</a>
  </header>

  <main class="dashboard">
    <div class="card">
      <i class="fas fa-edit"></i>
      <h2>Edit Quiz</h2>
      <p>Create, update, or delete quizzes for students.</p>
      <a href="edit-quiz.php"><button>Edit Quiz</button></a>
    </div>

    <div class="card">
      <i class="fas fa-chart-bar"></i>
      <h2>View Student Results</h2>
      <p>Check performance and feedback of your students.</p>
      <a href="view-result.php"><button>View Results</button></a>
    </div>
  </main>
</body>
</html>
