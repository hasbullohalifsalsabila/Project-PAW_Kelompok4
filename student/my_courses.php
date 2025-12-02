<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// Cek Login
if (!isset($_SESSION['user']['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$uid = $_SESSION['user']['user_id'];

// Ambil data kursus yang diikuti user
// Kita ambil semua kolom yang diperlukan
$stmt = $pdo->prepare("
    SELECT 
        c.title, 
        c.level, 
        c.price, 
        e.status, 
        e.course_id, 
        e.grade,
        e.enrolled_at
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.user_id = ?
    ORDER BY e.enrolled_at DESC
");
$stmt->execute([$uid]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* RESET */
    * { box-sizing: border-box; }
    body { background-color: #fdfbf7; font-family: 'Inter', sans-serif; }

    /* LAYOUT */
    .main-content {
        margin-left: 260px;
        width: calc(100% - 260px);
        min-height: 100vh;
        padding: 100px 40px 50px 40px;
    }

    /* JUDUL */
    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.2rem; color: #2c3e50;
        margin-bottom: 30px;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }

    /* GRID SYSTEM */
    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 25px;
    }

    /* CARD */
    .course-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #f0ece3;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        padding: 25px;
        display: flex; flex-direction: column; justify-content: space-between;
        transition: 0.3s; position: relative; overflow: hidden;
    }
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        border-color: #c49b63;
    }

    .course-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.4rem; color: #2c3e50; margin: 0 0 10px 0;
    }

    .meta-info { font-size: 0.9rem; color: #666; margin-bottom: 20px; }
    .meta-info div { margin-bottom: 5px; }

    /* STATUS BADGE */
    .badge {
        display: inline-block; padding: 6px 12px; border-radius: 20px;
        font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
    }
    .st-active { background: #e3f2fd; color: #1565c0; }
    .st-graduated { background: #e3f9e5; color: #1f9d55; }
    .st-pending { background: #fff3e0; color: #ef6c00; }

    /* TOMBOL */
    .btn-action {
        display: block; width: 100%; text-align: center;
        padding: 12px 0; border-radius: 8px; text-decoration: none;
        font-weight: 600; transition: 0.3s;
    }
    .btn-study { background: #2c3e50; color: white; }
    .btn-study:hover { background: #c49b63; }
    
    .btn-cert { background: #27ae60; color: white; border: 1px solid #27ae60; }
    .btn-cert:hover { background: #219150; }

    /* EMPTY STATE */
    .empty-state {
        text-align: center; padding: 60px;
        background: white; border-radius: 16px; border: 2px dashed #ddd;
    }
    .empty-icon { font-size: 3rem; color: #ccc; margin-bottom: 15px; }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .main-content { margin-left: 0; width: 100%; padding: 100px 20px; }
    }
</style>

<div class="main-content">

    <h1 class="page-title">Kursus Saya</h1>

    <?php if (count($courses) > 0): ?>
        
        <div class="course-grid">
            <?php foreach ($courses as $c): ?>
                <?php 
                    $status = $c['status'] ?? 'active'; // Default active jika null
                    $is_graduated = ($status === 'graduated');
                    $badgeClass = $is_graduated ? 'st-graduated' : 'st-active';
                    $statusLabel = $is_graduated ? 'Lulus' : 'Aktif';
                ?>

                <div class="course-card" style="<?= $is_graduated ? 'border-color:#27ae60;' : '' ?>">
                    
                    <?php if($is_graduated): ?>
                        <div style="position:absolute; top:15px; right:15px; color:#27ae60; font-size:1.5rem;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    <?php endif; ?>

                    <div>
                        <h3><?= htmlspecialchars($c['title']) ?></h3>
                        <div class="meta-info">
                            <div><i class="fa-solid fa-layer-group"></i> Level: <strong><?= ucfirst($c['level']) ?></strong></div>
                            <div><i class="fa-solid fa-calendar-days"></i> Gabung: <?= date('d M Y', strtotime($c['enrolled_at'])) ?></div>
                            
                            <div style="margin-top:10px;">
                                <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                                <?php if($c['grade']): ?>
                                    <span class="badge st-active"> <?= $c['grade'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:auto;">
                        <?php if($is_graduated): ?>
                            <a href="my_certificates.php" class="btn-action btn-cert">
                                <i class="fa-solid fa-certificate"></i> Lihat Sertifikat
                            </a>
                            <a href="../courses/view.php?id=<?=$c['course_id']?>" style="display:block; text-align:center; margin-top:10px; color:#666; font-size:0.85rem; text-decoration:none;">
                                Review Materi
                            </a>
                        <?php else: ?>
                            <a href="../courses/view.php?id=<?=$c['course_id']?>" class="btn-action btn-study">
                                Lanjutkan Belajar &rarr;
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-folder-open"></i></div>
            <h3 style="color:#555;">Belum Ada Kursus</h3>
            <p style="color:#888; margin-bottom:25px;">Anda belum mendaftar di kelas manapun saat ini.</p>
            
            <a href="../courses/courses_list.php" class="btn-action btn-study" style="max-width:250px; margin:0 auto;">
                🔍 Cari Kursus Baru
            </a>
        </div>

    <?php endif; ?>

</div>