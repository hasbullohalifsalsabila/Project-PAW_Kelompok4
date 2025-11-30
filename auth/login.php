<?php
// Pastikan sesi dimulai di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../config/db.php';
// Diasumsikan db.php mendefinisikan $pdo, $secretkey, dan $sitekey

$error = null;

// --- PERBAIKAN LOGIKA REDIRECT PENCEGAH LOOP ---
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
// ------------------------------------------


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response_code = $_POST['g-recaptcha-response'];

    // Verify Google Captcha
    $verify = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret=$secretkey&response=$response_code"
    );
    $data = json_decode($verify);

    if (!$data->success) {
        $error = "Captcha tidak valid.";
    } else {

        // Continue login normally
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT user_id,name,email,password,role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Email tidak ditemukan.";
        } elseif (!password_verify($password, $user['password'])) {
            $error = "Password salah.";
        } else {
            // SUCCESS LOGIN
            $_SESSION['user'] = [
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user['role'] === 'instructor') {
                header("Location: ../instructor/dashboard.php");
            } else {
                header("Location: ../student/dashboard.php");
            }
            exit;
        }
    }
}

$registered = isset($_GET['registered']);

// Nilai email untuk mempermudah pengisian kembali jika error
$email_value = htmlspecialchars($_POST['email'] ?? ($_GET['email'] ?? ''));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduCourse</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

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

        /* --- 2. CARD LOGIN --- */
        .auth-container {
            width: 90%;
            max-width: 450px; /* Lebar maksimum yang ideal untuk form */
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

        /* Input Wrapper for Password Field (to position eye icon) */
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        form input[type="email"],
        form input[type="password"],
        form input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cfcac0;
            border-radius: 10px;
            font-size: 1rem;
            background-color: #fcfaf7;
            transition: border-color 0.3s;
        }

        /* Adjust padding for password field to make room for the icon */
        .input-group input {
             padding-right: 45px; 
             margin-bottom: 0;
        }

        form input:focus {
            border-color: #c49b63; /* Emas saat fokus */
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
        }
        
        .toggle-password:hover {
            color: #2c3e50;
        }


        /* Tombol Login */
        .btn-login-submit {
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

        .btn-login-submit:hover {
            background-color: #c49b63;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(196, 155, 99, 0.4);
        }
        
        /* Captcha div */
        .g-recaptcha {
            margin-top: 20px;
            transform-origin: 0 0;
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

        .status-message.success {
            background-color: #e8f5e9; /* Hijau muda */
            color: #388e3c; /* Hijau tua */
            border: 1px solid #a5d6a7;
        }

        .status-message.error {
            background-color: #fbecec; /* Merah muda */
            color: #c0392b; /* Merah tua */
            border: 1px solid #e74c3c;
        }
        
        /* Link Daftar */
        .register-link {
            text-align: center;
            font-size: 0.9rem;
            margin-top: 15px;
        }
        
        .register-link a {
            color: #c49b63;
            font-weight: 600;
            transition: color 0.3s;
        }
        
        .register-link a:hover {
            color: #2c3e50;
        }
        
        /* Logo Kecil (Opsional) */
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
    <h2>Selamat Datang Kembali</h2>
    
    <?php if ($registered): ?>
        <div class="status-message success">
            Registrasi berhasil. Silakan login.
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="status-message error">
            <?=htmlspecialchars($error)?>
        </div>
    <?php endif; ?>
    
    <form method="post">
        <label for="email"><i class="fas fa-envelope"></i> Email</label>
        <input name="email" id="email" type="email" value="<?= $email_value ?>" required>
        
        <label for="password"><i class="fas fa-lock"></i> Password</label>
        <div class="input-group">
            <input name="password" id="password" type="password" required>
            <span class="toggle-password" onclick="togglePasswordVisibility()">
                <i class="fas fa-eye" id="toggleIcon"></i>
            </span>
        </div>
        
        <div class="g-recaptcha" data-sitekey="<?=$sitekey?>"></div>
        
        <button class="btn-login-submit" type="submit">
            <i class="fas fa-sign-in-alt"></i> Login
        </button>
    </form>
    
    <p class="register-link">
        Belum punya akun? <a href="register.php">Daftar Sekarang</a>
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