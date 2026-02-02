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

if ($currentId <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid current_id"]);
    exit;
}

try {
    /*
      Assumption:
      - Higher ID = newer
      - Detail page is showing current restaurant
      - We fetch next older restaurants for internal linking
    */
    $sql = "
        SELECT id, name, title, city, overview, image,restaurantOrCafe
        FROM restaurants
        WHERE id < :current_id
        ORDER BY id DESC
        LIMIT $limit
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":current_id" => $currentId
    ]);

    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "next_restaurants" => $restaurants
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Database error",
        "error" => $e->getMessage()
    ]);
}
