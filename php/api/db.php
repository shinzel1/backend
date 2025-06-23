<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
require_once(__DIR__ . '/load_env.php');
loadEnv(__DIR__.'/../../../.env'); // Keep .env one level above public folder
$host = getenv("DB_HOST");
$dbname = getenv("DB_NAME");
$username = getenv("DB_USER");
$password = getenv("DB_PASS");
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>