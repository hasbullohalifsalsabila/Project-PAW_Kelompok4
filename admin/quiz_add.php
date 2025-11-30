<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$courses = $pdo->query("SELECT course_id,title FROM courses")->fetchAll();
$success = false;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $course = $_POST['course'];
    $title = trim($_POST['title']);

    $stmt = $pdo->prepare("INSERT INTO quizzes (course_id,title) VALUES (?,?)");
    $stmt->execute([$course,$title]);

    $success = true;
}
?>

<div class="admin-container">

    <h1 class="page-title">Add Quiz</h1>

    <div class="form-card">
        <?php if ($success): ?><div class="success">Quiz added!</div><?php endif; ?>

        <form method="POST">

            <label>Course</label>
            <select name="course">
                <?php foreach ($courses as $c): ?>
                    <option value="<?=$c['course_id']?>"><?=$c['title']?></option>
                <?php endforeach ?>
            </select>

            <label>Quiz Title</label>
            <input type="text" name="title">

            <button class="btn-dark">Save</button>
        </form>
    </div>
</div>