<?php
require '../config/db.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM announcements WHERE announcement_id=?");
$stmt->execute([$id]);

header("Location: announcements.php");
exit;