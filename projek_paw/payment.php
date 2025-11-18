<?php
require __DIR__ . '/config/db.php';
if (!is_logged_in()) header('Location: auth/login.php');

$enroll_id = intval($_GET['enroll_id'] ?? 0);
if (!$enroll_id) die('enroll_id missing');

$stmt = $pdo->prepare("SELECT e.*, c.title, c.price, c.course_id FROM enrollments e JOIN courses c ON e.course_id = c.course_id WHERE e.enroll_id = ?");
$stmt->execute([$enroll_id]);
$en = $stmt->fetch();
if (!$en) die('enroll not found');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'] ?? 'bank';
    $ins = $pdo->prepare("INSERT INTO payments (enroll_id, amount, method, status, paid_at) VALUES (?, ?, ?, 'success', NOW())");
    $ins->execute([$enroll_id, $en['price'], $method]);
    $payment_id = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO payment_history (payment_id, user_id, action) VALUES (?, ?, ?)")->execute([$payment_id, current_user()['user_id'], 'paid']);
    header("Location: dashboard.php"); exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Pembayaran</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="container">
  <div class="topbar"><div class="brand">Pembayaran</div><div class="nav"><a href="dashboard.php" class="btn-outline">Dashboard</a></div></div>
  <div class="card">
    <h3><?=htmlspecialchars($en['title'])?></h3>
    <p>Jumlah: <strong>Rp <?=number_format($en['price'],0,',','.')?></strong></p>
    <form method="post">
      <label>Pilih Metode</label>
      <select name="method"><option value="bank">Transfer Bank</option><option value="ewallet">E-Wallet</option><option value="creditcard">Kartu Kredit</option></select>
      <button class="btn" type="submit">Bayar (Mock)</button>
    </form>
  </div>
</div>
</body>
</html>
