<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// 1. Validasi ID Kursus
if (!isset($_GET['id'])) {
    echo "<script>window.location='courses.php';</script>";
    exit;
}
$course_id = $_GET['id'];

// 2. Ambil Data Kursus
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    echo "<script>alert('Kursus tidak ditemukan!'); window.location='courses.php';</script>";
    exit;
}

// ================= LOGIKA SIMPAN & HAPUS =================

// A. Upload Materi
if (isset($_POST['upload_material'])) {
    $target_dir = "../uploads/materials/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true); 
    
    $file_name = time() . '_' . basename($_FILES["file_content"]["name"]);
    if (move_uploaded_file($_FILES["file_content"]["tmp_name"], $target_dir . $file_name)) {
        $pdo->prepare("INSERT INTO materials (course_id, title, file_path, type) VALUES (?, ?, ?, 'pdf')")
            ->execute([$course_id, $_POST['material_title'], $file_name]);
        $msg_materi = "✅ Materi berhasil diupload!";
    }
}

// B. Tambah Latihan Mandiri
if (isset($_POST['add_exercise'])) {
    $ex_title = "[Latihan] " . $_POST['exercise_title'];
    $pdo->prepare("INSERT INTO quizzes (course_id, title) VALUES (?, ?)")->execute([$course_id, $ex_title]);
    $msg_latihan = "✅ Latihan Mandiri dibuat!";
}

// C. Hapus Item
if (isset($_GET['delete_material'])) {
    $id = $_GET['delete_material'];
    $pdo->prepare("DELETE FROM materials WHERE material_id = ?")->execute([$id]);
    echo "<script>window.location='manage_course.php?id=$course_id';</script>";
}
if (isset($_GET['delete_quiz'])) {
    $id = $_GET['delete_quiz'];
    $pdo->prepare("DELETE FROM quizzes WHERE quiz_id = ?")->execute([$id]);
    echo "<script>window.location='manage_course.php?id=$course_id';</script>";
}
?>

<style>
    /* Background Utama */
    body { background-color: #fdfbf7; margin: 0; padding: 0; box-sizing: border-box; }

    /* KONTAINER UTAMA */
    .main-content {
        margin-left: 250px;    
        padding-top: 100px;    
        padding-left: 30px;
        padding-right: 30px;
        padding-bottom: 30px;
        min-height: 100vh;
        width: calc(100% - 250px);
        /* Pastikan background container juga krem/transparan */
        background-color: #fdfbf7; 
    }

    /* HEADER HALAMAN */
    .page-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0dbd0; /* Garis pemisah sedikit lebih gelap */
        padding-bottom: 15px;
    }
    .page-title { 
        margin: 0; 
        font-family: 'Times New Roman', serif; /* Font Serif sesuai tema */
        font-size: 28px; 
        color: #2c3e50; 
    }
    .btn-back {
        text-decoration: none; border: 1px solid #555; color: #555;
        padding: 5px 15px; border-radius: 15px; font-size: 13px;
        transition: 0.3s; font-weight: bold;
    }
    .btn-back:hover { background: #555; color: white; }

    /* GRID 2 KARTU */
    .grid-compact {
        display: grid;
        grid-template-columns: 1fr 1fr; 
        gap: 20px;
        margin-bottom: 30px;
        max-width: 900px; 
    }

    /* DESAIN KARTU PUTIH */
    .card-small {
        background: white;
        padding: 20px; 
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        border: 1px solid #f0ece3;
    }
    
    .card-head { font-size: 16px; font-weight: bold; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .text-blue { color: #3498db; }
    .text-green { color: #27ae60; }

    /* FORM INPUT */
    .form-group { margin-bottom: 12px; }
    .label-small { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #555; }
    .input-small {
        width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; font-size: 13px;
        box-sizing: border-box; 
    }
    
    .btn-action {
        width: 100%; padding: 10px; border: none; border-radius: 4px;
        font-size: 13px; font-weight: bold; color: white; cursor: pointer;
        margin-top: 10px;
    }
    .btn-blue { background: #3498db; }
    .btn-green { background: #27ae60; }

    /* TABEL BAWAH */
    .table-box {
        background: white; padding: 20px; border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        border: 1px solid #f0ece3;
        max-width: 900px; 
    }
    .table-simple { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .table-simple th { text-align: left; padding: 10px; border-bottom: 2px solid #eee; font-size: 13px; background: #f9f9f9; color: #444; }
    .table-simple td { padding: 10px; border-bottom: 1px solid #f0f0f0; font-size: 13px; color: #333; }

    /* Responsif HP */
    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding-top: 80px; }
        .grid-compact { grid-template-columns: 1fr; }
    }
</style>

<div class="main-content">

    <div class="page-header-row">
        <div>
            <h2 class="page-title">Manage Content</h2>
            <small style="color:#777;">Kursus: <strong><?= htmlspecialchars($course['title']) ?></strong></small>
        </div>
        <a href="courses.php" class="btn-back">&larr; Kembali</a>
    </div>

    <div class="grid-compact">
        
        <div class="card-small">
            <div class="card-head text-blue">📂 Upload Materi</div>
            
            <?php if(isset($msg_materi)) echo "<div style='color:green; font-size:12px; margin-bottom:10px;'>$msg_materi</div>"; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="label-small">Judul Materi</label>
                    <input type="text" name="material_title" class="input-small" placeholder="Contoh: Modul Bab 1" required>
                </div>
                <div class="form-group">
                    <label class="label-small">File (PDF/Video)</label>
                    <input type="file" name="file_content" class="input-small" required>
                </div>
                <button type="submit" name="upload_material" class="btn-action btn-blue">Upload</button>
            </form>
        </div>

        <div class="card-small">
            <div class="card-head text-green">💪 Latihan Mandiri</div>

            <?php if(isset($msg_latihan)) echo "<div style='color:green; font-size:12px; margin-bottom:10px;'>$msg_latihan</div>"; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="label-small">Judul Latihan</label>
                    <input type="text" name="exercise_title" class="input-small" placeholder="Contoh: Tugas Harian..." required>
                </div>
                <div class="form-group">
                    <p style="font-size:12px; color:#888; margin:0;">
                        Buat wadah tugas. Anda bisa menambah soal di tabel bawah.
                    </p>
                </div>
                <button type="submit" name="add_exercise" class="btn-action btn-green">+ Buat Latihan</button>
            </form>
        </div>

    </div>

    <div class="table-box">
        <h4 style="margin:0 0 10px 0; color:#333;">Daftar Konten Tersimpan</h4>
        
        <table class="table-simple">
            <thead>
                <tr>
                    <th width="15%">Tipe</th>
                    <th width="40%">Judul</th>
                    <th width="30%">Detail / Atur</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $mats = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY material_id DESC");
                $mats->execute([$course_id]);
                while($m = $mats->fetch()): ?>
                <tr>
                    <td><span style="color:#3498db; font-weight:bold;">MATERI</span></td>
                    <td><?= htmlspecialchars($m['title']) ?></td>
                    <td style="color:#999;">File: <?= basename($m['file_path']) ?></td>
                    <td>
                        <a href="?id=<?=$course_id?>&delete_material=<?=$m['material_id']?>" onclick="return confirm('Hapus?')" style="color:red; text-decoration:none; font-size:12px;">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php
                $quizzes = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ? ORDER BY quiz_id DESC");
                $quizzes->execute([$course_id]);
                while($q = $quizzes->fetch()): 
                    $is_exercise = (strpos($q['title'], '[Latihan]') !== false);
                    $label = $is_exercise ? 'LATIHAN' : 'KUIS';
                    $color = $is_exercise ? '#27ae60' : '#e67e22';
                ?>
                <tr>
                    <td><span style="color:<?=$color?>; font-weight:bold;"><?= $label ?></span></td>
                    <td><?= htmlspecialchars(str_replace('[Latihan] ', '', $q['title'])) ?></td>
                    <td>
                        <a href="edit_quiz.php?quiz_id=<?=$q['quiz_id']?>&course_id=<?=$course_id?>" 
                        style="background:#333; color:white; padding:5px 10px; text-decoration:none; border-radius:4px; font-size:12px;">
                        ⚙ Atur Soal
                        </a>
                    </td>
                    <td>
                        <a href="?id=<?=$course_id?>&delete_quiz=<?=$q['quiz_id']?>" onclick="return confirm('Hapus?')" style="color:red; text-decoration:none; font-size:12px;">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if($mats->rowCount() == 0 && $quizzes->rowCount() == 0): ?>
                    <tr><td colspan="4" align="center" style="color:#ccc; padding:20px;">Belum ada konten.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>