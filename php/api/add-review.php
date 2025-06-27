<?php
header("Access-Control-Allow-Origin: *"); // or specify your domain
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

require_once './db.php';

$data = json_decode(file_get_contents("php://input"), true);


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}
if (!$data || !isset($data['restaurant_id'], $data['reviewer_name'], $data['rating'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO reviews (restaurant_id, reviewer_name, rating, comment) VALUES (:restaurant_id, :reviewer_name, :rating, :comment)");
    $stmt->execute([
        ':restaurant_id' => $data['restaurant_id'],
        ':reviewer_name' => $data['reviewer_name'],
        ':rating' => $data['rating'],
        ':comment' => $data['comment'] ?? ''
    ]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
}
?>
