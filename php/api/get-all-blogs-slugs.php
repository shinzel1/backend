<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/db.php'; // Adjust path as needed

try {
    $stmt = $pdo->query("SELECT slug FROM blogs");
    $slugs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($slugs);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error"]);
}
?>