<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$instructors = $pdo->query("
    SELECT u.user_id, u.name, u.email, ip.bio 
    FROM users u
    LEFT JOIN instructors_profile ip ON u.user_id = ip.instructor_id
    WHERE u.role='instructor'
")->fetchAll();
?>

<div class="admin-container">

    <h1 class="page-title">Manage Instructors</h1>

    <a href="instructor_add.php" class="btn-add">+ Add Instructor</a>

    <div class="table-card">
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Bio</th>
                <th width="200">Actions</th>
            </tr>

            <?php foreach ($instructors as $i): ?>
            <tr>
                <td><?=$i['user_id']?></td>
                <td><?=$i['name']?></td>
                <td><?=$i['email']?></td>
                <td><?=substr($i['bio'],0,50)?>...</td>
                <td>
                    <a href="instructor_edit.php?id=<?=$i['user_id']?>" class="btn-small">Edit</a>
                    <a href="instructor_delete.php?id=<?=$i['user_id']?>" class="btn-small delete"
                    onclick="return confirm('Delete instructor?')">Delete</a>
                </td>
            </tr>
            <?php endforeach ?>

        </table>
    </div>

</div>