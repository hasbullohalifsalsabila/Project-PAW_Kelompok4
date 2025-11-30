<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$courses = $pdo->query("SELECT course_id,title FROM courses");

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = $_POST['course'];
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $file_url = trim($_POST['file_url']);
    $position = (int)$_POST['position'];

    $stmt = $pdo->prepare("
        INSERT INTO materials (course_id,title,type,file_url,position)
        VALUES (?,?,?,?,?)
    ");
    $stmt->execute([$course,$title,$type,$file_url,$position]);
    $success = true;
}
?>

<div class="admin-container">
    <h1 class="page-title">Add Material</h1>

    <div class="form-card">
        <?php if ($success): ?>
            <div class="success">Material added successfully!</div>
        <?php endif; ?>

        <form method="POST">
            <label>Course</label>
            <select name="course">
                <?php foreach ($courses as $c): ?>
                    <option value="<?=$c['course_id']?>"><?=$c['title']?></option>
                <?php endforeach ?>
            </select>

            <label>Title</label>
            <input type="text" name="title">

            <label>Type</label>
            <select name="type">
                <option value="video">Video</option>
                <option value="pdf">PDF</option>
                <option value="text">Text</option>
            </select>

            <label>File URL</label>
            <input type="text" name="file_url">

            <label>Position (sorting)</label>
            <input type="number" name="position" value="0">

            <button class="btn-dark">Save</button>
        </form>
    </div>
</div>