<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$success = false;

if ($_SERVER['REQUEST_METHOD']==='POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,'instructor')");
    $stmt->execute([$name,$email,$pass]);

    $new_id = $pdo->lastInsertId();

    // Insert profile
    $stmt = $pdo->prepare("INSERT INTO instructors_profile (instructor_id,bio,photo_url,linkedin,website)
                           VALUES (?,?,?,?,?)");
    $stmt->execute([
        $new_id,
        $_POST['bio'],
        $_POST['photo'],
        $_POST['linkedin'],
        $_POST['website']
    ]);

    $success = true;
}
?>

<div class="admin-container">
    <h1 class="page-title">Add Instructor</h1>

    <div class="form-card">
        <?php if ($success): ?><div class="success">Instructor added!</div><?php endif; ?>

        <form method="POST">

            <label>Name</label>
            <input type="text" name="name">

            <label>Email</label>
            <input type="email" name="email">

            <label>Password</label>
            <input type="password" name="password">

            <label>Bio</label>
            <textarea name="bio" rows="4"></textarea>

            <label>Photo URL</label>
            <input type="text" name="photo">

            <label>LinkedIn</label>
            <input type="text" name="linkedin">

            <label>Website</label>
            <input type="text" name="website">

            <button class="btn-dark">Save</button>

        </form>
    </div>
</div>