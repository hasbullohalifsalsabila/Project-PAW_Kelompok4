<?php
require __DIR__ . '/config/db.php';
if (!is_logged_in()) header('Location: auth/login.php');

$enroll_id = intval($_GET['enroll_id'] ?? 0);
if (!$enroll_id) die('enroll missing');

$stmt = $pdo->prepare("
  SELECT cert.*, e.user_id, u.name AS student_name, c.title AS course_title
  FROM certificates cert
  JOIN enrollments e ON cert.enroll_id = e.enroll_id
  JOIN users u ON e.user_id = u.user_id
  JOIN courses c ON e.course_id = c.course_id
  WHERE cert.enroll_id = ?
");
$stmt->execute([$enroll_id]);
$cert = $stmt->fetch();
if (!$cert) die('Sertifikat tidak ditemukan');
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Sertifikat</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="container">
  <div class="certificate">
    <h1>SERTIFIKAT</h1>
    <p>Diberikan kepada</p>
    <h2><?=htmlspecialchars($cert['student_name'])?></h2>
    <p>Telah menyelesaikan kursus</p>
    <h3><?=htmlspecialchars($cert['course_title'])?></h3>
    <p>Kode Sertifikat: <?=htmlspecialchars($cert['cert_code'])?></p>
    <p>Tanggal diterbitkan: <?=htmlspecialchars($cert['issued_date'])?></p>
  </div>
</div>
</body>
</html>
