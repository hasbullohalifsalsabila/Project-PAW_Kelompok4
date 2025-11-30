<?php
require "../config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$uid = $_SESSION['user']['user_id'];
$cid = $_POST['course_id'];
$rating = $_POST['rating'];
$comment = $_POST['comment'];

$insert = $pdo->prepare("
    INSERT INTO reviews (course_id, user_id, rating, comment, reviewed_at)
    VALUES (?, ?, ?, ?, NOW())
");
$insert->execute([$cid, $uid, $rating, $comment]);

header("Location: ../courses/course_detail.php?course=$cid&review_added=1");
exit;
?>