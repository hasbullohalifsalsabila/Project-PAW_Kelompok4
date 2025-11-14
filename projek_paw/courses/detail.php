<?php
require __DIR__ . '/../config/db.php';
if (!is_logged_in()) header('Location: ../auth/login.php');

$course_id = intval($_GET['course_id'] ?? 0);
if (!$course_id) die('course_id missing');

$stmt = $pdo->prepare("SELECT c.*, u.name AS instructor_name FROM courses c JOIN users u ON c.instructor_id = u.user_id WHERE c.course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();
if (!$course) die('Course not found');

$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $user_id = current_user()['user_id'];
    // check existing
    $check = $pdo->prepare("SELECT enroll_id FROM enrollments WHERE user_id = ? AND course_id = ?");
    $check->execute([$user_id, $course_id]);
    if ($check->fetch()) {
        $msg = "Anda sudah terdaftar di kursus ini.";
    } else {
        $ins = $pdo->prepare("INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)");
        $ins->execute([$user_id, $course_id]);
        $enroll_id = $pdo->lastInsertId();
        if (floatval($course['price']) > 0) {
            header("Location: ../payment.php?enroll_id=$enroll_id"); exit;
        } else {
            $msg = "Enroll berhasil. Akses materi di halaman dashboard.";
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title><?=htmlspecialchars($course['title'])?></title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="container">
  <div class="topbar">
    <div class="brand"><?=htmlspecialchars($course['title'])?></div>
    <div class="nav"><a href="index.php" class="btn-outline">Kembali</a></div>
  </div>

  <div class="card">
    <div class="course-title"><?=htmlspecialchars($course['title'])?></div>
    <div class="meta">Instruktur: <?=htmlspecialchars($course['instructor_name'])?> | Level: <?=htmlspecialchars($course['level'])?></div>
    <p style="margin-top:12px;"><?=nl2br(htmlspecialchars($course['description']))?></p>
    <p style="margin-top:12px;">Harga: <?= $course['price']==0 ? 'Gratis' : 'Rp '.number_format($course['price'],0,',','.') ?></p>

    <?php if ($msg): ?><div style="color:#16a085;margin-bottom:10px;"><?=htmlspecialchars($msg)?></div><?php endif; ?>

    <form method="post">
      <button class="btn" name="enroll" type="submit">Daftar / Enroll</button>
    </form>
  </div>
</div>
</body>
</html>
