<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
require_once './db.php'; // ✅ Fixed path
require_once './auth.php';

// Get slug to exclude from query
$exclude = $_GET['exclude'] ?? '';

try {
      // ✅ Use getallheaders() for broader compatibility
    $headers = getallheaders();

    // ✅ Normalize header key casing
    $authorizationHeader = '';
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $authorizationHeader = $value;
            break;
        }
    }

    if (!$authorizationHeader) {
        http_response_code(401);
        echo json_encode(["error" => "Missing Authorization token"]);
        exit;
    }

    // ✅ Extract token from "Bearer ..." header
    $token = str_replace('Bearer ', '', $authorizationHeader);

    // ✅ Verify token
    $user = verifyJWT($token);
    if (!$user) {
        http_response_code(403);
        echo json_encode(["error" => "Invalid or expired token"]);
        exit;
    }
    $query = "SELECT title, slug, created_at 
              FROM recipes 
              WHERE slug != :exclude 
              ORDER BY created_at DESC 
              LIMIT 5";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':exclude' => $exclude]);

    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($recipes);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
?>