<?php
require __DIR__ . '/../config/db.php';
if (!is_logged_in() || current_user()['role'] !== 'student') { header('Location: ../auth/login.php'); exit; }
$user = current_user();
$user_id = $user['user_id'];

// fetch enrollments
$stmt = $pdo->prepare("SELECT e.*, c.title, c.course_id, c.price FROM enrollments e JOIN courses c ON e.course_id = c.course_id WHERE e.user_id = ? ORDER BY e.enrolled_at DESC");
$stmt->execute([$user_id]);
$myCourses = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Student Dashboard</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="container">
  <div class="topbar">
    <div class="brand">Student Dashboard</div>
    <div class="nav"><a href="../dashboard.php" class="btn-outline">Home</a> <a href="../auth/logout.php" class="btn-outline">Logout</a></div>
  </div>

  <div class="card">
    <h2>Halo, <?=htmlspecialchars($user['name'])?></h2>
    <p class="meta">Kursus yang anda ikuti:</p>
    <?php if (empty($myCourses)): ?>
      <p>Tidak ada enroll. <a href="../courses/index.php" class="btn">Jelajah Kursus</a></p>
    <?php else: ?>
      <?php foreach ($myCourses as $mc): ?>
        <div class="card" style="margin-bottom:10px;">
          <div class="course-title"><?=htmlspecialchars($mc['title'])?></div>
          <div class="meta">Status: <?=htmlspecialchars($mc['status'])?> | Harga: <?= $mc['price']==0 ? 'Gratis' : 'Rp '.number_format($mc['price'],0,',','.') ?></div>
          <p style="margin-top:8px;"><a class="btn" href="../material.php?course_id=<?= $mc['course_id'] ?>">Akses Materi</a> <a class="btn-outline" href="../quiz.php?course_id=<?= $mc['course_id'] ?>">Kuis</a></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
