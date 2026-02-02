<?php
// Strict JSON and CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

require_once './db.php';
require_once './auth.php';

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

    // ✅ Check for title param
    if (!isset($_GET['title']) || empty($_GET['title'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing 'title' parameter."]);
        exit;
    }

    $title = $_GET['title'];

    // ✅ DB fetch
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE title = :title LIMIT 1");
    $stmt->execute(['title' => $title]);
    $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$restaurant) {
        http_response_code(404);
        echo json_encode(["error" => "Restaurant not found in tabe"]);
        exit;
    }

    // ✅ JSON decode fields
    $jsonFields = [
        'ambiance_features',
        'cuisine_menu_sections',
        'must_try',
        'reasons_to_visit',
        'tips_for_visitors',
        'location_details',
        'additional_info',
        'category',
        'tags',
        'menuImage',
        'chef_recommendations',
        'event_hosting',
        'nutritional_breakdown',
        'signature_cocktails',
        'contact_info',
        'reservations',
        'faq'
    ];

    foreach ($jsonFields as $field) {
        if (isset($restaurant[$field]) && !empty($restaurant[$field])) {
            $restaurant[$field] = json_decode($restaurant[$field], true);
        }
    }

    // ✅ Compose API response
    $response = [
        "id" => (int) $restaurant["id"],
        "name" => $restaurant["name"],
        "city" => $restaurant["city"],
        "restaurantOrCafe" => $restaurant["restaurantOrCafe"],
        "title" => $restaurant["title"],
        "location" => $restaurant["location"],
        "overview" => $restaurant["overview"],
        "shortDescription" => $restaurant["shortDescription"],
        "ambiance" => [
            "description" => $restaurant["ambiance_description"],
            "features" => $restaurant["ambiance_features"]
        ],
        "cuisine" => [
            "description" => $restaurant["cuisine_description"],
            "menu_sections" => $restaurant["cuisine_menu_sections"]
        ],
        "must_try" => $restaurant["must_try"],
        "service" => [
            "description" => $restaurant["service_description"],
            "style" => $restaurant["service_style"]
        ],
        "reasons_to_visit" => $restaurant["reasons_to_visit"],
        "tips_for_visitors" => $restaurant["tips_for_visitors"],
        "location_details" => $restaurant["location_details"],
        "additional_info" => $restaurant["additional_info"],
        "chef_recommendations" => $restaurant["chef_recommendations"] ?? [],
        "event_hosting" => $restaurant["event_hosting"] ?? [],
        "nutritional_breakdown" => $restaurant["nutritional_breakdown"] ?? [],
        "rating" => (float) $restaurant["rating"],
        "review_count" => isset($restaurant["review_count"]) ? (int) $restaurant["review_count"] : 0,
        "category" => $restaurant["category"],
        "tags" => $restaurant["tags"],
        "image" => $restaurant["image"],
        "locationUrl" => $restaurant["locationUrl"],
        "menuImage" => $restaurant["menuImage"],
        "signature_cocktails" => $restaurant["signature_cocktails"],
        "status" => $restaurant["status"] ?? null,
        "gallery" => $restaurant["gallery"] ?? [],
        "cuisines" => $restaurant["cuisines"] ?? [],
        "delivery" => $restaurant["delivery"] ?? false,
        "contact_info" => $restaurant["contact_info"] ?? [],
        "reservations" => $restaurant["reservations"] ?? [],
        "faq" => $restaurant["faq"] ?? [],
        "created_at" => $restaurant["created_at"]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Unexpected error: " . $e->getMessage()]);
    exit;
}
?>