<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$id = $_GET['id'] ?? null;
if (!$id) { echo "<script>window.location='users.php';</script>"; exit; }

// Ambil Data User Lama
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) die("User tidak ditemukan");

$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    // Validasi sederhana
    if ($name === '' || $email === '') {
        $error = "Nama dan Email wajib diisi.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE user_id=?");
            $stmt->execute([$name, $email, $role, $id]);
            
            // Redirect atau tampilkan pesan sukses
            echo "<script>alert('✅ Perubahan berhasil disimpan!'); window.location='users.php';</script>";
            exit;
        } catch (PDOException $e) {
            $error = "Gagal: Email mungkin sudah digunakan.";
        }
    }
}
?>

<style>
    body { background-color: #fdfbf7; margin: 0; padding: 0; }

    /* KONTAINER UTAMA */
    .main-content {
        margin-left: 250px; /* Lebar Sidebar */
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
        
        /* --- JARAK AMAN DARI ATAS --- */
        padding-top: 150px; 
        padding-left: 40px;
        padding-right: 40px;
        padding-bottom: 50px;
        
        /* Mengatur tata letak */
        display: flex;
        flex-direction: column;
        align-items: center; /* Posisi Tengah Horizontal */
        justify-content: flex-start; 
    }

    /* WRAPPER (Pembungkus agar rapi) */
    .content-wrapper {
        width: 100%;
        max-width: 550px; 
    }

    /* HEADER HALAMAN */
    .page-header-flex {
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        margin-bottom: 25px; 
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }
    .page-title { 
        font-family: 'Times New Roman', serif; 
        font-size: 2rem; color: #2c3e50; margin: 0; 
    }
    
    /* TOMBOL KEMBALI */
    .btn-back {
        text-decoration: none; border: 1px solid #2c3e50; color: #2c3e50;
        padding: 6px 15px; border-radius: 20px; font-weight: 600; 
        transition: 0.3s; font-size: 13px;
    }
    .btn-back:hover { background: #2c3e50; color: white; }

    /* KARTU FORM */
    .card-compact {
        background: white; 
        padding: 40px; 
        border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); 
        border: 1px solid #f0ece3;
        width: 100%; 
    }

    /* FORM STYLING */
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 14px; }
    
    .form-control {
        width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px;
        box-sizing: border-box; font-size: 14px;
    }
    .form-control:focus { outline: none; border-color: #2c3e50; }

    /* TOMBOL SIMPAN */
    .btn-save {
        width: 100%;
        background: #2c3e50; color: white; border: none; padding: 12px;
        border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 15px;
        transition: 0.3s; margin-top: 10px;
    }
    .btn-save:hover { background: #1a252f; }

    /* ALERT ERROR */
    .alert-error {
        background: #f8d7da; color: #721c24; padding: 12px; 
        border-radius: 6px; margin-bottom: 20px; font-size: 14px;
        border: 1px solid #f5c6cb; text-align: center;
    }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding: 120px 20px; }
    }
</style>

<div class="main-content">

    <div class="content-wrapper">

        <div class="page-header-flex">
            <h1 class="page-title">Edit User</h1>
            <a href="users.php" class="btn-back">&larr; Kembali</a>
        </div>

        <div class="card-compact">
            
            <?php if ($error): ?>
                <div class="alert-error">⚠️ <?=$error?></div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="student" <?=$user['role']=='student'?'selected':''?>>Student</option>
                        <option value="instructor" <?=$user['role']=='instructor'?'selected':''?>>Instructor</option>
                        <option value="admin" <?=$user['role']=='admin'?'selected':''?>>Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn-save">Save Changes</button>

            </form>
        </div>
    </div>

</div>