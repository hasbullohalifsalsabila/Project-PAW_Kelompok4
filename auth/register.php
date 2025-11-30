<?php
// Pastikan sesi dimulai di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../config/db.php';
// Diasumsikan db.php mendefinisikan $pdo dan fungsi is_logged_in()

// Pengecekan login
if (function_exists('is_logged_in') && is_logged_in()) {
    header('Location: ../dashboard.php'); // Perlu diperbaiki path redirect-nya
    exit;
}
// Jika is_logged_in() tidak ada atau error, gunakan pengecekan session sederhana:
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') {
        header("Location: ../admin/dashboard.php");
    } elseif ($role === 'instructor') {
        header("Location: ../instructor/dashboard.php");
    } else {
        header("Location: ../student/dashboard.php");
    }
    exit;
}

$error = null;

// Nilai input sebelumnya untuk mempermudah user jika terjadi error
$name_value = '';
$email_value = '';
$role_value = 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? 'student', ['student', 'instructor']) ? $_POST['role'] : 'student';

    // Simpan nilai input untuk ditampilkan kembali
    $name_value = htmlspecialchars($name);
    $email_value = htmlspecialchars($email);
    $role_value = htmlspecialchars($role);

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
            $ins->execute([$name, $email, $hash, $role]);
            
            // Registrasi berhasil, redirect ke halaman login
            header('Location: login.php?registered=1&email=' . urlencode($email)); 
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - EduCourse</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- 1. GLOBAL & RESET --- */
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #fdfbf7; /* Background Krem Hangat */
            color: #4a4a4a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            color: #2c3e50;
            font-size: 2.2rem;
            margin-bottom: 25px;
            text-align: center;
        }

        /* --- 2. CARD REGISTRASI --- */
        .auth-container {
            width: 90%;
            max-width: 450px; 
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #e8e4dd;
        }

        /* --- 3. FORM STYLE --- */
        form label {
            display: block;
            font-weight: 600;
            font-size: 0.95rem;
            color: #2c3e50;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        
        /* Input Group for Password (to position eye icon) */
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        form input,
        form select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cfcac0;
            border-radius: 10px;
            font-size: 1rem;
            background-color: #fcfaf7;
            transition: border-color 0.3s;
        }
        
        form select {
            appearance: none; /* Hide default dropdown arrow */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23666' width='18px' height='18px'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 35px;
        }
        
        /* Adjust padding for password field to make room for the icon */
        .input-group input {
             padding-right: 45px; 
             margin-bottom: 0;
        }

        form input:focus,
        form select:focus {
            border-color: #c49b63; 
            outline: none;
            box-shadow: 0 0 0 3px rgba(196, 155, 99, 0.2);
        }

        /* Eye Icon Style */
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
            font-size: 1rem;
            z-index: 10;
        }
        
        .toggle-password:hover {
            color: #2c3e50;
        }

        /* Tombol Daftar */
        .btn-register-submit {
            background-color: #2c3e50;
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.05rem;
            cursor: pointer;
            width: 100%;
            margin-top: 25px;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(44, 62, 80, 0.2);
        }

        .btn-register-submit:hover {
            background-color: #c49b63;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(196, 155, 99, 0.4);
        }
        
        /* Pesan Status */
        .status-message {
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 0.95rem;
            text-align: center;
        }

        .status-message.error {
            background-color: #fbecec; 
            color: #c0392b; 
            border: 1px solid #e74c3c;
        }
        
        /* Link Login */
        .login-link {
            text-align: center;
            font-size: 0.9rem;
            margin-top: 15px;
        }
        
        .login-link a {
            color: #c49b63;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .login-link a:hover {
            color: #2c3e50;
        }
        
        /* Logo Kecil */
        .logo-small {
            text-align: center;
            margin-bottom: 10px;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #2c3e50;
        }
        
    </style>
</head>

<body>

<div class="auth-container">
    <div class="logo-small">EduCourse <i class="fas fa-graduation-cap"></i></div>
    <h2>Daftar Akun Baru</h2>
    
    <?php if ($error): ?>
        <div class="status-message error">
            <?=htmlspecialchars($error)?>
        </div>
    <?php endif; ?>
    
    <form method="post">
        <label for="name"><i class="fas fa-user"></i> Nama Lengkap</label>
        <input name="name" id="name" type="text" value="<?= $name_value ?>" required>

        <label for="email"><i class="fas fa-envelope"></i> Email</label>
        <input name="email" id="email" type="email" value="<?= $email_value ?>" required>
        
        <label for="password"><i class="fas fa-lock"></i> Password</label>
        <div class="input-group">
            <input name="password" id="password" type="password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility()">
                <i class="fas fa-eye" id="toggleIcon"></i>
            </span>
        </div>
        
        <label for="role"><i class="fas fa-user-tag"></i> Daftar Sebagai</label>
        <select name="role" id="role">
            <option value="student" <?= $role_value == 'student' ? 'selected' : '' ?>>Student</option>
            <option value="instructor" <?= $role_value == 'instructor' ? 'selected' : '' ?>>Instructor</option>
        </select>
        
        <button class="btn-register-submit" type="submit">
            <i class="fas fa-user-plus"></i> Daftar
        </button>
    </form>
    
    <p class="login-link">
        Sudah punya akun? <a href="login.php">Login</a>
    </p>
</div>

<script>
    /**
     * Fungsi untuk mengubah visibility password
     */
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash'); // Ganti ikon menjadi mata dicoret
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye'); // Ganti ikon menjadi mata terbuka
        }
    }
</script>

</body>
</html>