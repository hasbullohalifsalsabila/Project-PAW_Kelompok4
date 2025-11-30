<?php
require '../config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->execute([$id]);

header("Location: users.php");
exit;