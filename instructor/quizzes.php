<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$iid = $_SESSION['user']['user_id'];
$cid = $_GET['course'];

// cek apakah kursus milik instructor
$check = $pdo->prepare("SELECT * FROM courses WHERE course_id=? AND instructor_id=?");
$check->execute([$cid, $iid]);
if (!$check->fetch()) die("Access denied.");

$quizzes = $pdo->prepare("SELECT * FROM quizzes WHERE course_id=?");
$quizzes->execute([$cid]);
$quizzes = $quizzes->fetchAll();
?>

<link rel="stylesheet" href="instructor.css">

<div class="inst-container">

<h1 class="page-title">Quizzes</h1>

<a href="quiz_add.php?course=<?=$cid?>" class="btn-dark">+ Add Quiz</a>

<div class="quiz-list">
<?php foreach ($quizzes as $q): ?>
<div class="quiz-card">
    <h3><?=$q['title']?></h3>

    <a class="btn-dark-sm" href="quiz_add.php?course=<?=$cid?>&edit=<?=$q['quiz_id']?>">Edit</a>
</div>
<?php endforeach; ?>
</div>

</div></body></html>