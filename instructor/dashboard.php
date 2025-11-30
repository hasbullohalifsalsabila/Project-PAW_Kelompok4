<?php
// Pastikan file konfigurasi dan tampilan dasar di-include
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// 1. Cek Login & Role
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SESSION['user']['role'] !== 'instructor') {
    die("Akses ditolak. Hanya instruktur yang dapat mengakses halaman ini.");
}

$iid = $_SESSION['user']['user_id']; 

try {
    // 2. Statistik: Total Kursus
    $c1 = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE instructor_id=?");
    $c1->execute([$iid]);
    $total_courses = $c1->fetchColumn();

    // 3. Statistik: Total Siswa Unik
    $c2 = $pdo->prepare("
        SELECT COUNT(DISTINCT e.user_id)
        FROM enrollments e
        JOIN courses c ON e.course_id=c.course_id
        WHERE c.instructor_id=?
    ");
    $c2->execute([$iid]);
    $total_students = $c2->fetchColumn();

    // 4. Ambil kelas + siswa (Raw Data)
    $sql = "
        SELECT c.course_id, c.title, u.name AS student_name, c.level
        FROM courses c
        LEFT JOIN enrollments e ON c.course_id = e.course_id
        LEFT JOIN users u ON e.user_id = u.user_id
        WHERE c.instructor_id = ?
        ORDER BY c.course_id DESC, u.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$iid]);
    $raw_data = $stmt->fetchAll();

    // Grouping Data
    $courses_list = [];
    foreach ($raw_data as $row) {
        $cid = $row['course_id'];

        if (!isset($courses_list[$cid])) {
            $courses_list[$cid] = [
                'title' => $row['title'],
                'level' => $row['level'],
                'students' => []
            ];
        }

        if ($row['student_name']) {
            $courses_list[$cid]['students'][] = $row['student_name'];
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage()); 
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

<style>
    /* RESET & GLOBAL */
    * { box-sizing: border-box; }
    body {
        background-color: #fdfbf7; /* Tema Krem Hangat */
        font-family: 'Inter', sans-serif;
        margin: 0; padding: 0;
        color: #2c3e50;
    }

    /* LAYOUT UTAMA (Sama seperti halaman siswa) */
    .main-content {
        margin-left: 280px; /* Sesuaikan dengan lebar Sidebar */
        padding: 40px;
        padding-top: 50px; /* Jarak dari atas */
        min-height: 100vh;
        max-width: 1200px;
    }

    /* HEADER */
    .page-header {
        margin-bottom: 40px;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 20px;
    }

    .welcome-text {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
    }

    .sub-text {
        color: #666;
        font-size: 1rem;
        margin-top: 5px;
    }

    /* STATS GRID */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 50px;
    }

    .stat-card {
        background: #ffffff;
        padding: 30px;
        border-radius: 16px;
        border: 1px solid #f0ece3;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        display: flex;
        align-items: center;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-color: #c49b63;
    }

    .stat-icon-bg {
        width: 60px; height: 60px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.8rem;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .icon-course { background-color: #fff3e0; color: #ef6c00; }
    .icon-student { background-color: #e3f2fd; color: #1565c0; }

    .stat-info h3 { margin: 0; font-size: 2.2rem; color: #2c3e50; font-weight: 700; }
    .stat-info p { margin: 0; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: 600; }

    /* DAFTAR KELAS */
    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        margin-bottom: 25px;
        color: #2c3e50;
        display: flex; align-items: center; gap: 10px;
    }

    .course-grid-layout {
        display: grid;
        grid-template-columns: 1fr; /* Single Column agar rapi ke bawah */
        gap: 25px;
    }

    .course-card {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #f0ece3;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        overflow: hidden;
    }

    .course-header-card {
        background-color: #faf9f6;
        padding: 20px 25px;
        border-bottom: 1px solid #f0ece3;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .course-info-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.3rem;
        color: #2c3e50;
        font-weight: 700;
    }

    .course-badge {
        background-color: #2c3e50;
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 600;
    }

    .student-list-container {
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .student-item {
        padding: 15px 25px;
        border-bottom: 1px solid #f8f8f8;
        color: #555;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .student-item:last-child { border-bottom: none; }
    .student-item:hover { background-color: #fdfbf7; }

    .student-icon { color: #ccc; }

    .empty-msg {
        padding: 20px 25px;
        color: #999;
        font-style: italic;
    }

    /* RESPONSIF */
    @media(max-width: 900px) {
        .main-content { margin-left: 0; padding: 100px 20px 40px 20px; }
    }
</style>

<div class="main-content"> 

    <div class="page-header">
        <h1 class="welcome-text">Dashboard Instruktur</h1>
        <p class="sub-text">Pantau performa kelas dan aktivitas siswa Anda.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon-bg icon-course">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_courses ?></h3>
                <p>Total Kelas</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-bg icon-student">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-info">
                <h3><?= $total_students ?></h3>
                <p>Total Siswa</p>
            </div>
        </div>
    </div>

    <h2 class="section-title"><i class="fa-solid fa-chalkboard-user"></i> Daftar Siswa per Kelas</h2>

    <div class="course-grid-layout">
        <?php if (empty($courses_list)): ?>
            <div class="empty-msg" style="text-align:center; padding:50px; border:2px dashed #ddd; border-radius:12px;">
                Belum ada kelas yang dibuat. <br>
                <a href="create_course.php" style="color:#c49b63; font-weight:bold; text-decoration:none;">+ Buat Kelas Baru</a>
            </div>
        <?php else: ?>
            
            <?php foreach ($courses_list as $course): ?>
                <div class="course-card">
                    <div class="course-header-card">
                        <div>
                            <div class="course-info-title"><?= htmlspecialchars($course['title']) ?></div>
                            <small style="color:#777;">Level: <?= ucfirst($course['level']) ?></small>
                        </div>
                        <span class="course-badge">
                            <?= count($course['students']) ?> Siswa
                        </span>
                    </div>

                    <ul class="student-list-container">
                        <?php if (empty($course['students'])): ?>
                            <li class="empty-msg">Belum ada siswa yang mendaftar di kelas ini.</li>
                        <?php else: ?>
                            <?php foreach ($course['students'] as $s): ?>
                                <li class="student-item">
                                    <i class="fa-solid fa-user-graduate student-icon"></i> 
                                    <?= htmlspecialchars($s) ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

</div>