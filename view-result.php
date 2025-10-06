<?php
$conn = new mysqli("localhost", "root", "", "learndbuddy");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$sql = "SELECT student_name, avg_percentage, latest_percentage, label FROM ai_results";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AI Student Performance - LearnDBuddy</title>
  <link rel="stylesheet" href="style.css"> <!-- pakai css sama -->
</head>
<body>
  <header>
    <h1>Student Performance</h1>
    <a href="dashboard.php" class="logout">Logout</a>
  </header>

  <main class="table-container">
    <table>
      <thead>
        <tr>
          <th>Student</th>
          <th>Average %</th>
          <th>Latest %</th>
          <th>Performance</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()) { ?>
            <tr>
              <td><?= htmlspecialchars($row['student_name']); ?></td>
              <td><?= $row['avg_percentage']; ?>%</td>
              <td><?= $row['latest_percentage']; ?>%</td>
              <td><?= htmlspecialchars($row['label']); ?></td>
            </tr>
          <?php } ?>
        <?php else: ?>
          <tr><td colspan="4">No results found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Back button -->
    <div style="text-align: center; margin-top: 20px;">
      <a href="dashboard.php">
        <button type="button">Back</button>
      </a>
    </div>
  </main>
</body>
</html>
<?php $conn->close(); ?>
