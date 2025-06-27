<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once './db.php';

$restaurantId = $_GET['restaurant_id'] ?? null;

if (!$restaurantId) {
    http_response_code(400);
    echo json_encode(["error" => "Missing restaurant_id"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM reviews WHERE restaurant_id = :id ORDER BY created_at DESC");
    $stmt->execute([':id' => $restaurantId]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($reviews);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB error: " . $e->getMessage()]);
}
?>
