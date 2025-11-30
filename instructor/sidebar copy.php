<?php
// Pastikan variabel $current telah didefinisikan sebelumnya, biasanya di header.php atau file yang menyertakan sidebar ini.
$current = basename($_SERVER["PHP_SELF"]);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="sidebar">
    
    <div class="brand">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>EduCourse</span>
    </div>

    <nav class="menu-list">
        
        <a href="dashboard.php"
            class="menu-item <?= $current=='dashboard.php'?'active':'' ?>">
            <i class="fa-solid fa-chart-line"></i>
            Dashboard
        </a>

        <a href="courses.php"
            class="menu-item <?= $current=='courses.php'?'active':'' ?>">
            <i class="fa-solid fa-book-open"></i>
            My Classes
        </a>

        <a href="student_grades.php"
            class="menu-item <?= $current=='student_grades.php'?'active':'' ?>">
            <i class="fa-solid fa-square-poll-vertical"></i>
            Input Grades
        </a>

        <a href="students.php"
            class="menu-item <?= $current=='students.php'?'active':'' ?>">
            <i class="fa-solid fa-users"></i>
            Students
        </a>

    </nav>
</div>

<style>
    /* Reset Dasar */
    * { 
        box-sizing: border-box; 
    }

    /* =========================================
       1. KONTAINER SIDEBAR
       ========================================= */
    .sidebar {
        width: 250px; /* Sedikit disempitkan agar lebih compact */
        height: 100vh;
        background-color: #fcfaf5; /* Warna Krem Sesuai Gambar */
        position: fixed;
        top: 0;
        left: 0;
        padding: 35px 20px;
        display: flex;
        flex-direction: column;
        border-right: 1px solid #e5e0d8; 
        z-index: 1000;
    }

    /* =========================================
       2. LOGO / BRAND
       ========================================= */
    .brand {
        font-family: 'Playfair Display', serif; /* Menggunakan font serif populer */
        font-size: 26px; /* Sedikit dibesarkan */
        font-weight: bold;
        color: #1a1a1a;
        margin-bottom: 50px; 
        display: flex;
        align-items: center;
        gap: 10px;
        padding-left: 5px;
    }
    .brand i { 
        font-size: 28px; 
        color: #34495e; /* Warna ikon agar menonjol */
    }

    /* =========================================
       3. LIST MENU & ITEM
       ========================================= */
    .menu-list {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 6px; /* Jarak antar menu lebih rapat */
    }

    /* ITEM MENU (TOMBOL) */
    .menu-item {
        display: flex;
        align-items: center;
        padding: 12px 15px; /* Padding disesuaikan */
        text-decoration: none;
        color: #6e6e6e; 
        font-family: 'Inter', sans-serif;
        font-size: 15px;
        font-weight: 500; /* Dibuat sedikit lebih tipis */
        border-radius: 10px; /* Sudut melengkung */
        transition: all 0.2s ease;
    }

    /* ICON DI KIRI */
    .menu-item i {
        width: 20px; /* Lebar ikon lebih kecil */
        margin-right: 15px;
        font-size: 16px;
        text-align: center;
    }

    /* EFEK HOVER */
    .menu-item:hover {
        background-color: #f0ebe4; /* Warna hover lebih lembut */
        color: #2c3e50;
    }

    /* MENU AKTIF */
    .menu-item.active {
        background-color: #2c3e50; /* Hitam kebiruan pekat (lebih modern) */
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1); /* Bayangan lebih ringan */
    }

    /* LOGOUT BUTTON - Dorong ke bawah dan beri gaya khusus */
    .menu-item.logout {
        margin-top: 50px; /* Jarak dari item di atasnya */
        color: #c0392b; /* Merah gelap */
    }
    .menu-item.logout:hover {
        background-color: #fae3e1; /* Hover merah muda */
        color: #a93226;
    }
    .menu-item.logout i {
        color: inherit; /* Ikon menggunakan warna teks logout */
    }
</style>