<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once './db.php';
require_once './auth.php';

// Fallback for getallheaders
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
}

// Normalize headers
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$authorizationHeader = $headers['authorization'] ?? '';

if (!$authorizationHeader) {
    http_response_code(401);
    echo json_encode(["error" => "Missing Authorization token"]);
    exit;
}

// Extract token
$token = str_replace('Bearer ', '', $authorizationHeader);

// Verify token
$user = verifyJWT($token);
if (!$user) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid or expired token"]);
    exit;
}

// Handle exclude param
$exclude = $_GET['exclude'] ?? '';

try {
    if (!empty($exclude)) {
        $stmt = $pdo->prepare("SELECT name,title,image FROM categories WHERE name != :exclude");
        $stmt->bindParam(':exclude', $exclude);
        $stmt->execute();
    } else {
        $stmt = $pdo->query("SELECT name,title,image FROM categories");
    }

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error"]);
}
