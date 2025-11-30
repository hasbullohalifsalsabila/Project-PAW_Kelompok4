<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id=?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) die("Category not found.");

$success = false;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);

    $stmt = $pdo->prepare("UPDATE categories SET name=?, description=? WHERE category_id=?");
    $stmt->execute([$name,$desc,$id]);

    $success = true;
}
?>

<div class="admin-container">

    <h1 class="page-title">Edit Category</h1>

    <div class="form-card">
        <?php if ($success): ?><div class="success">Saved!</div><?php endif; ?>

        <form method="POST">

            <label>Name</label>
            <input type="text" name="name" value="<?=$c['name']?>">

            <label>Description</label>
            <textarea name="description" rows="4"><?=$c['description']?></textarea>

            <button class="btn-dark">Save</button>
        </form>
    </div>
</div>