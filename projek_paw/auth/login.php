<?php
require __DIR__ . '/../config/db.php';
if (is_logged_in()) header('Location: ../dashboard.php');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT user_id,name,email,password,role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password'])) {
        $error = "Email atau password salah.";
    } else {
        $_SESSION['user'] = [
            'user_id' => $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        header('Location: ../dashboard.php'); exit;
    }
}
$registered = isset($_GET['registered']);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="container">
  <div class="card" style="max-width:520px;margin:30px auto;">
    <h2>Login</h2>
    <?php if ($registered): ?><div style="color:#16a085;margin-bottom:12px;">Registrasi berhasil. Silakan login.</div><?php endif; ?>
    <?php if ($error): ?><div style="color:#c0392b;margin-bottom:12px;"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post">
      <label>Email</label><input name="email" type="email" required>
      <label>Password</label><input name="password" type="password" required>
      <button class="btn" type="submit">Login</button>
    </form>
    <p style="margin-top:10px;">Belum punya akun? <a href="register.php">Daftar</a></p>
  </div>
</div>
</body>
</html>
