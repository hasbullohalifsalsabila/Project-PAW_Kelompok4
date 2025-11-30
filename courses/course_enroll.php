<?php
require "../config/db.php"; // Sesuaikan path jika folder config ada di luar
session_start();

// 1. CEK LOGIN
// Jika user belum login, arahkan ke halaman login
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Silakan login terlebih dahulu untuk mendaftar kursus.'); window.location.href='../auth/login.php';</script>";
    exit;
}

$user_id = $_SESSION['user']['user_id'];
$course_id = $_GET['course'] ?? null;

// Cek apakah ID kursus ada
if (!$course_id) {
    die("Error: ID Kursus tidak ditemukan.");
}

// 2. CEK DUPLIKASI
// Pastikan user belum terdaftar di kursus ini sebelumnya
$check = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$check->execute([$user_id, $course_id]);

if ($check->rowCount() > 0) {
    // Jika sudah terdaftar, kembalikan ke dashboard dengan pesan info
    header("Location: ../student/dashboard.php?enrolled=exist"); 
    exit;
}

// 3. PROSES PENDAFTARAN (INSERT DATABASE)
try {
    // Masukkan data ke tabel enrollments
    // Status default biasanya 'active' atau 'in_progress'
    $sql = "INSERT INTO enrollments (user_id, course_id, enrolled_at, status) VALUES (?, ?, NOW(), 'active')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $course_id]);

    // 4. BERHASIL & REDIRECT
    // Arahkan kembali ke dashboard student dengan pesan sukses
    header("Location: ../student/dashboard.php?free_enroll=success");
    exit;

} catch (PDOException $e) {
    die("Gagal mendaftar kursus: " . $e->getMessage());
}
?>