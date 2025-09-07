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
    try {
        $stmt = $pdo->query("SELECT name,title,image FROM categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($categories);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error"]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
?>