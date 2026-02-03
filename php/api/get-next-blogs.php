<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './db.php';
require_once './auth.php';

/* ===== Optional Auth ===== */
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    verifyJWT($token);
}

/* ===== Inputs ===== */
$currentId = isset($_GET['current_id']) ? intval($_GET['current_id']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 3;

/* ===== Safety ===== */
$limit = max(1, min($limit, 6));

if ($currentId <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid current_id"]);
    exit;
}

try {
    /* ===== Fetch next older blogs ===== */
    $sql = "
        SELECT id, title, slug, featured_image, created_at
        FROM blogs
        WHERE id < :current_id
        ORDER BY id DESC
        LIMIT $limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":current_id" => $currentId
    ]);

    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ===== Fallback: latest blogs if none found ===== */
    if (empty($blogs)) {
        $fallbackSql = "
            SELECT id, title, slug, featured_image, created_at
            FROM blogs
            ORDER BY id DESC
            LIMIT $limit
        ";
        $blogs = $pdo->query($fallbackSql)->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode([
        "next_blogs" => $blogs
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Database error",
        "error" => $e->getMessage()
    ]);
}
?>
