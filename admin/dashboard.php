<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// 1. Cek Login Admin (Keamanan)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// 2. Fetch Statistik
try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
    $totalInstructors = $pdo->query("SELECT COUNT(*) FROM users WHERE role='instructor'")->fetchColumn();
    $totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
} catch (PDOException $e) {
    $totalUsers = $totalStudents = $totalInstructors = $totalCourses = 0;
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* RESET & GLOBAL */
    * { box-sizing: border-box; }
    body { 
        background-color: #fdfbf7; /* Tema Cream Hangat */
        font-family: 'Inter', sans-serif; 
        margin: 0; padding: 0;
        color: #2c3e50;
    }

    /* LAYOUT UTAMA */
    .admin-container {
        margin-left: 260px; /* Memberi ruang untuk sidebar */
        padding: 40px;
        padding-top: 100px; /* Jarak dari header */
        min-height: 100vh;
        width: calc(100% - 260px);
    }

    /* JUDUL HALAMAN */
    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        margin-bottom: 30px;
        color: #2c3e50;
        border-bottom: 2px solid #e0dbd0;
        padding-bottom: 15px;
    }

    /* GRID KARTU STATISTIK */
    .grid-4 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 25px;
        margin-bottom: 50px;
    }

    .stat-card {
        background: #ffffff;
        padding: 25px;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #f0ece3;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border-color: #c49b63; /* Aksen Emas */
    }

    /* Hiasan Garis Samping Kartu */
    .stat-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 6px;
        background-color: #2c3e50;
    }
    
    /* Warna spesifik per kartu */
    .stat-card:nth-child(1)::before { background-color: #3498db; } /* Users - Biru */
    .stat-card:nth-child(2)::before { background-color: #27ae60; } /* Students - Hijau */
    .stat-card:nth-child(3)::before { background-color: #e67e22; } /* Instructors - Oranye */
    .stat-card:nth-child(4)::before { background-color: #9b59b6; } /* Courses - Ungu */

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        color: #2c3e50;
        line-height: 1;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #7f8c8d;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* SECTION TITLE */
    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* TABEL USER */
    .table-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #f0ece3;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        text-align: left;
        padding: 15px;
        background-color: #f8f9fa;
        color: #555;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 2px solid #eee;
    }

    td {
        padding: 15px;
        border-bottom: 1px solid #f0ece3;
        color: #333;
        vertical-align: middle;
    }

    tr:last-child td { border-bottom: none; }
    tr:hover { background-color: #fcfcfc; }

    /* BADGE ROLE */
    .role-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .role-admin { background: #34495e; color: white; }
    .role-instructor { background: #fff3e0; color: #e67e22; border: 1px solid #ffe0b2; }
    .role-student { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }

    /* RESPONSIVE HP */
    @media (max-width: 900px) {
        .admin-container { 
            margin-left: 0; 
            width: 100%; 
            padding: 100px 20px 40px 20px; 
        }
        .grid-4 { grid-template-columns: 1fr; }
    }
</style>

<div class="admin-container">

    <h1 class="page-title">Dashboard Overview</h1>

    <div class="grid-4">
        <div class="stat-card">
            <div class="stat-number"><?=$totalUsers?></div>
            <div class="stat-label"><i class="fa-solid fa-users"></i> Total Users</div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?=$totalStudents?></div>
            <div class="stat-label"><i class="fa-solid fa-user-graduate"></i> Students</div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?=$totalInstructors?></div>
            <div class="stat-label"><i class="fa-solid fa-chalkboard-user"></i> Instructors</div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?=$totalCourses?></div>
            <div class="stat-label"><i class="fa-solid fa-book-open"></i> Courses</div>
        </div>
    </div>

    <h2 class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> Pengguna Terbaru</h2>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Bergabung</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT name, email, role, created_at FROM users ORDER BY user_id DESC LIMIT 8");
                while ($row = $stmt->fetch()):
                    // Logika warna badge role
                    $roleClass = 'role-student';
                    if($row['role'] == 'admin') $roleClass = 'role-admin';
                    if($row['role'] == 'instructor') $roleClass = 'role-instructor';
                ?>
                <tr>
                    <td>
                        <div style="font-weight:600; color:#2c3e50;"><?= htmlspecialchars($row['name']) ?></div>
                    </td>
                    <td style="color:#666;"><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <span class="role-badge <?= $roleClass ?>">
                            <?= htmlspecialchars($row['role']) ?>
                        </span>
                    </td>
                    <td style="color:#888; font-size:0.9rem;">
                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</div>