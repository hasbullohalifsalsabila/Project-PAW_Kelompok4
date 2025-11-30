<?php
// 1. Deteksi Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// 2. Ambil Data Filter
try {
    $courses = $pdo->query("SELECT course_id, title FROM courses ORDER BY title ASC")->fetchAll();
} catch (PDOException $e) {
    die("Error Database: " . $e->getMessage());
}

$course_filter = $_GET['course'] ?? '';

// 3. Query Utama
$query = "SELECT a.*, c.title AS course_title, u.name AS instructor
          FROM announcements a
          JOIN courses c ON a.course_id = c.course_id
          JOIN users u ON a.instructor_id = u.user_id
          WHERE 1=1 ";

if ($course_filter !== '') {
    $query .= "AND a.course_id = :cid";
}

$query .= " ORDER BY a.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    if ($course_filter !== '') $stmt->bindValue(':cid', $course_filter);
    $stmt->execute();
    $list = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error Query: " . $e->getMessage());
}
?>

<style>
    /* Background Utama */
    body { background-color: #fdfbf7; margin: 0; padding: 0; }

    /* KONTAINER UTAMA */
    .main-content {
        margin-left: 250px;
        width: calc(100% - 250px);
        min-height: 100vh;
        box-sizing: border-box;
        
        /* Jarak agar tidak nabrak header */
        padding-top: 80px; 
        padding-left: 40px;
        padding-right: 40px;
        padding-bottom: 40px;
    }

    /* HEADER HALAMAN */
    .page-header-flex {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }
    .page-title { 
        font-family: 'Times New Roman', serif; 
        font-size: 1.8rem; color: #2c3e50; margin: 0; 
    }

    /* FILTER BAR (KARTU PUTIH) */
    .filter-box {
        background: white; padding: 20px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
        margin-bottom: 25px;
        display: flex; align-items: center; justify-content: space-between;
    }
    .filter-left { display: flex; gap: 10px; align-items: center; width: 100%; }
    
    .form-select { 
        padding: 10px; border: 1px solid #ccc; border-radius: 6px; 
        font-size: 14px; min-width: 250px;
    }

    /* TOMBOL */
    .btn-dark { 
        background: #2c3e50; color: white; border: none; 
        padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 14px;
    }
    .btn-add { 
        background: #27ae60; color: white; text-decoration: none; 
        padding: 10px 20px; border-radius: 6px; font-weight: bold; font-size: 14px; white-space: nowrap;
    }
    .btn-add:hover { background: #219150; }

    /* TABEL DATA */
    .table-card { 
        background: white; padding: 25px; border-radius: 12px; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
    }
    table { width: 100%; border-collapse: collapse; }
    th { 
        background: #f8f9fa; padding: 15px; text-align: left; 
        font-size: 14px; font-weight: bold; color: #555; 
        border-bottom: 2px solid #eee; 
    }
    td { 
        padding: 15px; border-bottom: 1px solid #eee; color: #333; 
        font-size: 14px; vertical-align: middle;
    }

    /* BADGE & TOMBOL KECIL */
    .badge-course { background: #e3f2fd; color: #1565c0; padding: 4px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
    
    .btn-action { padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: bold; margin-right: 5px; }
    .btn-edit { background: #ecf0f1; color: #2c3e50; }
    .btn-del { background: #ffebee; color: #c62828; }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; width: 100%; padding-top: 80px; }
        .filter-box { flex-direction: column; align-items: stretch; gap: 15px; }
        .filter-left { flex-direction: column; align-items: stretch; }
        .form-select { width: 100%; }
    }
</style>

<div class="main-content">

    <div class="page-header-flex">
        <h1 class="page-title">Manage Announcements</h1>
    </div>

    <div class="filter-box">
        <form method="GET" class="filter-left">
            <select name="course" class="form-select">
                <option value="">-- Filter Berdasarkan Kursus --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?=$c['course_id']?>" <?=$course_filter==$c['course_id']?'selected':''?>>
                        <?=$c['title']?>
                    </option>
                <?php endforeach ?>
            </select>
            <button class="btn-dark">Filter</button>
        </form>
        
        <a href="announcement_add.php" class="btn-add">+ Buat Pengumuman</a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="25%">Kursus / Instruktur</th>
                    <th width="50%">Judul & Isi</th>
                    <th width="20%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($list) > 0): ?>
                    <?php foreach ($list as $a): ?>
                    <tr>
                        <td>#<?=$a['announcement_id']?></td>
                        <td>
                            <span class="badge-course"><?=$a['course_title']?></span><br>
                            <small style="color:#777; margin-top:5px; display:block;">Oleh: <?=$a['instructor']?></small>
                        </td>
                        <td>
                            <strong style="font-size:15px;"><?=$a['title']?></strong><br>
                            <span style="color:#666; font-size:13px;"><?= substr($a['content'], 0, 80) ?>...</span>
                        </td>
                        <td>
                            <a href="announcement_edit.php?id=<?=$a['announcement_id']?>" class="btn-action btn-edit">Edit</a>
                            <a href="announcement_delete.php?id=<?=$a['announcement_id']?>" class="btn-action btn-del"
                               onclick="return confirm('Hapus pengumuman ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 40px; color:#999;">
                            Belum ada pengumuman yang dibuat.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>