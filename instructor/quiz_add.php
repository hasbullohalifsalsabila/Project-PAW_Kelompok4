<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$cid = $_GET['course'];
$edit = $_GET['edit'] ?? null;

$quiz = null;
$questions = [];

if ($edit) {
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id=?");
    $stmt->execute([$edit]);
    $quiz = $stmt->fetch();

    $qstmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id=?");
    $qstmt->execute([$edit]);
    $questions = $qstmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = $_POST['title'];

    if ($edit) {
        $upd = $pdo->prepare("UPDATE quizzes SET title=? WHERE quiz_id=?");
        $upd->execute([$title, $edit]);
    } else {
        $ins = $pdo->prepare("INSERT INTO quizzes (course_id, title) VALUES (?,?)");
        $ins->execute([$cid, $title]);
        $edit = $pdo->lastInsertId();
    }

    // save questions
    foreach ($_POST['question'] as $i => $qtext) {
        $a = $_POST['a'][$i];
        $b = $_POST['b'][$i];
        $c = $_POST['c'][$i];
        $d = $_POST['d'][$i];
        $correct = $_POST['correct'][$i];

        $insert = $pdo->prepare("
            INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_answer)
            VALUES (?,?,?,?,?,?,?)
        ");
        $insert->execute([$edit, $qtext, $a, $b, $c, $d, $correct]);
    }

    header("Location: quizzes.php?course=$cid");
    exit;
}
?>

<link rel="stylesheet" href="instructor.css">

<div class="inst-container">

<h1 class="page-title"><?= $edit ? "Edit Quiz" : "Add Quiz" ?></h1>

<form method="POST" class="form-box">

<label>Title</label>
<input type="text" name="title" required value="<?=$quiz['title'] ?? ''?>">

<h3>Add Questions</h3>
<div id="question-box"></div>

<button type="button" class="btn-dark-sm" onclick="addQuestion()">+ Add Question</button>
<br><br>

<button class="btn-dark">Save Quiz</button>

</form>

<script>
function addQuestion() {
    const box = document.getElementById('question-box');
    box.innerHTML += `
    <div class="q-block">
        <label>Question</label>
        <input type="text" name="question[]" required>

        <label>Option A</label><input type="text" name="a[]" required>
        <label>Option B</label><input type="text" name="b[]" required>
        <label>Option C</label><input type="text" name="c[]" required>
        <label>Option D</label><input type="text" name="d[]" required>

        <label>Correct Answer</label>
        <select name="correct[]" required>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select>
        <hr>
    </div>`;
}
</script>

</div></body></html>