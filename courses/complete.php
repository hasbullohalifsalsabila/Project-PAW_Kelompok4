<?php
require '../config/db.php';
session_start();

$uid = $_SESSION['user']['user_id'];
$mid = $_POST['material_id'];

$check = $pdo->prepare("SELECT * FROM materials_progress WHERE user_id=? AND material_id=?");
$check->execute([$uid,$mid]);

if (!$check->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO materials_progress (user_id,material_id,is_completed,completed_at)
                           VALUES (?,?,1,NOW())");
    $stmt->execute([$uid,$mid]);
}

echo "ok";