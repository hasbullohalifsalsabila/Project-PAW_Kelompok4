<?php
require __DIR__ . '/config/db.php';
if (!is_logged_in()) header('Location: auth/login.php');

$course_id = intval($_GET['course_id'] ?? 0);
if (!$course_id) die('course missing');

$user_id = current_user()['user_id'];
$en = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$en->execute([$user_id, $course_id]);
$enr = $en->fetch();
if (!$enr) die('Anda belum terdaftar di kursus ini');

$sth = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY position ASC");
$sth->execute([$course_id]);
$materials = $sth->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['material_id'])) {
    $mid = intval($_POST['material_id']);
    $chk = $pdo->prepare("SELECT id FROM materials_progress WHERE user_id = ? AND material_id = ?");
    $chk->execute([$user_id, $mid]);
    if ($chk->fetch()) {
        $pdo->prepare("UPDATE materials_progress SET is_completed=1, completed_at=NOW() WHERE user_id = ? AND material_id = ?")->execute([$user_id,$mid]);
    } else {
        $pdo->prepare("INSERT INTO materials_progress (user_id, material_id, is_completed, completed_at) VALUES (?, ?, 1, NOW())")->execute([$user_id,$mid]);
    }
    header("Location: material.php?course_id=$course_id"); exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Materi</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="container">
  <div class="topbar"><div class="brand">Materi</div><div class="nav"><a href="dashboard.php" class="btn-outline">Dashboard</a></div></div>
  <?php foreach ($materials as $m): 
    $chk = $pdo->prepare("SELECT is_completed FROM materials_progress WHERE user_id = ? AND material_id = ?");
    $chk->execute([$user_id,$m['material_id']]);
    $mp = $chk->fetch();
    $done = $mp && $mp['is_completed'];
  ?>
    <div class="card">
      <div class="course-title"><?=htmlspecialchars($m['title'])?></div>
      <div class="meta"><?=htmlspecialchars($m['type'])?></div>
      <?php if ($m['type'] === 'video' || $m['type'] === 'pdf'): ?>
        <p><a class="btn-outline" href="<?=htmlspecialchars($m['file_url'])?>" target="_blank">Buka</a></p>
      <?php else: ?>
        <p><?=nl2br(htmlspecialchars($m['file_url']))?></p>
      <?php endif; ?>

      <?php if (!$done): ?>
        <form method="post" style="margin-top:8px;">
          <input type="hidden" name="material_id" value="<?= $m['material_id'] ?>">
          <button class="btn" type="submit">Tandai Selesai</button>
        </form>
      <?php else: ?>
        <div class="badge badge-free">Selesai</div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html>
