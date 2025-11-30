<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$uid = $_SESSION['user']['user_id'];

try {
    // A. Total Kursus
    $total_courses = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE user_id=$uid")->fetchColumn();

    // B. Sertifikat (Cek apakah tabel certificates ada)
    $certs = 0;
    try {
        $certs = $pdo->query("SELECT COUNT(*) FROM certificates WHERE user_id=$uid")->fetchColumn();
    } catch (Exception $e) {
        // Jika tabel certificates belum ada, biarkan 0 agar tidak crash
        $certs = 0;
    }

    // C. Kursus Selesai (Asumsi: Sama dengan jumlah sertifikat)
    $completed = $certs;

    // D. Pengumuman
    $notes = $pdo->query("
        SELECT a.title, a.content, c.title AS course_title, a.created_at
        FROM announcements a
        JOIN courses c ON a.course_id = c.course_id
        JOIN enrollments e ON e.course_id = c.course_id
        WHERE e.user_id=$uid
        ORDER BY a.created_at DESC
        LIMIT 3
    ")->fetchAll();

} catch (PDOException $e) {
    die("<div style='margin-left:260px; padding:50px; color:red;'>
            <h2>Error Database!</h2>
            <p>Pesan: " . $e->getMessage() . "</p>
            <p>Solusi: Pastikan tabel <b>certificates</b> dan <b>enrollments</b> sudah dibuat.</p>
         </div>");
}
?>

<style>
    /* Layout */
    .main-content {
        margin-left: 250px; width: calc(100% - 250px);
        min-height: 100vh; box-sizing: border-box;
        background-color: #fdfbf7; padding: 40px; padding-top: 100px;
    }

    /* Header */
    .dashboard-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 30px; border-bottom: 2px solid #e0dbd0; padding-bottom: 20px;
    }
    .welcome-text { font-family: 'Times New Roman', serif; font-size: 2rem; color: #2c3e50; margin: 0; }
    .sub-text { color: #777; margin-top: 5px; font-size: 14px; }

    .btn-browse {
        background-color: #2c3e50; color: #fff; padding: 12px 25px;
        border-radius: 30px; text-decoration: none; font-weight: 600;
        transition: 0.3s;
    }
    .btn-browse:hover { background-color: #34495e; }

    /* Stats Grid */
    .stats-grid {
        display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; margin-bottom: 40px;
    }
    .stat-card {
        background: white; padding: 25px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
        transition: 0.3s; display: flex; flex-direction: column; justify-content: center;
        text-decoration: none; color: inherit; position: relative; overflow: hidden;
    }
    .stat-card::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: #ccc;
    }
    .card-blue::before { background: #3498db; }
    .card-green::before { background: #27ae60; }
    .card-orange::before { background: #f39c12; }

    .stat-num { font-size: 36px; font-weight: 800; color: #2c3e50; margin: 0; }
    .stat-label { font-size: 14px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }

    /* Announcements */
    .section-title { font-size: 1.5rem; color: #2c3e50; margin-bottom: 20px; font-weight: bold; }
    .announcement-card {
        background: white; padding: 20px; border-radius: 8px;
        border-left: 4px solid #e67e22; margin-bottom: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.03);
    }
    .ann-title { font-size: 16px; font-weight: bold; color: #d35400; margin: 0 0 5px 0; }
    .ann-course { font-size: 12px; background: #fdf0e0; color: #d35400; padding: 2px 8px; border-radius: 10px; font-weight: bold; }
    .ann-msg { color: #555; font-size: 14px; margin: 10px 0; line-height: 1.5; }
    .ann-time { font-size: 12px; color: #999; font-style: italic; }

    @media (max-width: 900px) {
        .main-content { margin-left: 0; width: 100%; padding: 100px 20px; }
        .stats-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="main-content">

    <div class="dashboard-header">
        <div>
            <h1 class="welcome-text">Selamat Datang, <?= htmlspecialchars($_SESSION['user']['name']) ?> 👋</h1>
            <p class="sub-text">Lanjutkan pembelajaran Anda hari ini.</p>
        </div>
        <a href="../courses/courses_list.php" class="btn-browse">🔍 Cari Kursus Baru</a>
    </div>

    <div class="stats-grid">
        <a href="my_courses.php" class="stat-card card-blue">
            <h3 class="stat-num"><?= $total_courses ?></h3>
            <p class="stat-label">Kursus Saya</p>
        </a>
        <div class="stat-card card-green">
            <h3 class="stat-num"><?= $completed ?></h3>
            <p class="stat-label">Selesai</p>
        </div>
        <div class="stat-card card-orange">
            <h3 class="stat-num"><?= $certs ?></h3>
            <p class="stat-label">Sertifikat</p>
        </div>
    </div>

    <?php if (isset($_GET['free_enroll'])): ?>
        <div style="background:#d4edda; color:#155724; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #c3e6cb;">
            🎉 <strong>Sukses!</strong> Anda berhasil mendaftar kursus gratis.
        </div>
    <?php endif; ?>

    <h2 class="section-title">📢 Pengumuman Terbaru</h2>
    
    <div class="announcement-list">
        <?php if (count($notes) > 0): ?>
            <?php foreach ($notes as $n): ?>
            <div class="announcement-card">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 class="ann-title"><?= htmlspecialchars($n['title']) ?></h3>
                    <span class="ann-course"><?= htmlspecialchars($n['course_title']) ?></span>
                </div>
                <p class="ann-msg"><?= htmlspecialchars($n['content']) ?></p>
                <small class="ann-time">Diposting: <?= date('d M Y, H:i', strtotime($n['created_at'])) ?></small>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color:#999; border:2px dashed #ddd; border-radius:8px;">
                Belum ada pengumuman dari instruktur.
            </div>
        <?php endif; ?>
    </div>

</div>