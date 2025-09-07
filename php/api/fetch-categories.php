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
    require_once './db.php';
    require_once './auth.php';

    // ✅ Authorization token check (optional)
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);
        $user = verifyJWT($token);
    }

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
