<?php
require __DIR__ . '/../config/db.php';
if (!is_logged_in()) header('Location: ../auth/login.php');

$course_id = intval($_GET['course_id'] ?? 0);
if ($course_id) {
    header("Location: detail.php?course_id=$course_id"); exit;
}

// list all courses
$stmt = $pdo->query("SELECT c.*, u.name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.user_id ORDER BY c.created_at DESC");
$courses = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Katalog Kursus</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="container">
  <div class="topbar">
    <div class="brand">Katalog Kursus</div>
    <div class="nav"><a href="../dashboard.php" class="btn-outline">Dashboard</a></div>
  </div>

  <?php foreach ($courses as $c): ?>
    <div class="card" style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div class="course-title"><?=htmlspecialchars($c['title'])?></div>
        <div class="meta"><?=htmlspecialchars($c['short_desc'])?></div>
        <div class="meta">Instruktur: <?=htmlspecialchars($c['instructor_name'])?></div>
      </div>
      <div style="text-align:right">
        <div style="margin-bottom:6px;"><?= $c['price']==0 ? '<span class="badge badge-free">Gratis</span>' : '<span class="badge badge-paid">Rp '.number_format($c['price'],0,',','.').'</span>' ?></div>
        <a class="btn" href="detail.php?course_id=<?= $c['course_id'] ?>">Detail</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
