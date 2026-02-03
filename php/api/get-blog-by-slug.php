<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
require_once './db.php'; // Adjust path as needed
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

    $stmt = $pdo->prepare("SELECT id, title, author, tags, content,summary, cover_image FROM blogs WHERE slug = :slug");
    $stmt->execute([':slug' => $slug]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($blog) {
        $blog['tags'] = json_decode($blog['tags'] ?? '[]');
        echo json_encode($blog);
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Blog not found"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error"]);
}
?>