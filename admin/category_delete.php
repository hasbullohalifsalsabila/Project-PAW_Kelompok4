<?php
require '../config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM categories WHERE category_id=?");
$stmt->execute([$id]);

header("Location: categories.php");
exit;