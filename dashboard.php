<?php
session_start();
include 'db.php';


if (!isset($_SESSION['lecturer_id'])) {
  header("Location: index.php");
  exit;
}

$lecturer_name = $_SESSION['lecturer_name']; // 🔹 tambah line ni
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Lecturer Dashboard - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f7fa;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    header {
      background: #1e90ff;
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .logout {
      background: #fff;
      color: #1e90ff;
      padding: 8px 15px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }

    /* CENTERED DASHBOARD GRID */
    main.dashboard {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 25px;
      justify-content: center;
      align-content: center;
      place-items: center;
      flex-grow: 1;
      max-width: 1000px;
      margin: 40px auto;
      width: 100%;
    }

    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 25px;
      width: 90%;
      max-width: 400px;
      text-align: center;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
      transform: scale(1.02);
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    /* FIX: ICON NO LONGER “BOUNCY” */
    .card i {
      font-size: 45px;
      color: #1e90ff;
      margin-bottom: 15px;
      transition: none !important;
      transform: none !important;
    }

    .card h2 {
      font-size: 20px;
      margin: 10px 0 5px;
      color: #333;
    }

    .card p {
      color: #666;
      font-size: 14px;
      margin-bottom: 15px;
    }

    .card button {
      background: #1e90ff;
      color: #fff;
      border: none;
      padding: 10px 18px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }

    .card button:hover {
      background: #187bcd;
    }

    @media (max-width: 768px) {
      main.dashboard {
        grid-template-columns: 1fr;
      }
    }

    /* Toast message */
    .toast {
      visibility: hidden;
      min-width: 250px;
      background: #333;
      color: #fff;
      text-align: center;
      border-radius: 4px;
      padding: 12px;
      position: fixed;
      left: 50%;
      bottom: 30px;
      transform: translateX(-50%);
      font-size: 14px;
    }

    .toast.show {
      visibility: visible;
      animation: fadein 0.5s, fadeout 0.5s 3.5s;
    }

    @keyframes fadein {
      from { bottom: 10px; opacity: 0; }
      to { bottom: 30px; opacity: 1; }
    }

    @keyframes fadeout {
      from { bottom: 30px; opacity: 1; }
      to { bottom: 10px; opacity: 0; }
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <img src="logo.png" alt="LearnDBuddy Logo" style="height:50px;">
      <h1>Welcome, <?php echo htmlspecialchars($lecturer_name); ?></h1>
    </div>
    <a href="logout.php" class="logout">Logout</a>
  </header>

  <main class="dashboard">
    <div class="card">
      <i class="fas fa-chalkboard-teacher"></i>
      <h2>Manage Classes</h2>
      <p>Create, edit, and delete student classes.</p>
      <a href="manage_class.php"><button>Go</button></a>
    </div>

    <div class="card">
      <i class="fas fa-file-alt"></i>
      <h2>Manage Notes</h2>
      <p>Upload or delete PDF/video notes for students.</p>
      <a href="edit_notes.php"><button>Go</button></a>
    </div>

    <div class="card">
      <i class="fas fa-edit"></i>
      <h2>Edit Quizzes</h2>
      <p>Create, update, or delete quizzes for your classes.</p>
      <a href="edit-quiz.php"><button>Go</button></a>
    </div>

    <div class="card">
      <i class="fas fa-chart-bar"></i>
      <h2>View Student Results</h2>
      <p>View performance and quiz analytics by class.</p>
      <a href="view-result.php"><button>Go</button></a>
    </div>
  </main>

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
