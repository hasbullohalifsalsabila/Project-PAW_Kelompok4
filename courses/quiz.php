<?php
require '../config/db.php';
require '../student/header.php';
require '../student/sidebar.php';

$cid = $_GET['course'];
$uid = $_SESSION['user']['user_id'];

// Check enrollment
$enrolled = $pdo->prepare("SELECT * FROM enrollments WHERE user_id=? AND course_id=?");
$enrolled->execute([$uid, $cid]);
if (!$enrolled->fetch()) die("You are not enrolled in this course.");

// Load quizzes
$quizzes = $pdo->prepare("SELECT * FROM quizzes WHERE course_id=?");
$quizzes->execute([$cid]);
$quizzes = $quizzes->fetchAll();
?>

<link rel="stylesheet" href="quiz.css">

<div class="quiz-container">

<h1 class="page-title">Course Quizzes</h1>

<div class="quiz-list">

<?php foreach ($quizzes as $q): ?>
    <div class="quiz-card">
        <h3><?=$q['title']?></h3>
        <a href="quiz_start.php?id=<?=$q['quiz_id']?>" class="btn-dark">Start Quiz</a>
    </div>
<?php endforeach; ?>

</div>

</div>

</div></body></html>