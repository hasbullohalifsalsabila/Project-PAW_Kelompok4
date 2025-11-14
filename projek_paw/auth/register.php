<?php
require __DIR__ . '/../config/db.php';
if (is_logged_in()) header('Location: ../dashboard.php');

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? 'student', ['student','instructor']) ? $_POST['role'] : 'student';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = "Periksa input: nama, email valid, password minimal 6 karakter.";
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email sudah terdaftar.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?, ?, ?, ?)");
            $ins->execute([$name,$email,$hash,$role]);
            header('Location: login.php?registered=1'); exit;
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Register</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="container">
  <div class="card" style="max-width:520px;margin:30px auto;">
    <h2>Daftar Akun</h2>
    <?php if ($error): ?><div style="color:#c0392b;margin-bottom:12px;"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post">
      <label>Nama lengkap</label><input name="name" required>
      <label>Email</label><input name="email" type="email" required>
      <label>Password</label><input name="password" type="password" required>
      <label>Daftar sebagai</label>
      <select name="role"><option value="student">Student</option><option value="instructor">Instructor</option></select>
      <button class="btn" type="submit">Daftar</button>
    </form>
    <p style="margin-top:10px;">Sudah punya akun? <a href="login.php">Login</a></p>
  </div>
</div>
</body>
</html>
