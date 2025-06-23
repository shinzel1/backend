<?php 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './db.php'; // ✅ Fixed path

try {
    $stmt = $pdo->query("SELECT title,city,restaurantOrCafe FROM restaurants"); // ✅ make sure 'recipes' table exists
    $slugs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($slugs);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Database error",
        "error" => $e->getMessage() // ✅ helpful for debugging
    ]);
}
?>
