<?php
// filename: admin/course_detail.php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$course_id = $_GET['id'] ?? null;

// Ambil Data Course
$stmt = $pdo->prepare("SELECT c.*, u.name as instructor_name FROM courses c JOIN users u ON c.instructor_id = u.user_id WHERE c.course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

// Ambil Materi yang sudah ada
$stmt_mat = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY created_at ASC");
$stmt_mat->execute([$course_id]);
$materials = $stmt_mat->fetchAll();

// Ambil Latihan yang sudah ada
$stmt_ass = $pdo->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY created_at ASC");
$stmt_ass->execute([$course_id]);
$assignments = $stmt_ass->fetchAll();
?>

<div class="admin-container">
    <div class="page-header-row">
        <h1 class="page-title">Kelola Kelas: <?= htmlspecialchars($course['title']) ?></h1>
        <a href="courses.php" class="btn-back">Selesai</a>
    </div>

    <div class="info-box" style="background:#fff; padding:15px; border-radius:8px; margin-bottom:20px; border-left: 5px solid #2c3e50;">
        <p><strong>Instruktur:</strong> <?= htmlspecialchars($course['instructor_name']) ?></p>
        <p><strong>Deskripsi:</strong> <?= htmlspecialchars($course['description']) ?></p>
    </div>

    <div class="content-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        
        <div class="card-module">
            <div class="header-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3>📚 Materi Pelajaran</h3>
                <a href="material_add.php?course_id=<?=$course_id?>" class="btn-small">+ Tambah Materi</a>
            </div>
            
            <ul class="list-group">
                <?php if(count($materials) == 0) echo "<i>Belum ada materi.</i>"; ?>
                <?php foreach($materials as $m): ?>
                    <li style="background:#f9f9f9; padding:10px; margin-bottom:5px; border-radius:4px;">
                        <?= htmlspecialchars($m['title']) ?> <span style="font-size:12px; color:#888;">(<?= $m['type'] ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card-module">
            <div class="header-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3>📝 Latihan Mandiri</h3>
                <a href="assignment_add.php?course_id=<?=$course_id?>" class="btn-small">+ Tambah Latihan</a>
            </div>

            <ul class="list-group">
                <?php if(count($assignments) == 0) echo "<i>Belum ada latihan.</i>"; ?>
                <?php foreach($assignments as $a): ?>
                    <li style="background:#f9f9f9; padding:10px; margin-bottom:5px; border-radius:4px;">
                        <?= htmlspecialchars($a['title']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

    </div>
</div>

<style>
    .card-module {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .btn-small {
        background: #27ae60;
        color: white;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
    }
    .list-group { list-style: none; padding: 0; }
</style>