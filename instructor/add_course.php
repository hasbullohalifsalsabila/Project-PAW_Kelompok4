<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// Cek Login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'instructor') {
    header("Location: ../auth/login.php");
    exit;
}

$iid = $_SESSION['user']['user_id'];

// --- PROSES SIMPAN DATA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari Form
    $title      = $_POST['title'];
    $short_desc = $_POST['short_desc'];
    $desc       = $_POST['description'];
    $price      = $_POST['price'];
    $level      = $_POST['level'];

    try {
        // PERBAIKAN: Menggunakan Named Parameters (:nama) agar tidak error hitungan
        $sql = "INSERT INTO courses (instructor_id, title, short_desc, description, price, level) 
                VALUES (:iid, :title, :short_desc, :description, :price, :level)";
        
        $stmt = $pdo->prepare($sql);
        
        // Eksekusi dengan mencocokkan nama parameter
        $stmt->execute([
            ':iid'         => $iid,
            ':title'       => $title,
            ':short_desc'  => $short_desc,
            ':description' => $desc,
            ':price'       => $price,
            ':level'       => $level
        ]);

        echo "<script>alert('Kelas berhasil dibuat!'); window.location.href='courses.php';</script>";
    
    } catch (PDOException $e) {
        $error = "Gagal menyimpan: " . $e->getMessage();
    }
}
?>

<style>
    .main-content { margin-left: 260px; padding: 40px; background: #fdfbf7; min-height: 100vh; }
    .card-form { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3; max-width: 800px; margin: 0 auto; }
    .form-group { margin-bottom: 20px; }
    .form-label { font-weight: 600; display: block; margin-bottom: 8px; color: #2c3e50; }
    .form-control { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    .btn-submit { background: #2c3e50; color: white; padding: 12px 25px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; width: 100%; transition: 0.3s; }
    .btn-submit:hover { background: #c49b63; }
    .page-header { text-align: center; margin-bottom: 30px; }
</style>

<div class="main-content">

    <div class="page-header">
        <h1 style="font-family: 'Playfair Display', serif; color: #2c3e50;">Buat Kelas Baru</h1>
        <a href="courses.php" style="text-decoration: none; color: #666;">&larr; Kembali ke Daftar Kursus</a>
    </div>

    <div class="card-form">
        <?php if(isset($error)) echo "<div style='color: red; margin-bottom: 15px; background: #ffebee; padding: 10px; border-radius: 5px;'>$error</div>"; ?>

        <form method="POST">
            
            <div class="form-group">
                <label class="form-label">Judul Kelas</label>
                <input type="text" name="title" class="form-control" required placeholder="Contoh: Master PHP dari Nol">
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi Singkat (Max 255 kar)</label>
                <input type="text" name="short_desc" class="form-control" required placeholder="Ringkasan isi kelas...">
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi Lengkap</label>
                <textarea name="description" class="form-control" rows="5" required placeholder="Jelaskan apa yang akan dipelajari..."></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Level</label>
                    <select name="level" class="form-control" required>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="price" class="form-control" required value="0" min="0" placeholder="0 untuk Gratis">
                </div>
            </div>

            <button type="submit" class="btn-submit">Simpan & Buat Kelas</button>

        </form>
    </div>

</div>