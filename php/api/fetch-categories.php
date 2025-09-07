<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once './db.php';
require_once './auth.php';

// Get slug to exclude from query (optional)
$exclude = $_GET['exclude'] ?? '';

try {
    /**
     * 🔐 Get Authorization header reliably (Apache, Nginx, PHP built-in)
     */
    // function getAuthorizationHeader() {
    //     $headers = [];

    //     // Prefer getallheaders()
    //     if (function_exists('getallheaders')) {
    //         $headers = getallheaders();
    //     } elseif (function_exists('apache_request_headers')) {
    //         $headers = apache_request_headers();
    //     }

    //     $headers = array_change_key_case($headers, CASE_LOWER);

    //     // Check common sources
    //     if (!empty($headers['authorization'])) {
    //         return trim($headers['authorization']);
    //     }
    //     if (isset($_SERVER['HTTP_AUTHORIZATION'])) { 
    //         return trim($_SERVER['HTTP_AUTHORIZATION']);
    //     }
    //     if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    //         return trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    //     }

    //     return null;
    // }

    // $authorizationHeader = getAuthorizationHeader();

    // if (!$authorizationHeader) {
    //     http_response_code(401);
    //     echo json_encode(["error" => "Missing Authorization token"]);
    //     exit;
    // }

    // Extract token (Bearer <token>)
    // $token = preg_replace('/^Bearer\s+/i', '', $authorizationHeader);

    // // Verify token
    // $user = verifyJWT($token);
    // if (!$user) {
    //     http_response_code(403);
    //     echo json_encode(["error" => "Invalid or expired token"]);
    //     exit;
    // }

    // ✅ Fetch categories
    try {
        $stmt = $pdo->query("SELECT name, title, image FROM categories");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Database error"]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
