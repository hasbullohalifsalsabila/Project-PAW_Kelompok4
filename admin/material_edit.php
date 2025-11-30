<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM materials WHERE material_id=?");
$stmt->execute([$id]);
$m = $stmt->fetch();

$courses = $pdo->query("SELECT course_id,title FROM courses");

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = $_POST['course'];
    $title = trim($_POST['title']);
    $type = $_POST['type'];
    $file_url = trim($_POST['file_url']);
    $position = (int)$_POST['position'];

    $stmt = $pdo->prepare("UPDATE materials SET course_id=?,title=?,type=?,file_url=?,position=? WHERE material_id=?");
    $stmt->execute([$course,$title,$type,$file_url,$position,$id]);
    $success = true;
}
?>

<div class="admin-container">

    <h1 class="page-title">Edit Material</h1>

    <div class="form-card">

        <?php if ($success): ?>
            <div class="success">Changes saved!</div>
        <?php endif; ?>

        <form method="POST">
            <label>Course</label>
            <select name="course">
                <?php foreach ($courses as $c): ?>
                    <option value="<?=$c['course_id']?>" <?=$m['course_id']==$c['course_id']?'selected':''?>>
                        <?=$c['title']?>
                    </option>
                <?php endforeach ?>
            </select>

            <label>Title</label>
            <input type="text" name="title" value="<?=$m['title']?>">

            <label>Type</label>
            <select name="type">
                <option value="video" <?=$m['type']=='video'?'selected':''?>>Video</option>
                <option value="pdf" <?=$m['type']=='pdf'?'selected':''?>>PDF</option>
                <option value="text" <?=$m['type']=='text'?'selected':''?>>Text</option>
            </select>

            <label>File URL</label>
            <input type="text" name="file_url" value="<?=$m['file_url']?>">

            <label>Position</label>
            <input type="number" name="position" value="<?=$m['position']?>">

            <button class="btn-dark">Save Changes</button>
        </form>
    </div>
</div>