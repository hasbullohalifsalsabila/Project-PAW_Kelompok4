<?php
require __DIR__ . '/config/db.php';
if (!is_logged_in()) { header('Location: auth/login.php'); exit; }
$user = current_user();
$role = $user['role'];

if ($role === 'admin') {
    header('Location: admin/index.php'); exit;
} elseif ($role === 'instructor') {
    header('Location: instructor/index.php'); exit;
} else {
    header('Location: student/index.php'); exit;
}
