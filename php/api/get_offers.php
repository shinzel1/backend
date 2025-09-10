<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once './db.php';

// Get restaurant_id from query params
$restaurantId = $_GET['restaurant_id'] ?? null;

if (!$restaurantId || !is_numeric($restaurantId)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing restaurant_id"]);
    exit;
}

try {
    // ✅ Fetch offers linked to this restaurant
    $stmt = $pdo->prepare("
        SELECT o.id, o.title, o.code, o.discount_value, o.discount_type,
               o.start_date, o.end_date, o.is_active, o.created_at
        FROM offers o
        JOIN offer_restaurant orr ON o.id = orr.offer_id
        WHERE orr.restaurant_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$restaurantId]);
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$offers) {
        echo json_encode([]);
        exit;
    }

    echo json_encode($offers);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Server error: " . $e->getMessage()
    ]);
}
