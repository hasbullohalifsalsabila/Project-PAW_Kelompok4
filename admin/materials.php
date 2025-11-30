<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

// Ambil daftar course
$courses = $pdo->query("SELECT course_id, title FROM courses");

// Filter
$course_id = $_GET['course'] ?? '';

$query = "
    SELECT m.material_id, m.title, m.type, m.position,
           c.title AS course_title
    FROM materials m
    JOIN courses c ON m.course_id = c.course_id
    WHERE 1
";

if ($course_id !== '')
    $query .= " AND m.course_id = :cid";

$query .= " ORDER BY m.course_id, m.position ASC";

$stmt = $pdo->prepare($query);
if ($course_id !== '')
    $stmt->bindValue(':cid', $course_id);
$stmt->execute();
$materials = $stmt->fetchAll();
?>

<div class="admin-container">

    <h1 class="page-title">Manage Materials</h1>

    <div class="filter-bar">
        <form method="GET" class="filter-form">
            <select name="course">
                <option value="">All Courses</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?=$c['course_id']?>" <?=$course_id==$c['course_id']?'selected':''?>>
                        <?=$c['title']?>
                    </option>
                <?php endforeach ?>
            </select>

            <button class="btn-dark">Filter</button>
            <a href="material_add.php" class="btn-add">+ Add Material</a>
        </form>
    </div>

    <div class="table-card">
        <table>
            <tr>
                <th>ID</th>
                <th>Course</th>
                <th>Title</th>
                <th>Type</th>
                <th>Position</th>
                <th>Action</th>
            </tr>

            <?php foreach ($materials as $m): ?>
                <tr>
                    <td><?=$m['material_id']?></td>
                    <td><?=$m['course_title']?></td>
                    <td><?=$m['title']?></td>
                    <td><?=$m['type']?></td>
                    <td><?=$m['position']?></td>
                    <td>
                        <a href="material_edit.php?id=<?=$m['material_id']?>" class="btn-small">Edit</a>
                        <a href="material_delete.php?id=<?=$m['material_id']?>" class="btn-small delete"
                           onclick="return confirm('Delete material?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach ?>

        </table>
    </div>
</div>