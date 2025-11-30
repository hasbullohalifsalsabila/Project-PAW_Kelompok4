<?php
require '../config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM quizzes WHERE quiz_id=?");
$stmt->execute([$id]);

header("Location: quizzes.php");
exit;