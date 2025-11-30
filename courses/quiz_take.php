<?php
// quiz_take.php
session_start();
require '../config/db.php';

// 1. CEK KEAMANAN
if (!isset($_SESSION['user']['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: my_courses.php");
    exit;
}

$qid = $_GET['id'];
$uid = $_SESSION['user']['user_id'];

// 2. AMBIL DATA KUIS
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id=?");
$stmt->execute([$qid]);
$quiz = $stmt->fetch();

if (!$quiz) die("Kuis tidak ditemukan.");

// 3. AMBIL SOAL (Diacak)
$stmt_q = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id=? ORDER BY RAND()");
$stmt_q->execute([$qid]);
$questions = $stmt_q->fetchAll();

// 4. HITUNG DURASI (30 Detik Per Soal)
$total_questions = count($questions);
$duration_seconds = $total_questions * 30; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ujian: <?= htmlspecialchars($quiz['title']) ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* CSS RESET */
        * { box-sizing: border-box; }
        body { 
            background-color: #fdfbf7; /* Tema Krem Hangat */
            font-family: 'Inter', sans-serif; 
            margin: 0; padding: 0;
            color: #2c3e50;
        }

        /* HEADER FOKUS (STICKY) */
        .quiz-header {
            background: white;
            height: 70px;
            padding: 0 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; justify-content: space-between; align-items: center;
        }
        .quiz-title {
            font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.2rem;
            color: #2c3e50;
            width: 30%; /* Space kiri */
        }
        
        /* TIMER STYLE */
        .timer-badge {
            background-color: #2c3e50;
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: background-color 0.3s;
        }
        .timer-badge.urgent {
            background-color: #c0392b; /* Merah jika waktu mau habis */
            animation: pulse 1s infinite;
        }

        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

        .btn-quit {
            text-decoration: none; color: #c0392b; font-weight: 600; font-size: 0.9rem;
            border: 1px solid #e74c3c; padding: 8px 20px; border-radius: 20px;
            transition: 0.2s;
            width: 30%; /* Space kanan */
            text-align: right;
            display: flex; justify-content: flex-end;
        }
        .btn-quit span { cursor: pointer; }
        .btn-quit:hover span { text-decoration: underline; }

        /* CONTAINER UTAMA */
        .quiz-wrapper {
            max-width: 800px;
            margin: 100px auto 60px auto; /* Jarak dari header fixed */
            padding: 0 20px;
        }

        /* KARTU PERTANYAAN */
        .question-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid #f0ece3;
        }

        .q-number {
            font-size: 0.85rem; color: #c49b63; font-weight: 800; 
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;
        }

        .q-text {
            font-size: 1.15rem; font-weight: 600; line-height: 1.6;
            margin-bottom: 20px; color: #2c3e50;
        }

        /* PILIHAN JAWABAN */
        .options-grid {
            display: flex; flex-direction: column; gap: 12px;
        }

        .option-label {
            display: flex; align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
        }

        .option-label:hover {
            background-color: #fff8e1; /* Sorot kuning muda */
            border-color: #c49b63;
        }

        .option-label input[type="radio"] {
            accent-color: #c49b63; 
            transform: scale(1.3);
            margin-right: 15px;
        }

        .opt-text { font-size: 1rem; color: #555; }

        /* TOMBOL SUBMIT */
        .submit-bar {
            text-align: center; margin-top: 40px; padding-bottom: 50px;
        }
        .btn-submit {
            background-color: #2c3e50; color: white;
            border: none; padding: 15px 60px;
            font-size: 1.1rem; font-weight: 700;
            border-radius: 50px; cursor: pointer;
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.3);
            transition: 0.3s;
        }
        .btn-submit:hover {
            background-color: #c49b63;
            transform: translateY(-2px);
        }

        /* Responsif HP */
        @media (max-width: 600px) {
            .quiz-title { display: none; } /* Sembunyikan judul di HP biar timer muat */
            .quiz-header { justify-content: center; }
            .btn-quit { position: absolute; right: 20px; border: none; width: auto; }
        }

    </style>
</head>
<body>

    <div class="quiz-header">
        <div class="quiz-title">
            <i class="fa-solid fa-pen-to-square"></i> <?= htmlspecialchars($quiz['title']) ?>
        </div>

        <div class="timer-badge" id="timerBadge">
            <i class="fa-solid fa-stopwatch"></i>
            <span id="timeDisplay">00:00</span>
        </div>

        <div class="btn-quit">
            <span onclick="confirmQuit()">Batal</span>
        </div>
    </div>

    <div class="quiz-wrapper">
        
        <form action="quiz_submit.php" method="POST" id="quizForm">
            <input type="hidden" name="quiz_id" value="<?=$qid?>">

            <?php $no=1; foreach ($questions as $q): ?>
            <div class="question-card">
                <div class="q-number">Soal Nomor <?= $no++ ?></div>
                <div class="q-text"><?= nl2br(htmlspecialchars($q['question_text'])) ?></div>

                <div class="options-grid">
                    <label class="option-label">
                        <input type="radio" name="answers[<?= $q['id'] ?>]" value="A"> 
                        <span class="opt-text">A. <?= htmlspecialchars($q['option_a']) ?></span>
                    </label>
                    
                    <label class="option-label">
                        <input type="radio" name="answers[<?= $q['id'] ?>]" value="B"> 
                        <span class="opt-text">B. <?= htmlspecialchars($q['option_b']) ?></span>
                    </label>
                    
                    <label class="option-label">
                        <input type="radio" name="answers[<?= $q['id'] ?>]" value="C"> 
                        <span class="opt-text">C. <?= htmlspecialchars($q['option_c']) ?></span>
                    </label>
                    
                    <label class="option-label">
                        <input type="radio" name="answers[<?= $q['id'] ?>]" value="D"> 
                        <span class="opt-text">D. <?= htmlspecialchars($q['option_d']) ?></span>
                    </label>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="submit-bar">
                <button class="btn-submit" type="submit" onclick="return confirm('Yakin ingin mengumpulkan jawaban?')">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Jawaban
                </button>
            </div>
        </form>

    </div>

    <script>
        // Ambil durasi dari PHP
        let timeLeft = <?= $duration_seconds ?>; 
        
        const timeDisplay = document.getElementById('timeDisplay');
        const timerBadge = document.getElementById('timerBadge');
        const quizForm = document.getElementById('quizForm');

        function confirmQuit() {
            if(confirm("Yakin ingin keluar? Jawaban tidak akan tersimpan.")) {
                window.history.back();
            }
        }

        const timerInterval = setInterval(() => {
            // Hitung Menit dan Detik
            let m = Math.floor(timeLeft / 60);
            let s = timeLeft % 60;

            // Tambahkan nol di depan jika satuan (misal 5 jadi 05)
            m = m < 10 ? '0' + m : m;
            s = s < 10 ? '0' + s : s;

            timeDisplay.innerText = `${m}:${s}`;

            // Peringatan jika waktu kurang dari 30 detik
            if (timeLeft <= 30) {
                timerBadge.classList.add('urgent');
            }

            // Waktu Habis
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                alert("WAKTU HABIS! Jawaban Anda akan dikirim secara otomatis.");
                quizForm.submit(); // Auto Submit
            }

            timeLeft--;
        }, 1000); // Update setiap 1 detik
    </script>

</body>
</html>