<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
require_once './db.php'; // ✅ Fixed path
// Get slug to exclude from query
$exclude = $_GET['exclude'] ?? '';

try {
    $query = "SELECT title, slug, created_at 
              FROM blogs 
              WHERE slug != :exclude 
              ORDER BY created_at DESC 
              LIMIT 5";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':exclude' => $exclude]);

    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($blogs);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
?>