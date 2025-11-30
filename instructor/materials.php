<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$iid = $_SESSION['user']['user_id'];
$cid = $_GET['course'];

// Pastikan instructor hanya bisa melihat materinya sendiri
$check = $pdo->prepare("SELECT * FROM courses WHERE course_id=? AND instructor_id=?");
$check->execute([$cid, $iid]);
if (!$check->fetch()) die("Access denied.");

// ambil materi
$materials = $pdo->prepare("SELECT * FROM materials WHERE course_id=? ORDER BY position ASC");
$materials->execute([$cid]);
$materials = $materials->fetchAll();
?>

<link rel="stylesheet" href="instructor.css">

<div class="inst-container">

<h1 class="page-title">Materials</h1>

<a href="material_add.php?course=<?=$cid?>" class="btn-dark">+ Add Material</a>

<div class="material-list">
<?php foreach ($materials as $m): ?>
<div class="material-card">
    <h3><?=$m['title']?></h3>
    <p>Type: <?=$m['type']?></p>
    <p>File: <?=$m['file_url'] ? 'Uploaded' : 'Text Content'?></p>

    <a class="btn-dark-sm" href="material_add.php?course=<?=$cid?>&edit=<?=$m['material_id']?>">Edit</a>
</div>
<?php endforeach; ?>
</div>

</div></body></html>