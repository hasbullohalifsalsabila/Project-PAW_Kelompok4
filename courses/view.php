<?php
// =========================================================================
// 1. LOGIKA BACKEND
// =========================================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek Login
if (!isset($_SESSION['user']['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$uid = $_SESSION['user']['user_id'];
$cid = (int) ($_GET['id'] ?? 0);

// Validasi ID
if (!$cid) {
    header('Location: my_courses.php');
    exit;
}

// Cek Enrolled
$stmt_enroll = $pdo->prepare("SELECT 1 FROM enrollments WHERE user_id=? AND course_id=?");
$stmt_enroll->execute([$uid, $cid]);
if (!$stmt_enroll->fetch()) {
    echo "<script>alert('Anda tidak terdaftar dalam kursus ini.'); window.location='my_courses.php';</script>";
    exit;
}

// Ambil Data Kursus
$stmt_course = $pdo->prepare("SELECT * FROM courses WHERE course_id=?");
$stmt_course->execute([$cid]);
$course = $stmt_course->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo "<script>alert('Kursus tidak ditemukan.'); window.location='my_courses.php';</script>";
    exit;
}

// Ambil Materi
$stmt_materials = $pdo->prepare("
    SELECT m.*, 
        (SELECT is_completed FROM materials_progress 
         WHERE user_id = :uid AND material_id = m.material_id LIMIT 1) AS completed
    FROM materials m
    WHERE course_id = :cid
    ORDER BY position ASC, material_id ASC
");
$stmt_materials->execute([':uid' => $uid, ':cid' => $cid]);
$materials = $stmt_materials->fetchAll(PDO::FETCH_ASSOC);

// Ambil Kuis
$stmt_quizzes = $pdo->prepare("SELECT quiz_id, title FROM quizzes WHERE course_id=? ORDER BY quiz_id ASC");
$stmt_quizzes->execute([$cid]);
$all_quizzes = $stmt_quizzes->fetchAll(PDO::FETCH_ASSOC);

// Hitung Skor Terbaik Kuis
$quiz_results = [];
if (!empty($all_quizzes)) {
    $quiz_ids = array_column($all_quizzes, 'quiz_id');
    $placeholders = implode(',', array_fill(0, count($quiz_ids), '?'));
    
    $stmt_results = $pdo->prepare("
        SELECT quiz_id, MAX(score) as score
        FROM quiz_results
        WHERE user_id = ? AND quiz_id IN ({$placeholders})
        GROUP BY quiz_id
    ");

    $params = array_merge([$uid], $quiz_ids);
    $stmt_results->execute($params);
    
    while ($row = $stmt_results->fetch(PDO::FETCH_ASSOC)) {
        $quiz_results[$row['quiz_id']] = $row;
    }
}

// Pisahkan Latihan vs Evaluasi
$latihan_mandiri = [];
$evaluasi_kuis = [];

foreach ($all_quizzes as $q) {
    $quiz_id = $q['quiz_id'];
    $result = $quiz_results[$quiz_id] ?? null;
    
    $q['status'] = $result ? 'completed' : 'ready';
    $q['score'] = $result ? $result['score'] : null;

    if (stripos($q['title'], '[Latihan]') !== false) {
        $q['display_title'] = trim(str_ireplace('[Latihan]', '', $q['title']));
        $latihan_mandiri[] = $q;
    } else {
        $q['display_title'] = $q['title'];
        $evaluasi_kuis[] = $q;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?> - Course View</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* CSS RESET */
        * { box-sizing: border-box; }
        
        /* WARNA TEMA KREM HANGAT (SEPERTI SEBELUMNYA) */
        body { 
            background-color: #fdfbf7; 
            font-family: 'Inter', sans-serif; 
            margin: 0; padding: 0;
            color: #2c3e50;
        }

        /* CONTAINER MODE FOKUS */
        .focus-container {
            max-width: 950px; 
            margin: 0 auto;   
            padding: 40px 20px;
            min-height: 100vh;
        }

        /* NAVIGASI ATAS */
        .focus-nav {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 50px; 
        }

        /* TOMBOL KEMBALI (Solid Dark) */
        .btn-back-solid {
            background-color: #2c3e50; 
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 50px;
            display: inline-flex; align-items: center; gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            font-size: 0.95rem;
        }
        .btn-back-solid:hover {
            background-color: #c49b63; /* Aksen Emas/Coklat */
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .brand-logo { 
            font-family: 'Playfair Display', serif; 
            font-weight: bold; font-size: 1.4rem; color: #2c3e50; 
        }

        /* HEADER KURSUS */
        .course-header { text-align: center; margin-bottom: 60px; }
        .course-title { 
            font-family: 'Playfair Display', serif; 
            font-size: 3rem; color: #1a1a1a; 
            margin: 0 0 15px 0; line-height: 1.2; 
        }
        .course-desc { 
            font-size: 1.1rem; color: #666; 
            max-width: 700px; margin: 0 auto; line-height: 1.6; 
        }

        /* SECTION STYLES */
        .section-wrapper { margin-bottom: 60px; }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem; color: #2c3e50;
            margin-bottom: 25px; padding-bottom: 15px;
            border-bottom: 2px solid #e0dbd0; /* Garis pemisah warna hangat */
            display: flex; align-items: center; gap: 10px;
        }

        /* TABEL ELEGAN */
        .table-responsive { overflow-x: auto; border-radius: 12px; }
        .custom-table {
            width: 100%; border-collapse: separate; border-spacing: 0 12px;
        }
        
        .custom-table tr {
            background: #ffffff; /* Row Putih di atas background krem */
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .custom-table tr:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
            z-index: 2; position: relative;
        }

        .custom-table td {
            padding: 20px; vertical-align: middle;
            border-top: 1px solid #f0ece3; 
            border-bottom: 1px solid #f0ece3;
        }
        
        /* Rounded Corners pada Row */
        .custom-table td:first-child { 
            border-top-left-radius: 12px; border-bottom-left-radius: 12px; 
            border-left: 1px solid #f0ece3; 
        }
        .custom-table td:last-child { 
            border-top-right-radius: 12px; border-bottom-right-radius: 12px; 
            border-right: 1px solid #f0ece3; 
            text-align: right; 
        }

        /* IKON KONTEN */
        .table-icon {
            width: 50px; height: 50px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
        }
        /* Warna Ikon sesuai Tema Sebelumnya */
        .icon-materi { background: #f4f7f6; color: #2c3e50; }
        .icon-latihan { background: #e8f5e9; color: #2e7d32; }
        .icon-quiz { background: #fff3e0; color: #ef6c00; }

        /* TEKS KONTEN */
        .content-title { 
            font-weight: 700; font-size: 1.1rem; 
            display: block; color: #2c3e50; margin-bottom: 4px; 
        }
        .content-subtitle { 
            font-size: 0.8rem; color: #999; 
            text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; 
        }

        /* BADGE STATUS */
        .status-badge {
            padding: 6px 14px; border-radius: 20px; 
            font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; display: inline-block;
        }
        .bg-done { background: #e3f9e5; color: #1f9d55; border: 1px solid #c3e6cb; }
        .bg-pending { background: #f4f4f4; color: #888; border: 1px solid #ddd; }
        .bg-score { background: #e6f7ff; color: #005f73; border: 1px solid #b3e0ff; }
        .bg-ready { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }
        .bg-exam { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }

        /* TOMBOL AKSI (BUKA/MULAI) */
        .btn-open {
            background: white; border: 1px solid #ddd; color: #555;
            padding: 8px 18px; border-radius: 30px; text-decoration: none;
            font-size: 0.85rem; font-weight: 700; transition: 0.2s;
            display: inline-block;
        }
        .btn-open:hover { 
            background: #2c3e50; color: white; border-color: #2c3e50; 
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center; padding: 50px; 
            background: rgba(0,0,0,0.02); /* Transparan gelap dikit agar blend */
            border-radius: 12px; color: #999; font-style: italic;
        }

        /* RESPONSIF */
        @media (max-width: 600px) {
            .custom-table td { padding: 15px; }
            .content-subtitle { display: none; } 
            .course-title { font-size: 2.2rem; }
            .btn-back-solid span { display: none; } 
            .status-badge { display: none; } /* Sembunyikan badge di HP agar tidak sempit */
        }
    </style>
</head>
<body>

<div class="focus-container">

    <div class="focus-nav">
        <a href="../student/my_courses.php" class="btn-back-solid">
            <i class="fa-solid fa-arrow-left"></i> <span>Kembali ke Dashboard</span>
        </a>
        <div class="brand-logo">EduCourse.</div>
    </div>

    <div class="course-header">
        <h1 class="course-title"><?= htmlspecialchars($course['title']) ?></h1>
        <p class="course-desc"><?= htmlspecialchars($course['description']) ?></p>
    </div>

    <div class="section-wrapper">
        <h2 class="section-title"><i class="fa-solid fa-book-open" style="color:#aaa;"></i> &nbsp;Materi Pembelajaran</h2>
        
        <?php if (count($materials) > 0): ?>
        <div class="table-responsive">
            <table class="custom-table">
                <tbody>
                    <?php foreach ($materials as $m): ?>
                    <?php 
                        $iconClass = (stripos($m['type'], 'video') !== false) ? 'fa-play' : 'fa-file-lines';
                    ?>
                    <tr>
                        <td width="70">
                            <div class="table-icon icon-materi"><i class="fa-solid <?= $iconClass ?>"></i></div>
                        </td>
                        <td>
                            <span class="content-title"><?= htmlspecialchars($m['title']) ?></span>
                            <span class="content-subtitle">Materi Pelajaran</span>
                        </td>
                        <td width="160" style="text-align:center;">
                            <?php if ($m['completed']): ?>
                                <span class="status-badge bg-done"><i class="fa-solid fa-check"></i> Selesai</span>
                            <?php else: ?>
                                <span class="status-badge bg-pending">Belum Dibaca</span>
                            <?php endif; ?>
                        </td>
                        <td width="100">
                            <a href="material.php?id=<?= urlencode($m['material_id']) ?>" class="btn-open">
                                Buka
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">Belum ada materi.</div>
        <?php endif; ?>
    </div>

    <?php if (count($latihan_mandiri) > 0): ?>
    <div class="section-wrapper">
        <h2 class="section-title" style="color: #2e7d32; border-color: #a5d6a7;">
            <i class="fa-solid fa-puzzle-piece"></i> &nbsp;Latihan Mandiri
        </h2>
        <div class="table-responsive">
            <table class="custom-table">
                <tbody>
                    <?php foreach ($latihan_mandiri as $ex): ?>
                    <?php $is_completed = $ex['status'] == 'completed'; ?>
                    <tr>
                        <td width="70">
                            <div class="table-icon icon-latihan"><i class="fa-solid fa-brain"></i></div>
                        </td>
                        <td>
                            <span class="content-title"><?= htmlspecialchars($ex['display_title']) ?></span>
                            <span class="content-subtitle">Latihan Soal</span>
                        </td>
                        <td width="160" style="text-align:center;">
                            <?php if ($is_completed): ?>
                                <span class="status-badge bg-done">Skor: <?= round($ex['score']) ?>%</span>
                            <?php else: ?>
                                <span class="status-badge bg-ready">Siap</span>
                            <?php endif; ?>
                        </td>
                        <td width="100">
                            <a href="quiz_start.php?id=<?= urlencode($ex['quiz_id']) ?>" class="btn-open">
                                Mulai
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($evaluasi_kuis) > 0): ?>
        <div class="section-wrapper">
            <h2 class="section-title" style="color: #e65100; border-color: #ffcc80;">
                <i class="fa-solid fa-pen-nib"></i> &nbsp; Kuis Kelas
            </h2>
            <div class="table-responsive">
                <table class="custom-table">
                    <tbody>
                        <?php foreach ($evaluasi_kuis as $qz): ?>
                        <?php $is_completed = $qz['status'] == 'completed'; ?>
                        <tr>
                            <td width="70">
                                <div class="table-icon icon-quiz"><i class="fa-solid fa-stopwatch"></i></div>
                            </td>
                            <td>
                                <span class="content-title"><?= htmlspecialchars($qz['display_title']) ?></span>
                                <span class="content-subtitle">Ujian Akhir</span>
                            </td>
                            <td width="160" style="text-align:center;">
                                <?php if ($is_completed): ?>
                                    <span class="status-badge bg-score">Nilai: <?= round($qz['score']) ?></span>
                                <?php else: ?>
                                    <span class="status-badge bg-exam">Wajib</span>
                                <?php endif; ?>
                            </td>
                            <td width="100" style="text-align:right;">
                                <?php if ($is_completed): ?>
                                    <a href="quiz_result.php?score=<?=round($qz['score'])?>&qid=<?=$qz['quiz_id']?>" class="btn-open" style="background:#f8f9fa; color:#888; border-color:#ddd;">
                                        <i class="fa-solid fa-check"></i> Selesai
                                    </a>
                                <?php else: ?>
                                    <a href="quiz_start.php?id=<?= urlencode($qz['quiz_id']) ?>" class="btn-open">
                                        Ujian
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    <?php if (count($materials) == 0 && count($latihan_mandiri) == 0 && count($evaluasi_kuis) == 0): ?>
        <div class="empty-state">
            <h3>Kursus Kosong</h3>
            <p>Belum ada konten yang diupload instruktur.</p>
        </div>
    <?php endif; ?>

</div>

</body>
</html>