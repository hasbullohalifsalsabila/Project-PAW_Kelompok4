<?php
require '../config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM materials WHERE material_id=?");
$stmt->execute([$id]);

header("Location: materials.php");
exit;