<?php
require '../config/db.php';
require 'header.php';
require 'sidebar.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT u.*, ip.bio, ip.photo_url, ip.linkedin, ip.website
    FROM users u
    LEFT JOIN instructors_profile ip ON u.user_id = ip.instructor_id
    WHERE u.user_id=?
");
$stmt->execute([$id]);
$i = $stmt->fetch();

$success = false;

if ($_SERVER['REQUEST_METHOD']==='POST') {

    $stmt = $pdo->prepare("UPDATE users SET name=?, email=? WHERE user_id=?");
    $stmt->execute([$_POST['name'],$_POST['email'],$id]);

    $stmt = $pdo->prepare("
        UPDATE instructors_profile SET 
        bio=?, photo_url=?, linkedin=?, website=? 
        WHERE instructor_id=?
    ");
    $stmt->execute([
        $_POST['bio'], $_POST['photo'], $_POST['linkedin'], $_POST['website'], $id
    ]);

    $success = true;
}
?>

<div class="admin-container">

    <h1 class="page-title">Edit Instructor</h1>

    <div class="form-card">

        <?php if ($success): ?><div class="success">Saved!</div><?php endif; ?>

        <form method="POST">

            <label>Name</label>
            <input type="text" name="name" value="<?=$i['name']?>">

            <label>Email</label>
            <input type="email" name="email" value="<?=$i['email']?>">

            <label>Bio</label>
            <textarea name="bio" rows="4"><?=$i['bio']?></textarea>

            <label>Photo URL</label>
            <input type="text" name="photo" value="<?=$i['photo_url']?>">

            <label>LinkedIn</label>
            <input type="text" name="linkedin" value="<?=$i['linkedin']?>">

            <label>Website</label>
            <input type="text" name="website" value="<?=$i['website']?>">

            <button class="btn-dark">Save</button>

        </form>

    </div>
</div>