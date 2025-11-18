<?php
require __DIR__ . '/../config/db.php';
session_destroy();
header('Location: ../index.php');
exit;
