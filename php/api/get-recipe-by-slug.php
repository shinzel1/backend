<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

ini_set('display_errors', 0);
ini_set('log_errors', 0);
error_reporting(E_ALL);

require_once './db.php'; // ✅ Ensure correct path
require_once './auth.php';
$slug = $_GET['slug'] ?? '';

if (!$slug) {
    http_response_code(400);
    echo json_encode(["message" => "Slug is required"]);
    exit;
}

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

    $stmt = $pdo->prepare("
        SELECT *
        FROM recipes
        WHERE slug = :slug
        LIMIT 1
    ");
    $stmt->execute([':slug' => $slug]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($recipe) {
        $recipe['tags'] = json_decode($recipe['tags'] ?? '[]');

        echo json_encode($recipe);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Recipe not found"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Database error",
        "error" => $e->getMessage() // Optional: Remove in production
    ]);
}
