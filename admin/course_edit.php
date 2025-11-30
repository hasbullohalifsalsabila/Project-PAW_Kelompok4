<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id=?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) die("Course tidak ditemukan");

$instructors = $pdo->query("SELECT user_id,name FROM users WHERE role='instructor'");

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $level = $_POST['level'];
    $instructor = $_POST['instructor'];

    $stmt = $pdo->prepare("UPDATE courses SET title=?,description=?,price=?,level=?,instructor_id=? WHERE course_id=?");
    $stmt->execute([$title,$description,$price,$level,$instructor,$id]);
    $success = true;
}
?>

<div class="admin-container">
    <h1 class="page-title">Edit Course</h1>

    <div class="form-card">
        <?php if ($success): ?>
            <div class="success">Course updated!</div>
        <?php endif; ?>

        <form method="POST">

            <label>Title</label>
            <input type="text" name="title" value="<?=$course['title']?>">

            <label>Description</label>
            <textarea name="description" rows="5"><?=$course['description']?></textarea>

            <label>Price</label>
            <input type="number" name="price" value="<?=$course['price']?>">

            <label>Level</label>
            <select name="level">
                <option value="beginner" <?=$course['level']=='beginner'?'selected':''?>>Beginner</option>
                <option value="intermediate" <?=$course['level']=='intermediate'?'selected':''?>>Intermediate</option>
                <option value="advanced" <?=$course['level']=='advanced'?'selected':''?>>Advanced</option>
            </select>

            <label>Instructor</label>
            <select name="instructor">
                <?php foreach ($instructors as $i): ?>
                    <option value="<?=$i['user_id']?>" <?=$course['instructor_id']==$i['user_id']?'selected':''?>><?=$i['name']?></option>
                <?php endforeach; ?>
            </select>

            <button class="btn-dark">Save Changes</button>
        </form>
    </div>
</div><?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id=?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) die("Course tidak ditemukan");

$instructors = $pdo->query("SELECT user_id,name FROM users WHERE role='instructor'");

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $level = $_POST['level'];
    $instructor = $_POST['instructor'];

    $stmt = $pdo->prepare("UPDATE courses SET title=?,description=?,price=?,level=?,instructor_id=? WHERE course_id=?");
    $stmt->execute([$title,$description,$price,$level,$instructor,$id]);
    $success = true;
}
?>

<div class="admin-container">
    <h1 class="page-title">Edit Course</h1>

    <div class="form-card">
        <?php if ($success): ?>
            <div class="success">Course updated!</div>
        <?php endif; ?>

        <form method="POST">

            <label>Title</label>
            <input type="text" name="title" value="<?=$course['title']?>">

            <label>Description</label>
            <textarea name="description" rows="5"><?=$course['description']?></textarea>

            <label>Price</label>
            <input type="number" name="price" value="<?=$course['price']?>">

            <label>Level</label>
            <select name="level">
                <option value="beginner" <?=$course['level']=='beginner'?'selected':''?>>Beginner</option>
                <option value="intermediate" <?=$course['level']=='intermediate'?'selected':''?>>Intermediate</option>
                <option value="advanced" <?=$course['level']=='advanced'?'selected':''?>>Advanced</option>
            </select>

            <label>Instructor</label>
            <select name="instructor">
                <?php foreach ($instructors as $i): ?>
                    <option value="<?=$i['user_id']?>" <?=$course['instructor_id']==$i['user_id']?'selected':''?>><?=$i['name']?></option>
                <?php endforeach; ?>
            </select>

            <button class="btn-dark">Save Changes</button>
        </form>
    </div>
</div>