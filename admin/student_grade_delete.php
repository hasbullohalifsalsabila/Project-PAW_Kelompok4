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
    body { background-color: #fdfbf7; margin: 0; }
    .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); }
    .card-edit { background: white; padding: 30px; border-radius: 8px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    .form-group { margin-bottom: 15px; }
    .form-label { display: block; font-weight: bold; margin-bottom: 5px; }
    .form-control { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
    .btn-save { width: 100%; padding: 12px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
</style>

<div class="main-content">
    <div class="card-edit">
        <h2 style="margin-top:0; text-align:center;">Edit Nilai Siswa</h2>
        <hr style="border:0; border-top:1px solid #eee; margin-bottom:20px;">
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">Nama Siswa</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" readonly style="background:#eee;">
            </div>
            <div class="form-group">
                <label class="form-label">Judul Kuis/Latihan</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($data['title']) ?>" readonly style="background:#eee;">
            </div>
            <div class="form-group">
                <label class="form-label">Nilai (Score)</label>
                <input type="number" name="score" class="form-control" value="<?= $data['score'] ?>" min="0" max="100" required>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="student_grades.php" style="flex:1; text-align:center; padding:12px; border:1px solid #ccc; color:#333; text-decoration:none; border-radius:5px;">Batal</a>
                <button type="submit" class="btn-save" style="flex:2;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>