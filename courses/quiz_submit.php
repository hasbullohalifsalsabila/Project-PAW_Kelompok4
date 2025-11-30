<?php
// quiz_submit.php
session_start();
require '../config/db.php';

// Cek Login & Data Post
if (!isset($_SESSION['user']['user_id']) || !isset($_POST['quiz_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$uid = $_SESSION['user']['user_id'];
$qid = $_POST['quiz_id'];
$answers = $_POST['answers'] ?? []; 

// 1. Ambil Kunci Jawaban
$stmt = $pdo->prepare("SELECT id, correct_answer FROM quiz_questions WHERE quiz_id = ?");
$stmt->execute([$qid]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_soal = count($questions);
$benar = 0;

// 2. Hitung Nilai
foreach ($questions as $q) {
    $qid_soal = $q['id'];
    if (isset($answers[$qid_soal]) && $answers[$qid_soal] === $q['correct_answer']) {
        $benar++;
    }
}

$final_score = ($total_soal > 0) ? round(($benar / $total_soal) * 100) : 0;

// 3. SIMPAN DATA (PERBAIKAN KOLOM DATABASE)
// Nama kolom disesuaikan dengan screenshot database Anda: 'attempted_at'

$cek = $pdo->prepare("SELECT result_id FROM quiz_results WHERE user_id = ? AND quiz_id = ?");
$cek->execute([$uid, $qid]);
$existing = $cek->fetch();

if ($existing) {
    // Update
    $sql = "UPDATE quiz_results SET 
            score = ?, 
            correct_answers = ?, 
            total_questions = ?, 
            attempted_at = NOW() 
            WHERE result_id = ?";
    $pdo->prepare($sql)->execute([$final_score, $benar, $total_soal, $existing['result_id']]);
} else {
    // Insert
    $sql = "INSERT INTO quiz_results (user_id, quiz_id, score, correct_answers, total_questions, attempted_at) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $pdo->prepare($sql)->execute([$uid, $qid, $final_score, $benar, $total_soal]);
}

// 4. Redirect
header("Location: quiz_result.php?score=$final_score&benar=$benar&total=$total_soal&qid=$qid");
exit;
?>