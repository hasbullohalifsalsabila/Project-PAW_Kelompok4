<?php
require "../config/db.php";
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user'])) {
    
    $user_id = $_SESSION['user']['user_id'];
    $course_id = $_POST['course_id'];

    // 1. Cek Duplikasi
    $check = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $check->execute([$user_id, $course_id]);

    if ($check->rowCount() > 0) {
        header("Location: ../student/dashboard.php?enrolled=exist");
        exit;
    }

    // 2. Masukkan Data (Status: Active)
    // Dalam aplikasi nyata, status mungkin 'pending' dulu. 
    // Tapi karena ini simulasi scan QRIS, kita anggap langsung 'active'.
    try {
        $sql = "INSERT INTO enrollments (user_id, course_id, enrolled_at, status) VALUES (?, ?, NOW(), 'active')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $course_id]);

        // Redirect ke Dashboard
        echo "<script>
                alert('Pembayaran Terverifikasi! Selamat belajar.'); 
                window.location.href='../student/dashboard.php';
              </script>";
        exit;

    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }

} else {
    header("Location: ../student/courses_list.php");
}
?>