<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './db.php';
require_once './auth.php';

// ✅ Authorization token check (optional)
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    $user = verifyJWT($token);
}

// ✅ Get limit from query string
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;

try {
    if ($limit === -1) {
        $stmt = $pdo->query("SELECT * FROM blogs ORDER BY id DESC");
    } else {
        // 🚨 Do NOT use bindValue for LIMIT — instead, inject the integer directly
        $stmt = $pdo->query("SELECT * FROM blogs ORDER BY id DESC LIMIT $limit");
    }

    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($blogs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Database error",
        "error" => $e->getMessage()
    ]);
}
?>