<?php
require "../config/db.php";
require "header.php";
require "sidebar.php";

$instructor_id = $_SESSION['user']['user_id'];

// ambil semua course instructor
$stmt = $pdo->prepare("SELECT * FROM courses WHERE instructor_id=?");
$stmt->execute([$instructor_id]);
$courses = $stmt->fetchAll();
?>

<h1>Manage Subclasses</h1>

<?php foreach($courses as $course): ?>

<h2><?=$course['title']?></h2>

<a class="btn-dark" href="subclass_add.php?course=<?=$course['course_id']?>">
 + Tambah Subclass
</a>

<br><br>

<table class="table">
    <tr>
        <th>ID</th>
        <th>Nama Subclass</th>
        <th>Jadwal</th>
        <th>Kapasitas</th>
    </tr>

<?php
$sub = $pdo->prepare("SELECT * FROM course_subclasses WHERE course_id=?");
$sub->execute([$course['course_id']]);

foreach($sub as $s):
?>
    <tr>
        <td><?=$s['subclass_id']?></td>
        <td><?=$s['name']?></td>
        <td><?=$s['schedule']?></td>
        <td><?=$s['capacity']?></td>
    </tr>
<?php endforeach; ?>

</table>

<br><hr><br>

<?php endforeach; ?>

<?php require "footer.php"; ?>
