<?php
// filename: admin/student_grades.php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// 1. DATA FILTER
$courses = $pdo->query("SELECT course_id, title FROM courses ORDER BY title ASC")->fetchAll();
$quizzes = $pdo->query("SELECT quiz_id, title FROM quizzes ORDER BY title ASC")->fetchAll();

// 2. LOGIKA CARI
$search_name = $_GET['student'] ?? '';
$filter_course = $_GET['course'] ?? '';
$filter_quiz   = $_GET['quiz'] ?? '';
$filter_date   = $_GET['date'] ?? '';

$sql = "SELECT r.*, u.name AS student_name, q.title AS quiz_title, c.title AS course_title, c.course_id
        FROM quiz_results r
        JOIN users u ON r.user_id = u.user_id
        JOIN quizzes q ON r.quiz_id = q.quiz_id
        JOIN courses c ON q.course_id = c.course_id
        WHERE 1=1 ";

$params = [];

if ($search_name) { $sql .= " AND u.name LIKE ?"; $params[] = "%$search_name%"; }
if ($filter_course) { $sql .= " AND c.course_id = ?"; $params[] = $filter_course; }
if ($filter_quiz) { $sql .= " AND r.quiz_id = ?"; $params[] = $filter_quiz; }
if ($filter_date) { $sql .= " AND DATE(r.attempted_at) = ?"; $params[] = $filter_date; }

$sql .= " ORDER BY r.attempted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$grades = $stmt->fetchAll();
?>

<style>
    /* Reset */
    body { background-color: #fdfbf7; margin: 0; padding: 0; }
    
    /* KONTAINER UTAMA */
    .main-content {
        margin-left: 250px; /* Lebar Sidebar */
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
        
        /* PERBAIKAN UTAMA DI SINI: */
        padding-top: 80px;    /* Jarak atas diperbesar agar tidak nabrak */
        padding-left: 40px;   /* Jarak kiri */
        padding-right: 40px;  /* Jarak kanan */
        padding-bottom: 40px; /* Jarak bawah */
    }

    /* JUDUL HALAMAN */
    .page-header-flex {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #e0dbd0; 
        padding-bottom: 15px;
    }
    .page-title { 
        font-family: 'Times New Roman', serif; 
        font-size: 2.2rem; /* Font diperbesar sedikit */
        color: #2c3e50; margin: 0; 
    }

    /* KOTAK FILTER */
    .filter-box {
        background: white; padding: 25px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
        margin-bottom: 30px;
        
        display: flex; flex-wrap: wrap; gap: 20px; align-items: flex-end; /* Tombol sejajar bawah */
    }

    .form-group { display: flex; flex-direction: column; flex: 1; min-width: 160px; }
    .form-group label { font-size: 13px; font-weight: bold; margin-bottom: 8px; color: #555; }
    
    .form-input { 
        width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; 
        font-size: 14px; box-sizing: border-box;
    }
    .form-input:focus { outline: none; border-color: #2c3e50; }

    /* TOMBOL BUTTON GROUP */
    .btn-group { display: flex; gap: 10px; }
    
    .btn-search { 
        background: #2c3e50; color: white; border: none; 
        padding: 11px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; 
        font-size: 14px; transition: 0.2s; height: 42px; /* Tinggi disamakan dengan input */
    }
    .btn-search:hover { background: #1a252f; }

    .btn-reset { 
        background: #95a5a6; color: white; text-decoration: none; 
        padding: 11px 20px; border-radius: 6px; font-weight: bold; font-size: 14px; 
        display: inline-flex; align-items: center; justify-content: center;
        height: 42px; box-sizing: border-box; transition: 0.2s;
    }
    .btn-reset:hover { background: #7f8c8d; }

    /* TABEL */
    .table-card { 
        background: white; padding: 30px; border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3; 
    }
    table { width: 100%; border-collapse: collapse; }
    th { 
        background: #f8f9fa; padding: 15px; text-align: left; 
        font-size: 14px; font-weight: bold; color: #444; 
        border-bottom: 2px solid #ddd; 
    }
    td { 
        padding: 15px; border-bottom: 1px solid #eee; color: #333; 
        font-size: 14px; vertical-align: middle;
    }

    .score-badge { font-weight: bold; padding: 5px 12px; border-radius: 20px; color: white; font-size: 12px; }
    .pass { background: #27ae60; } 
    .fail { background: #e74c3c; } 

    .btn-act { text-decoration: none; font-weight: bold; font-size: 13px; margin-right: 10px; }
    .edit { color: #2980b9; }
    .del { color: #c0392b; }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding: 80px 20px 20px 20px; }
        .filter-box { flex-direction: column; align-items: stretch; }
        .btn-group { flex-direction: column; }
    }
</style>

<div class="main-content">
    
    <div class="page-header-flex">
        <h1 class="page-title">Data Nilai Siswa</h1>
    </div>

    <form method="GET" class="filter-box">
        <div class="form-group">
            <label>Cari Nama Siswa</label>
            <input type="text" name="student" class="form-input" placeholder="Nama..." value="<?= htmlspecialchars($search_name) ?>">
        </div>
        
        <div class="form-group">
            <label>Filter Kelas</label>
            <select name="course" class="form-input">
                <option value="">-- Semua Kelas --</option>
                <?php foreach($courses as $c): ?>
                    <option value="<?=$c['course_id']?>" <?=$filter_course==$c['course_id']?'selected':''?>>
                        <?=$c['title']?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Filter Kuis</label>
            <select name="quiz" class="form-input">
                <option value="">-- Semua Kuis --</option>
                <?php foreach($quizzes as $q): ?>
                    <option value="<?=$q['quiz_id']?>" <?=$filter_quiz==$q['quiz_id']?'selected':''?>>
                        <?=$q['title']?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="date" class="form-input" value="<?=$filter_date?>">
        </div>

        <div class="btn-group">
            <button type="submit" class="btn-search">🔍 Cari</button>
            <a href="student_grades.php" class="btn-reset">✖ Reset</a>
        </div>
    </form>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Kuis / Latihan</th>
                    <th>Nilai</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($grades) > 0): ?>
                    <?php $no=1; foreach($grades as $g): 
                        $badge_class = ($g['score'] >= 70) ? 'pass' : 'fail';
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($g['student_name']) ?></strong></td>
                        <td><?= htmlspecialchars($g['course_title']) ?></td>
                        <td><?= htmlspecialchars($g['quiz_title']) ?></td>
                        <td><span class="score-badge <?= $badge_class ?>"><?= $g['score'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($g['attempted_at'])) ?></td>
                        <td>
                            <a href="student_grade_edit.php?id=<?=$g['result_id']?>" class="btn-act edit">✏ Edit</a>
                            <a href="student_grade_delete.php?id=<?=$g['result_id']?>" class="btn-act del">🗑 Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" align="center" style="padding:30px; color:#999;">Data tidak ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>