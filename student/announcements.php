<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$uid = $_SESSION['user']['user_id'];

// ==========================================
// 1. LOGIKA HAPUS NOTIFIKASI (DISMISS)
// ==========================================
if (isset($_GET['dismiss_type']) && isset($_GET['dismiss_id'])) {
    $type = $_GET['dismiss_type'];
    $id   = $_GET['dismiss_id'];

    // Simpan riwayat penghapusan agar tidak muncul lagi
    try {
        $stmt = $pdo->prepare("INSERT INTO notification_dismissals (user_id, type, item_id) VALUES (?, ?, ?)");
        $stmt->execute([$uid, $type, $id]);
    } catch (Exception $e) {
        // Jika error (misal duplikat), abaikan saja
    }

    // Refresh halaman agar notif hilang
    echo "<script>window.location='announcements.php';</script>";
    exit;
}

// ==========================================
// 2. AMBIL DATA GABUNGAN (UNION QUERY)
// ==========================================
// Mengambil: Pengumuman, Materi, Kuis dari kursus yang diikuti siswa
$sql = "
    SELECT * FROM (
        -- A. PENGUMUMAN DARI ADMIN/INSTRUKTUR
        SELECT 'announcement' as type, a.announcement_id as item_id, a.title, a.content as description, a.created_at, c.title as course_title
        FROM announcements a
        JOIN courses c ON a.course_id = c.course_id
        JOIN enrollments e ON e.course_id = c.course_id
        WHERE e.user_id = :uid1
        
        UNION ALL

        -- B. MATERI BARU
        SELECT 'material' as type, m.material_id as item_id, m.title, CONCAT('Materi baru (', m.type, ') telah ditambahkan.') as description, m.created_at, c.title as course_title
        FROM materials m
        JOIN courses c ON m.course_id = c.course_id
        JOIN enrollments e ON e.course_id = c.course_id
        WHERE e.user_id = :uid2

        UNION ALL

        -- C. KUIS BARU
        SELECT 'quiz' as type, q.quiz_id as item_id, q.title, 'Kuis baru telah tersedia, silakan kerjakan.' as description, q.created_at, c.title as course_title
        FROM quizzes q
        JOIN courses c ON q.course_id = c.course_id
        JOIN enrollments e ON e.course_id = c.course_id
        WHERE e.user_id = :uid3
    ) AS all_notifs
    
    -- Filter: Jangan tampilkan yang sudah dihapus user ini
    WHERE NOT EXISTS (
        SELECT 1 FROM notification_dismissals nd 
        WHERE nd.user_id = :uid4 
        AND nd.type = all_notifs.type 
        AND nd.item_id = all_notifs.item_id
    )
    ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':uid1' => $uid,
    ':uid2' => $uid,
    ':uid3' => $uid,
    ':uid4' => $uid
]);
$notifications = $stmt->fetchAll();
?>

<style>
    /* Layout */
    body { background-color: #fdfbf7; margin: 0; }
    .main-content {
        margin-left: 250px; padding: 40px; width: calc(100% - 250px);
        min-height: 100vh; box-sizing: border-box; padding-top: 80px;
    }

    .page-title { 
        font-family: 'Times New Roman', serif; font-size: 2rem; color: #2c3e50; 
        margin-bottom: 30px; border-bottom: 2px solid #e0dbd0; padding-bottom: 15px;
    }

    /* NOTIFICATION LIST */
    .notif-list { display: flex; flex-direction: column; gap: 20px; }

    /* CARD DESAIN */
    .notif-card {
        background: #ffffff; padding: 25px; border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0ece3;
        position: relative; border-left: 5px solid #ccc; transition: transform 0.2s;
    }
    .notif-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }

    /* WARNA JENIS NOTIFIKASI */
    .type-announcement { border-left-color: #e67e22; } /* Oranye */
    .type-material { border-left-color: #27ae60; }     /* Hijau */
    .type-quiz { border-left-color: #c0392b; }         /* Merah */

    /* BADGE LABEL */
    .notif-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .badge { 
        font-size: 11px; font-weight: bold; padding: 4px 10px; border-radius: 20px; 
        text-transform: uppercase; letter-spacing: 0.5px; 
    }
    .bg-announcement { background: #fff3e0; color: #e67e22; }
    .bg-material { background: #e8f5e9; color: #27ae60; }
    .bg-quiz { background: #ffebee; color: #c0392b; }

    .course-name { font-size: 13px; color: #888; font-weight: 600; }

    /* KONTEN */
    .notif-title { font-size: 1.4rem; font-weight: bold; color: #2c3e50; margin: 5px 0; font-family: serif; }
    .notif-desc { color: #555; line-height: 1.6; font-size: 14px; margin-bottom: 10px; }
    .notif-time { font-size: 12px; color: #999; font-style: italic; }

    /* TOMBOL HAPUS (X) */
    .btn-close {
        position: absolute; top: 15px; right: 20px; text-decoration: none;
        font-size: 1.5rem; color: #ccc; transition: 0.3s; line-height: 1;
    }
    .btn-close:hover { color: #c0392b; }

    /* EMPTY STATE */
    .empty-state { text-align: center; padding: 50px; color: #999; border: 2px dashed #ddd; border-radius: 12px; }
</style>

<div class="main-content">

    <h1 class="page-title">Pusat Informasi</h1>

    <div class="notif-list">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $n): 
                // Tentukan Label & Warna
                $label = 'Pengumuman';
                $bgClass = 'bg-announcement';
                if($n['type'] == 'material') { $label = 'Materi Baru'; $bgClass = 'bg-material'; }
                if($n['type'] == 'quiz')     { $label = 'Kuis Baru';   $bgClass = 'bg-quiz'; }
            ?>

            <div class="notif-card type-<?= $n['type'] ?>">
                
                <a href="announcements.php?dismiss_type=<?=$n['type']?>&dismiss_id=<?=$n['item_id']?>" 
                   class="btn-close" 
                   onclick="return confirm('Sembunyikan notifikasi ini dari beranda Anda?')" 
                   title="Hapus Notifikasi">&times;</a>

                <div class="notif-header">
                    <span class="badge <?= $bgClass ?>"><?= $label ?></span>
                    <span class="course-name"><?= htmlspecialchars($n['course_title']) ?></span>
                </div>

                <h3 class="notif-title"><?= htmlspecialchars($n['title']) ?></h3>
                <p class="notif-desc"><?= htmlspecialchars($n['description']) ?></p>

                <div class="notif-time">
                    Diposting: <?= date('d F Y, H:i', strtotime($n['created_at'])) ?>
                </div>
            </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h3>🎉 Semua Bersih!</h3>
                <p>Tidak ada pengumuman atau notifikasi baru saat ini.</p>
            </div>
        <?php endif; ?>
    </div>

</div>