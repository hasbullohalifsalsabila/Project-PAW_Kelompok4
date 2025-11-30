<?php
require '../config/db.php';
session_start();

// Cek Login Instruktur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'instructor') {
    die("Akses ditolak.");
}

if (isset($_POST['graduate'])) {
    $eid = $_POST['enroll_id'];
    
    // Update status menjadi 'graduated'
    $stmt = $pdo->prepare("UPDATE enrollments SET status = 'graduated' WHERE enroll_id = ?");
    
    if ($stmt->execute([$eid])) {
        echo "<script>alert('Siswa berhasil dinyatakan LULUS!'); window.history.back();</script>";
    } else {
        echo "Gagal update database.";
    }
}
?>