<?php
// quiz_result.php
session_start();
require '../config/db.php';

// Get data from URL
$score = isset($_GET['score']) ? (int)$_GET['score'] : 0;
$benar = isset($_GET['benar']) ? (int)$_GET['benar'] : 0;
$total = isset($_GET['total']) ? (int)$_GET['total'] : 0;
$qid   = $_GET['qid'] ?? 0;

// Fetch Quiz Title & Course ID for the "Back" button
$stmt = $pdo->prepare("SELECT title, course_id FROM quizzes WHERE quiz_id = ?");
$stmt->execute([$qid]);
$quiz_info = $stmt->fetch();
$course_id = $quiz_info['course_id'] ?? 0;

// Determine Message & Color based on score
if ($score >= 90) {
    $emoji = "🏆";
    $title = "Outstanding!";
    $msg   = "You have mastered this material perfectly.";
    $color = "#27ae60"; // Green
} elseif ($score >= 70) {
    $emoji = "🎉";
    $title = "Great Job!";
    $msg   = "You passed the quiz successfully.";
    $color = "#2980b9"; // Blue
} else {
    $emoji = "💪";
    $title = "Keep Going!";
    $msg   = "Don't give up, review the material and try again.";
    $color = "#e67e22"; // Orange
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; }
        body { 
            background-color: #fdfbf7; /* Warm Theme Background */
            font-family: 'Inter', sans-serif; 
            margin: 0; padding: 0;
            display: flex; justify-content: center; align-items: center;
            min-height: 100vh;
            color: #2c3e50;
        }

        .result-container {
            background: white;
            width: 100%;
            max-width: 500px;
            padding: 50px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border: 1px solid #f0ece3;
            text-align: center;
            animation: popIn 0.5s ease;
        }

        @keyframes popIn { 0% {transform: scale(0.9); opacity: 0;} 100% {transform: scale(1); opacity: 1;} }

        .score-circle {
            width: 150px; height: 150px;
            background: <?= $color ?>;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 30px auto;
            color: white;
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            font-weight: 700;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            position: relative;
        }
        
        .score-circle span { font-size: 1rem; position: absolute; top: 25px; right: 35px; opacity: 0.8; }

        .result-emoji { font-size: 3rem; margin-bottom: 10px; display: block; }
        
        .result-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; margin: 0 0 10px 0;
            color: #2c3e50;
        }

        .result-msg { color: #7f8c8d; font-size: 1rem; line-height: 1.6; margin-bottom: 30px; }

        .stat-grid {
            display: flex; justify-content: center; gap: 15px; margin-bottom: 40px;
            background: #f9f9f9; padding: 15px; border-radius: 12px;
        }
        .stat-item { padding: 0 15px; text-align: center; }
        .stat-label { font-size: 0.75rem; color: #999; text-transform: uppercase; letter-spacing: 1px; }
        .stat-val { font-size: 1.2rem; font-weight: 700; color: #2c3e50; }
        .stat-item:first-child { border-right: 1px solid #ddd; }

        .btn-home {
            background-color: #2c3e50; color: white;
            text-decoration: none; padding: 15px 40px;
            border-radius: 50px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 10px;
            transition: 0.3s; box-shadow: 0 5px 15px rgba(44, 62, 80, 0.2);
        }
        .btn-home:hover { background-color: #c49b63; transform: translateY(-2px); }

    </style>
</head>
<body>

    <div class="result-container">
        <div class="result-emoji"><?= $emoji ?></div>
        
        <div class="score-circle">
            <?= $score ?><span>%</span>
        </div>

        <h1 class="result-title"><?= $title ?></h1>
        <p class="result-msg"><?= $msg ?></p>

        <div class="stat-grid">
            <div class="stat-item">
                <div class="stat-val"><?= $benar ?> / <?= $total ?></div>
                <div class="stat-label">Correct Answers</div>
            </div>
            <div class="stat-item">
                <div class="stat-val"><?= htmlspecialchars($quiz_info['title'] ?? 'Kuis') ?></div>
                <div class="stat-label">Quiz Title</div>
            </div>
        </div>

        <a href="view.php?id=<?=$course_id?>" class="btn-home">
            <i class="fa-solid fa-arrow-left"></i> Back to Module
        </a>
    </div>

</body>
</html>