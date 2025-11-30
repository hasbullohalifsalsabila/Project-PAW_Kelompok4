<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_id DESC")->fetchAll();
?>

<div class="admin-container">

    <h1 class="page-title">Manage Categories</h1>

    <a href="category_add.php" class="btn-add">+ Add Category</a>

    <div class="table-card">
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th width="150">Action</th>
            </tr>

            <?php foreach ($categories as $c): ?>
            <tr>
                <td><?=$c['category_id']?></td>
                <td><?=$c['name']?></td>
                <td><?=$c['description']?></td>
                <td>
                    <a href="category_edit.php?id=<?=$c['category_id']?>" class="btn-small">Edit</a>
                    <a href="category_delete.php?id=<?=$c['category_id']?>" class="btn-small delete"
                       onclick="return confirm('Delete category?')">Delete</a>
                </td>
            </tr>
            <?php endforeach ?>
        </table>
    </div>

</div>