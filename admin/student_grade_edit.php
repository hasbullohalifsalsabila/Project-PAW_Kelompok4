<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$id = $_GET['id'] ?? null;
if(!$id) { echo "<script>window.location='student_grades.php';</script>"; exit; }

// Ambil Data Lama
$stmt = $pdo->prepare("SELECT r.*, u.name, q.title FROM quiz_results r 
                       JOIN users u ON r.user_id = u.user_id 
                       JOIN quizzes q ON r.quiz_id = q.quiz_id 
                       WHERE result_id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_score = $_POST['score'];
    $pdo->prepare("UPDATE quiz_results SET score = ? WHERE result_id = ?")->execute([$new_score, $id]);
    echo "<script>alert('✅ Nilai berhasil diupdate!'); window.location='student_grades.php';</script>";
}
?>

<style>
    body { background-color: #fdfbf7; margin: 0; padding: 0; }

    /* KONTAINER UTAMA */
    .main-content {
        margin-left: 250px; 
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
        
        /* --- PERBAIKAN DI SINI --- */
        /* Memberi jarak sangat lega dari atas agar tidak nabrak header */
        padding-top: 120px; 
        padding-left: 40px;
        padding-right: 40px;
        padding-bottom: 60px;

        display: flex;
        flex-direction: column;
        /* Mengubah dari center ke flex-start agar patokannya dari atas */
        justify-content: flex-start; 
        align-items: center;     
    }

    /* WRAPPER KONTEN */
    .content-wrapper {
        width: 100%;
        max-width: 550px; 
        /* Tambahan margin atas agar terlihat agak ke tengah tapi aman */
        margin-top: 20px; 
    }

    /* HEADER (JUDUL & TOMBOL KEMBALI) */
    .page-header-flex {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 20px; 
        padding-bottom: 15px;
        border-bottom: 2px solid #e0dbd0;
    }
    .page-title { 
        font-family: 'Times New Roman', serif; 
        font-size: 1.8rem; color: #2c3e50; margin: 0; 
    }
    
    .btn-back {
        text-decoration: none; border: 1px solid #555; color: #555;
        padding: 6px 15px; border-radius: 20px; font-weight: 600; transition: 0.3s; font-size: 13px;
    }
    .btn-back:hover { background: #555; color: white; }

    /* KARTU FORM */
    .card-edit { 
        background: white; 
        padding: 40px; 
        border-radius: 12px; 
        width: 100%; 
        box-sizing: border-box;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
        border: 1px solid #f0ece3;
    }

    /* FORM INPUT */
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-weight: bold; margin-bottom: 8px; font-size: 14px; color: #555; }
    
    .form-control { 
        width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; 
        box-sizing: border-box; font-size: 14px;
    }
    .form-control:focus { outline: none; border-color: #2c3e50; }

    /* TOMBOL SIMPAN */
    .btn-save { 
        width: 100%; padding: 14px; background: #2c3e50; color: white; 
        border: none; border-radius: 6px; cursor: pointer; font-weight: bold; 
        font-size: 16px; transition: 0.3s;
    }
    .btn-save:hover { background: #1a252f; }

    .readonly-input { background-color: #f9f9f9; color: #777; cursor: not-allowed; border-color: #eee; }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding: 100px 20px 40px 20px; }
    }
</style>

<div class="main-content">
    
    <div class="content-wrapper">
        
        <div class="page-header-flex">
            <h1 class="page-title">Edit Nilai Siswa</h1>
            <a href="student_grades.php" class="btn-back">&larr; Kembali</a>
        </div>

        <div class="card-edit">
            <form method="POST">
                
                <div class="form-group">
                    <label class="form-label">Nama Siswa</label>
                    <input type="text" class="form-control readonly-input" value="<?= htmlspecialchars($data['name']) ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Judul Kuis/Latihan</label>
                    <input type="text" class="form-control readonly-input" value="<?= htmlspecialchars($data['title']) ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nilai (Score)</label>
                    <input type="number" name="score" class="form-control" value="<?= $data['score'] ?>" min="0" max="100" required style="font-size: 18px; font-weight: bold; color: #2c3e50;">
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
                </div>

            </form>
        </div>

    </div>
</div>