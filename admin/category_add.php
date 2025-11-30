<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$success = false;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);

    $stmt = $pdo->prepare("INSERT INTO categories (name,description) VALUES (?,?)");
    $stmt->execute([$name,$desc]);
    
    $success = true;
}
?>

<div class="admin-container">

    <h1 class="page-title">Add Category</h1>

    <div class="form-card">
        <?php if ($success): ?>
            <div class="success">Category added!</div>
        <?php endif; ?>

        <form method="POST">
            <label>Name</label>
            <input type="text" name="name">

            <label>Description</label>
            <textarea name="description" rows="4"></textarea>

            <button class="btn-dark">Save</button>
        </form>
    </div>
</div>