<?php
require "../config/db.php";

// --- LOGIKA PAGINATION ---
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

// Hitung Total Halaman
$total_stmt = $pdo->query("SELECT COUNT(*) FROM courses");
$total_courses = $total_stmt->fetchColumn();
$total_pages = ceil($total_courses / $limit);

// --- MODIFIKASI QUERY UTAMA ---
// Menambahkan subquery COUNT(*) untuk menghitung siswa di setiap kelas
$stmt = $pdo->prepare("
    SELECT c.*, u.name AS instructor,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.course_id) AS total_students
    FROM courses c
    JOIN users u ON c.instructor_id=u.user_id
    ORDER BY c.course_id DESC 
    LIMIT :start, :limit
");
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$courses = $stmt->fetchAll();

// Batas Maksimal Siswa
const MAX_STUDENTS = 10; 
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Semua Kursus</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>
    /* --- GLOBAL STYLE --- */
    body {
        font-family: 'Inter', sans-serif;
        background-color: #fdfbf7; /* Krem Hangat */
        color: #333;
        padding: 0;
        margin: 0;
    }

    /* --- HEADER --- */
    .header-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 30px 40px;
        background-color: #fdfbf7; 
        margin-bottom: 20px;
        position: relative;
    }

    .page-title {
        margin: 0;
        font-family: 'Playfair Display', serif; 
        font-weight: 700;
        color: #1a1a1a; 
        font-size: 2.2rem;
    }

    .btn-back {
        position: absolute;
        right: 40px;
        text-decoration: none;
        padding: 10px 20px;
        background-color: #2c3e50; 
        color: #fff;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        transition: background 0.3s;
    }
    .btn-back:hover {
        background-color: #1a252f;
    }

    /* --- GRID LAYOUT --- */
    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        padding: 0 40px 40px;
        max-width: 1200px;
        margin: 0 auto;
    }

    /* --- CARD --- */
    .course-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.03); 
        border: 1px solid #f0ece3;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative; /* Untuk badge full */
        overflow: hidden;
    }

    .course-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
        border-color: #e0dbd0;
    }

    /* Efek Overlay Jika Penuh (Opsional) */
    .course-card.full {
        border: 1px solid #e74c3c; /* Border Merah */
        background-color: #fff5f5; /* Background kemerahan */
    }

    .course-card h3 {
        margin-top: 0;
        color: #2c3e50;
        font-family: 'Inter', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .course-card p {
        color: #666;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .price {
        font-weight: 700;
        color: #c49b63; 
        font-size: 1.1rem;
        margin-bottom: 5px;
    }

    .student-count {
        font-size: 0.8rem;
        color: #888;
        margin-bottom: 15px;
        display: block;
    }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #f5f5f5;
        padding-top: 15px;
        margin-top: auto;
    }

    .card-footer span {
        font-size: 0.85rem;
        color: #888;
        font-weight: 500;
    }

    .btn-view {
        background-color: #2c3e50;
        color: white;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        transition: background 0.2s;
    }
    .btn-view:hover {
        background-color: #c49b63;
    }

    /* TOMBOL FULL (DISABLED) */
    .btn-full {
        background-color: #c0392b; /* Merah */
        color: white;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: not-allowed; /* Kursor tanda larang */
        opacity: 0.8;
    }

    /* PAGINATION */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
        margin-bottom: 60px;
        gap: 10px;
    }

    .page-link {
        text-decoration: none;
        padding: 10px 16px;
        background-color: #fff;
        color: #2c3e50;
        border: 1px solid #d1d1d1;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .page-link:hover {
        background-color: #2c3e50;
        color: white;
        border-color: #2c3e50;
    }

    .page-link.active {
        background-color: #c49b63; 
        color: white;
        border-color: #c49b63;
        pointer-events: none; 
    }

    .page-info {
        color: #888;
        font-size: 0.9rem;
        margin: 0 15px;
    }

    @media (max-width: 600px) {
        .header-container { flex-direction: column; gap: 20px; }
        .btn-back { position: static; width: 100%; text-align: center; box-sizing: border-box; }
        .course-grid { padding: 0 20px 40px; }
    }
</style>

</head>
<body>

    <div class="header-container">
        <h1 class="page-title">Semua Kursus</h1>
        <a class="btn-back" href="../student/dashboard.php">
            &larr; Kembali Ke Dashboard
        </a>
    </div>

    <div class="course-grid">
        <?php if (count($courses) > 0): ?>
            <?php foreach($courses as $c): 
                // LOGIKA PENGECEKAN KUOTA
                $current_students = $c['total_students'];
                $is_full = ($current_students >= MAX_STUDENTS);
            ?>
                <div class="course-card <?= $is_full ? 'full' : '' ?>">
                    <div>
                        <h3><?=$c['title']?></h3>
                        <p><?= mb_strimwidth($c['short_desc'] ?? 'Deskripsi tidak ada.', 0, 100, "...") ?></p>
                        
                        <div class="price">
                            <?=($c['price']==0 ? "Gratis" : "Rp ".number_format($c['price'],0,',','.'))?>
                        </div>

                        <span class="student-count" style="color: <?= $is_full ? '#c0392b' : '#27ae60' ?>; font-weight:bold;">
                            <i class="fa-solid fa-users"></i> 
                            <?= $current_students ?> / <?= MAX_STUDENTS ?> Siswa
                            <?= $is_full ? '(PENUH)' : '' ?>
                        </span>
                    </div>

                    <div class="card-footer">
                        <span>👨‍🏫 <?=$c['instructor']?></span>
                        
                        <?php if ($is_full): ?>
                            <span class="btn-full">KELAS PENUH</span>
                        <?php else: ?>
                            <a class="btn-view" href="course_detail.php?course=<?=$c['course_id']?>">
                                Daftar &rarr;
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align:center; grid-column: 1/-1; color:#888;">Belum ada kursus yang tersedia.</p>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="page-link">&larr; Prev</a>
        <?php endif; ?>

        <span class="page-info">Halaman <?= $page ?> dari <?= $total_pages ?></span>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>" class="page-link">Next &rarr;</a>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</body>
</html>