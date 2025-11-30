<?php
require "../config/db.php";
session_start();

// Set header agar outputnya dianggap JSON oleh browser
header('Content-Type: application/json');

// Hanya terima request POST dan User Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user'])) {
    
    $user_id = $_SESSION['user']['user_id'];
    
    // Ambil data JSON yang dikirim oleh JavaScript
    $input = json_decode(file_get_contents('php://input'), true);
    $course_id = $input['course_id'] ?? null;

    if (!$course_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Course ID']);
        exit;
    }

    // 1. Cek apakah sudah terdaftar
    $check = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $check->execute([$user_id, $course_id]);

    if ($check->rowCount() > 0) {
        // Jika sudah ada, tetap kirim sukses agar redirect jalan
        echo json_encode(['status' => 'success', 'message' => 'Already enrolled']);
        exit;
    }

    // 2. Masukkan Data ke Database
    try {
        $sql = "INSERT INTO enrollments (user_id, course_id, enrolled_at, status) VALUES (?, ?, NOW(), 'active')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $course_id]);

        // Kirim sinyal SUKSES ke JavaScript
        echo json_encode(['status' => 'success']);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
}
?>