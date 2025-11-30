<?php
// filename: admin/course_add.php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$instructor_id = $_GET['instructor_id'] ?? null;

if (!$instructor_id) {
    // Gunakan peringatan kustom daripada alert() untuk pengalaman yang lebih baik jika memungkinkan
    // Dalam konteks PHP yang sudah ada, kita pertahankan alert()
    echo "<script>alert('Instruktur tidak dipilih!'); window.location='courses.php';</script>";
    exit;
}

// Ambil nama instruktur
$stmt_ins = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt_ins->execute([$instructor_id]);
$instructor = $stmt_ins->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc  = $_POST['description'];
    $price = $_POST['price'];
    // BARU: Ambil data level kelas
    $level = $_POST['level'] ?? 'Beginner'; // Default ke Beginner jika tidak ada

    // Simpan Kelas Baru
    // CATATAN: QUERY INI MENGASUMSIKAN ANDA SUDAH MENAMBAHKAN KOLOM 'level' DI TABEL 'courses'
    $stmt = $pdo->prepare(
        "INSERT INTO courses (instructor_id, title, description, price, level) 
         VALUES (?, ?, ?, ?, ?)"
    );
    
    // WAJIB: kirim 5 value (termasuk $level)
    if ($stmt->execute([$instructor_id, $title, $desc, $price, $level])) {

        echo "<script>
                  alert('✅ Kelas berhasil dibuat!'); 
                  window.location='courses.php';
              </script>";
        exit;
    } else {
        // Tambahkan penanganan kesalahan jika query gagal
        echo "<script>alert('❌ Gagal menyimpan kelas ke database.');</script>";
    }
}

?>

<style>
    body { background-color: #fdfbf7; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

    /* KONTAINER UTAMA */
    .main-content {
        margin-left: 250px; /* Lebar Sidebar */
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
        
        /* Mengatur posisi ke tengah */
        padding: 40px;
        padding-top: 80px; 
        display: flex;
        flex-direction: column;
        align-items: center; 
    }

    /* HEADER HALAMAN */
    .page-header-flex {
        width: 100%;
        max-width: 600px; /* Samakan lebar dengan kartu */
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }
    .page-title { 
        font-family: 'Times New Roman', serif; 
        font-size: 1.8rem; color: #2c3e50; margin: 0; 
    }
    
    .btn-back {
        text-decoration: none; border: 1px solid #555; color: #555;
        padding: 6px 15px; border-radius: 20px; font-weight: 600; 
        transition: 0.3s; font-size: 13px;
    }
    .btn-back:hover { background: #555; color: white; }

    /* KARTU FORMULIR */
    .card-form {
        background: white;
        padding: 40px;
        border-radius: 12px;
        width: 100%;
        max-width: 600px; /* Lebar ideal */
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: 1px solid #f0ece3;
        box-sizing: border-box;
    }

    /* INSTRUKTUR INFO */
    .instructor-info {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        border-left: 4px solid #2c3e50;
        color: #555;
        font-size: 14px;
    }
    .instructor-info strong { color: #2c3e50; font-size: 16px; }

    /* FORM STYLING */
    .form-group { margin-bottom: 20px; }
    .form-label { 
        display: block; margin-bottom: 8px; 
        font-weight: bold; font-size: 14px; color: #555; 
    }
    
    .form-control {
        width: 100%; padding: 12px; 
        border: 1px solid #ccc; border-radius: 6px;
        box-sizing: border-box; font-size: 14px;
        font-family: sans-serif;
        appearance: none; /* Hilangkan default style untuk select */
    }
    .form-control:focus { outline: none; border-color: #2c3e50; }
    
    /* Styling khusus untuk SELECT */
    .select-wrapper {
        position: relative;
    }
    .select-wrapper::after {
        content: '▼';
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        pointer-events: none;
        color: #888;
        font-size: 10px;
    }

    textarea.form-control { resize: vertical; }

    /* TOMBOL SIMPAN */
    .btn-save {
        width: 100%; padding: 14px; 
        background: #2c3e50; color: white; 
        border: none; border-radius: 6px; 
        cursor: pointer; font-weight: bold; font-size: 15px; 
        margin-top: 10px; transition: 0.3s;
    }
    .btn-save:hover { background: #1a252f; }

    /* Responsif HP */
    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding: 80px 20px; }
    }
</style>

<div class="main-content">
    
    <div class="page-header-flex">
        <h1 class="page-title">Tambah Kelas Baru</h1>
        <a href="courses.php" class="btn-back">&larr; Kembali</a>
    </div>

    <div class="card-form">
        
        <div class="instructor-info">
            Instruktur Pengajar:<br>
            <strong><?= htmlspecialchars($instructor['name']) ?></strong>
        </div>

        <form method="POST">
            
            <div class="form-group">
                <label class="form-label">Judul Kelas (Course Title)</label>
                <input type="text" name="title" class="form-control" required placeholder="Contoh: Pemrograman Web Dasar">
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi Kelas</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Jelaskan secara singkat apa yang akan dipelajari..."></textarea>
            </div>
            
            <!-- BARU: DROPDOWN LEVEL KELAS -->
            <div class="form-group">
                <label class="form-label">Level Kelas (Course Level)</label>
                <div class="select-wrapper">
                    <select name="level" class="form-control" required>
                        <option value="Beginner">Beginner (Pemula)</option>
                        <option value="Intermediate">Intermediate (Menengah)</option>
                        <option value="Professional">Professional (Profesional)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Harga Kelas (IDR)</label>
                <input type="number" name="price" class="form-control" required placeholder="Contoh: 150000">
            </div>


            <button type="submit" class="btn-save">Simpan & Lanjut Kelola Materi &rarr;</button>
            
        </form>
    </div>

</div>