<?php
require '../config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM courses WHERE course_id=?");
$stmt->execute([$id]);

header("Location: courses.php");
exit;