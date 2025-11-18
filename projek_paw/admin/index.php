<?php
require __DIR__ . '/../config/db.php';
if (!is_logged_in() || current_user()['role'] !== 'admin') { header('Location: ../auth/login.php'); exit; }
$user = current_user();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Admin Dashboard</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="container">
  <div class="topbar">
    <div class="brand">Admin Dashboard</div>
    <div class="nav"><a href="../dashboard.php" class="btn-outline">Home</a> <a href="../auth/logout.php" class="btn-outline">Logout</a></div>
  </div>

  <div class="card">
    <h2>Halo, <?=htmlspecialchars($user['name'])?></h2>
    <p class="meta">Fitur admin: manage users, courses, categories (prototype).</p>
    <p>Untuk final project, kamu bisa tambahkan CRUD di sini.</p>
  </div>
</div>
</body>
</html>
