<?php
// Dapatkan nama file saat ini untuk penanda menu aktif
$current_page = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['user']['role'] ?? 'student'; // Default role
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="sidebar">
    
    <div class="brand">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>EduCourse</span>
    </div>

    <nav class="menu-list">
        
        <?php if ($role === 'admin'): ?>
            <a href="dashboard.php" class="menu-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="users.php" class="menu-item <?= (strpos($current_page, 'user') !== false) ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> Manage Users
            </a>
            <a href="courses.php" class="menu-item <?= (strpos($current_page, 'course') !== false) ? 'active' : '' ?>">
                <i class="fa-solid fa-book-open"></i> Manage Courses
            </a>
            <a href="payments.php" class="menu-item <?= $current_page == 'payments.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-wallet"></i> Payments
            </a>
            <a href="certificates.php" class="menu-item <?= $current_page == 'certificates.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-certificate"></i> Certificates
            </a>
            <a href="announcements.php" class="menu-item <?= (strpos($current_page, 'announcement') !== false) ? 'active' : '' ?>">
                <i class="fa-solid fa-bullhorn"></i> Announcements
            </a>
            <a href="reports.php" class="menu-item <?= $current_page == 'reports.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chart-pie"></i> Reports
            </a>

        <?php elseif ($role === 'instructor'): ?>
            <a href="dashboard.php" class="menu-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
            <a href="my_courses.php" class="menu-item <?= $current_page == 'my_courses.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-chalkboard-user"></i> Kelas Saya
            </a>
            <a href="student_grades.php" class="menu-item <?= $current_page == 'student_grades.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-marker"></i> Input Nilai
            </a>

        <?php else: // STUDENT ?>
            <a href="dashboard.php" class="menu-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>

            <a href="my_courses.php" class="menu-item <?= $current_page == 'my_courses.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-book"></i> My Courses
            </a>

            <a href="announcements.php" class="menu-item <?= $current_page == 'announcements.php' ? 'active' : '' ?>">
                <i class="fa-regular fa-bell"></i> Announcement
            </a>

            <!-- Perbaikan di sini: Ikon Certificate dan Logika Active -->
            <a href="my_certificates.php" class="menu-item <?= $current_page == 'my_certificates.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-certificate"></i> Sertificate
            </a>
        <?php endif; ?>

    </nav>


</div>

<style>
    /* Reset Dasar */
    * { box-sizing: border-box; }

    /* KONTAINER SIDEBAR */
    .sidebar {
        width: 260px;
        height: 100vh;
        background-color: #fcfaf5; /* Warna Krem Sesuai Gambar */
        position: fixed;
        top: 0;
        left: 0;
        padding: 35px 25px;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #e5e0d8; /* Garis tipis di kanan */
        z-index: 1000;
    }

    /* LOGO / BRAND */
    .brand {
        font-family: 'Times New Roman', serif; /* Font Serif agar elegan */
        font-size: 24px;
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 50px; /* Jarak jauh ke menu */
        display: flex;
        align-items: center;
        gap: 12px;
        padding-left: 5px;
    }
    .brand i { font-size: 28px; }

    /* LIST MENU */
    .menu-list {
        flex-grow: 1; /* Isi ruang kosong agar logout terdorong ke bawah */
        display: flex;
        flex-direction: column;
        gap: 8px; /* Jarak antar menu */
    }

    /* ITEM MENU (TOMBOL) */
    .menu-item {
        display: flex;
        align-items: center;
        padding: 14px 20px;
        text-decoration: none;
        color: #6e6e6e; /* Warna teks abu tua */
        font-family: sans-serif;
        font-size: 15px;
        font-weight: 600; /* Agak tebal */
        border-radius: 14px; /* Sudut melengkung */
        transition: all 0.3s ease;
    }

    /* ICON DI KIRI */
    .menu-item i {
        width: 24px;
        margin-right: 15px;
        font-size: 18px;
        text-align: center;
    }

    /* EFEK HOVER (Saat mouse lewat) */
    .menu-item:hover {
        background-color: #eeebe5; /* Sedikit lebih gelap dari background */
        color: #1a1a1a;
    }

    /* MENU AKTIF (Hitam Pekat) */
    .menu-item.active {
        background-color: #1a1a1a; /* Hitam pekat */
        color: #ffffff; /* Putih */
        box-shadow: 0 8px 20px rgba(0,0,0,0.15); /* Bayangan lembut */
        transform: translateY(-1px); /* Sedikit naik */
    }

    /* LOGOUT BUTTON */
    .menu-item.logout {
        margin-top: auto; /* Dorong ke paling bawah */
        color: #c0392b;
    }
    .menu-item.logout:hover {
        background-color: #fae3e1;
        color: #a93226;
    }
</style>