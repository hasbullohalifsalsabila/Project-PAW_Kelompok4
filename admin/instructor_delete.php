<?php
require '../config/db.php';

// 1. Ambil ID dari URL
$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // 2. Cek apakah user benar-benar instruktur (Demi keamanan)
        $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user && $user['role'] === 'instructor') {
            // 3. Hapus Instruktur
            // Catatan: Jika database Anda disetting ON DELETE CASCADE, 
            // maka semua kursus, materi, dan kuis milik instruktur ini akan ikut terhapus otomatis.
            $pdo->prepare("DELETE FROM users WHERE user_id = ?")->execute([$id]);
            
            echo "<script>alert('✅ Instruktur berhasil dihapus.'); window.location='courses.php';</script>";
        } else {
            echo "<script>alert('User bukan instruktur atau tidak ditemukan.'); window.location='courses.php';</script>";
        }

    } catch (PDOException $e) {
        // Error biasanya terjadi jika ada data terkait yang menahan penghapusan (Constraint)
        echo "<script>alert('Gagal menghapus: Instruktur ini masih memiliki data aktif yang terikat.'); window.location='courses.php';</script>";
    }
} else {
    header("Location: courses.php");
}
?>