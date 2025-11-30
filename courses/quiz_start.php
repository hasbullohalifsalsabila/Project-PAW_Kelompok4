<?php
require '../config/db.php';
require '../student/header.php';
require '../student/sidebar.php';

$qid = $_GET['id'];
$uid = $_SESSION['user']['user_id'];

// 1. CEK APAKAH SUDAH PERNAH MENGERJAKAN?
$cek = $pdo->prepare("SELECT score, total_questions, correct_answers FROM quiz_results WHERE user_id = ? AND quiz_id = ?");
$cek->execute([$uid, $qid]);
$hasil = $cek->fetch();

if ($hasil) {
    // Jika sudah ada nilai, langsung lempar ke halaman hasil
    $score = $hasil['score'];
    $benar = $hasil['correct_answers']; // Pastikan kolom ini ada di DB (dari perbaikan sebelumnya)
    $total = $hasil['total_questions']; // Pastikan kolom ini ada di DB
    
    echo "<script>
            alert('Anda sudah mengerjakan kuis ini! Mengalihkan ke halaman hasil...');
            window.location.href = 'quiz_result.php?score=$score&benar=$benar&total=$total&qid=$qid';
          </script>";
    exit;
}

// ... (Sisa kode quiz_start.php tetap sama seperti sebelumnya) ...
$quiz = $pdo->prepare("SELECT q.*, c.title AS course_title FROM quizzes q JOIN courses c ON q.course_id=c.course_id WHERE quiz_id=?");
$quiz->execute([$qid]);
$data = $quiz->fetch();

if (!$data) die("Kuis tidak ditemukan.");

// Hitung Jumlah Soal
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = ?");
$stmt_count->execute([$qid]);
$total_soal = $stmt_count->fetchColumn();

$durasi_detik = $total_soal * 30; 
$durasi_menit = floor($durasi_detik / 60);
$sisa_detik   = $durasi_detik % 60;

$qid = $_GET['id'];

// 1. Ambil Detail Kuis
$quiz = $pdo->prepare("
    SELECT q.*, c.title AS course_title
    FROM quizzes q
    JOIN courses c ON q.course_id=c.course_id
    WHERE quiz_id=?
");
$quiz->execute([$qid]);
$data = $quiz->fetch();

if (!$data) die("Kuis tidak ditemukan.");

// 2. Hitung Jumlah Soal untuk Menentukan Durasi
// Logika: 1 Soal = 30 Detik
$stmt_count = $pdo->prepare("SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = ?");
$stmt_count->execute([$qid]);
$total_soal = $stmt_count->fetchColumn();

$durasi_detik = $total_soal * 30; // 30 detik per soal
$durasi_menit = floor($durasi_detik / 60);
$sisa_detik   = $durasi_detik % 60;
?>

<style>
    body { background-color: #fdfbf7; font-family: 'Inter', sans-serif; }
    
    .quiz-container {
        margin-left: 280px; /* Sesuaikan sidebar */
        padding: 50px;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 80vh;
    }

    .quiz-card {
        background: white;
        width: 100%;
        max-width: 600px;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #f0ece3;
        text-align: center;
    }

    .page-title {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .quiz-meta {
        background: #fff8e1;
        border: 1px solid #ffe0b2;
        color: #e65100;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
        text-align: left;
        font-size: 0.95rem;
    }

    .quiz-meta div { margin-bottom: 8px; }
    .quiz-meta i { margin-right: 10px; width: 20px; text-align: center; }

    .btn-dark {
        background-color: #2c3e50;
        color: white;
        padding: 15px 40px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        display: inline-block;
        transition: 0.3s;
        font-size: 1.1rem;
    }
    .btn-dark:hover {
        background-color: #c49b63;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(196, 155, 99, 0.3);
    }
</style>

<div class="quiz-container">

    <div class="quiz-card">
        <h1 class="page-title"><?= htmlspecialchars($data['title']) ?></h1>
        <p style="color:#666;">Kelas: <strong><?= htmlspecialchars($data['course_title']) ?></strong></p>

        <div class="quiz-meta">
            <div><i class="fa-solid fa-list-ol"></i> Jumlah Soal: <strong><?= $total_soal ?> Butir</strong></div>
            <div><i class="fa-solid fa-clock"></i> Batas Waktu: <strong><?= $durasi_menit ?> Menit <?= $sisa_detik > 0 ? $sisa_detik.' Detik' : '' ?></strong></div>
            <div><i class="fa-solid fa-stopwatch"></i> Aturan: <strong>30 Detik / Soal</strong></div>
        </div>

        <p style="margin-bottom: 30px; color:#555;">
            Pastikan koneksi internet Anda stabil. Waktu akan berjalan otomatis begitu Anda menekan tombol mulai.
        </p>

        <a href="quiz_take.php?id=<?=$qid?>" class="btn-dark">
            <i class="fa-solid fa-play"></i> Mulai Kerjakan
        </a>
    </div>

</div>