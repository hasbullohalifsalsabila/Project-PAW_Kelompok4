<?php
require __DIR__ . '/../config/db.php';
if (!is_logged_in() || current_user()['role'] !== 'instructor') { header('Location: ../auth/login.php'); exit; }
$user = current_user();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Instructor Dashboard</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="container">
  <div class="topbar">
    <div class="brand">Instructor Panel</div>
    <div class="nav"><a href="../dashboard.php" class="btn-outline">Home</a> <a href="../auth/logout.php" class="btn-outline">Logout</a></div>
  </div>

  <div class="card">
    <h2>Halo, <?=htmlspecialchars($user['name'])?></h2>
    <p class="meta">Di sini instruktur bisa manage courses, materials, kuis.</p>
  </div>
</div>
</body>
</html>
