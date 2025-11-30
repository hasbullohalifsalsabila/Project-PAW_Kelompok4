<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'instructor') {
    header("Location: ../auth/login.php");
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Instructor Panel</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Reset */
    * { box-sizing: border-box; }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Inter', sans-serif;
        background-color: #fdfbf7;
    }

    /* --- TOPBAR STYLE (SAMA PERSIS DENGAN STUDENT) --- */
    .topbar {
        position: fixed;
        top: 0;
        left: 260px; 
        width: calc(100% - 260px); 
        height: 80px;

        background-color: #fdfbf7;
        display: flex;
        justify-content: space-between;
        align-items: center;

        padding: 0 40px;
        border-bottom: 2px solid #e0dbd0;
        z-index: 999;
    }

    /* Title */
    .topbar-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
    }

    /* Right side */
    .topbar-user {
        display: flex;
        gap: 15px;
        align-items: center;
        font-size: 0.95rem;
        font-weight: 600;
    }

    /* Buttons */
    .btn-nav {
        text-decoration: none;
        padding: 10px 22px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-home {
        background-color: #2c3e50; 
        color: white;
    }
    .btn-home:hover {
        background-color: #34495e;
        transform: translateY(-2px);
    }

    .btn-logout {
        background-color: #fff;
        color: #c0392b;
        border: 1px solid #c0392b;
    }
    .btn-logout:hover {
        background-color: #c0392b;
        color: white;
    }

    /* Responsive */
    @media (max-width: 900px) {
        .topbar {
            left: 0;
            width: 100%;
            padding: 0 20px;
        }
        .topbar-title { font-size: 1.2rem; }
    }
</style>
</head>
<body>

<!-- NAVBAR INSTRUCTOR (SAMA DENGAN STUDENT) -->
<nav class="topbar">
    <div class="topbar-title">Instructor</div>

    <div class="topbar-user">

        <a class="btn-nav btn-home" href="dashboard.php">
            <i class="fa-solid fa-house"></i> Home
        </a>

        <a class="btn-nav btn-logout" href="../auth/logout.php">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</nav>

<div style="height:80px;"></div> <!-- SPACER agar konten tidak tertutup navbar -->
