<?php
// config/db.php
declare(strict_types=1);

// start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB constants (projek_paw)
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_NAME')) define('DB_NAME', 'projek_paw');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// PDO connection
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// helper: check auth
function is_logged_in(): bool {
    return isset($_SESSION['user']);
}
function current_user() {
    return $_SESSION['user'] ?? null;
}
