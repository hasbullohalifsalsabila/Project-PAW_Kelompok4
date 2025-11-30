<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$iid = $_SESSION['user']['user_id'];

// ==========================================
// LOGIKA DELETE
// ==========================================
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];

    $check = $pdo->prepare("SELECT course_id FROM courses WHERE course_id=? AND instructor_id=?");
    $check->execute([$del_id, $iid]);

    if ($check->rowCount() > 0) {
        $pdo->prepare("DELETE FROM courses WHERE course_id=?")->execute([$del_id]);
        echo "<script>alert('Kelas berhasil dihapus!'); location.href='courses.php';</script>";
    } else {
        echo "<script>alert('Akses ditolak.'); location.href='courses.php';</script>";
    }
}

// Ambil data courses
$courses = $pdo->prepare("SELECT * FROM courses WHERE instructor_id=? ORDER BY course_id DESC");
$courses->execute([$iid]);
$list = $courses->fetchAll();

// Hitung jumlah kelas saat ini
$total_kelas = count($list);
?>

<style>
    body {
        background-color: #fdfbf7;
    }

    .main-content {
        padding: 30px 40px 40px 280px; 
        min-height: 100vh;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 35px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e0dbd0;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.1rem;
        color: #2c3e50;
        margin: 0;
    }

    .btn-add {
        background-color: #2c3e50;
        color: #fff;
        padding: 11px 22px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 4px 14px rgba(0,0,0,0.2);
        transition: 0.25s;
    }
    .btn-add:hover {
        background-color: #c49b63;
        transform: translateY(-2px);
    }

    /* Style tambahan untuk tombol Disabled */
    .btn-disabled {
        background-color: #bdc3c7; /* Warna abu-abu */
        color: #fff;
        padding: 11px 22px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        cursor: not-allowed;
        border: 1px solid #b0b0b0;
    }

    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
        gap: 30px;
    }

    .course-card {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        border: 1px solid #f0ece3;
        box-shadow: 0 5px 15px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        transition: 0.3s ease;
    }
    .course-card:hover {
        transform: translateY(-5px);
        border-color: #c49b63;
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    }

    .course-card h3 {
        font-family: 'Playfair Display', serif;
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 12px;
        font-size: 1.3rem;
    }

    .course-meta {
        display: flex;
        justify-content: space-between;
        color: #999;
        font-size: 0.87rem;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f4f4f4;
    }

    .course-desc {
        flex-grow: 1;
        color: #666;
        font-size: 0.92rem;
        margin-bottom: 22px;
        line-height: 1.55;
    }

    .card-actions {
        display: flex;
        gap: 10px;
    }

    .btn-manage {
        flex: 1;
        background: #eef2f5;
        padding: 10px;
        text-align: center;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        color: #2c3e50;
        transition: 0.2s;
    }
    .btn-manage:hover {
        background: #2c3e50;
        color: #fff;
    }

    .btn-delete {
        background: #fff0f0;
        color: #e74c3c;
        border: 1px solid #ffcccc;
        padding: 10px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.2s;
    }
    .btn-delete:hover {
        background: #e74c3c;
        border-color: #e74c3c;
        color: #fff;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px;
        border-radius: 12px;
        background: #fff;
        border: 2px dashed #e0dbd0;
        color: #777;
    }
    
    @media(max-width: 900px) {
        .main-content {
            padding: 30px 25px;
        }
    }
</style>

<div class="main-content">
    
    <hr> 
    
    <div class="course-grid">
        <?php if ($total_kelas > 0): ?>
            <?php foreach ($list as $c): ?>
            <div class="course-card">

                <h3><?= htmlspecialchars($c['title']) ?></h3>

                <div class="course-meta">
                    <span><?= htmlspecialchars($c['level']) ?></span>
                    <span style="color:#c49b63;font-weight:600;">
                        <?= $c['price'] == 0 ? "Free" : "Rp " . number_format($c['price']) ?>
                    </span>
                </div>

                <div class="course-desc">
                    <?= mb_strimwidth(strip_tags($c['description'] ?? ''), 0, 100, "...") ?>
                </div>

                <div class="card-actions">
                    <a href="manage_course.php?id=<?= $c['course_id'] ?>" class="btn-manage">⚙ Kelola</a>

                    <a href="courses.php?delete_id=<?= $c['course_id'] ?>"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus kelas ini? Ini akan menghapus semua data terkait!')"
                        class="btn-delete">🗑</a>
                </div>

            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>Belum ada kelas</h3>
                <p>Klik tombol di atas untuk **Buat Kelas Baru**.</p>
            </div>
        <?php endif; ?>
    </div>

</div>