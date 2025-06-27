<?php
// File: restaurants/read.php
header("Access-Control-Allow-Origin: *"); // or specify your domain
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}
require_once '../db.php';
require_once '../auth.php';
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

$id = $_GET['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing 'id' parameter"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(["error" => "Restaurant not found"]);
        exit;
    }

    // JSON fields to decode
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
        'nutritional_breakdown'
    ];

    foreach ($jsonFields as $field) {
        if (isset($row[$field])) {
            $decoded = json_decode($row[$field], true);
            $row[$field] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }
    }

    // Assemble the final response
    $response = [
        "id" => (int) $row["id"],
        "name" => $row["name"],
        "city" => $row["city"],
        "restaurantOrCafe" => $row["restaurantOrCafe"],
        "title" => $row["title"],
        "location" => $row["location"],
        "overview" => $row["overview"],
        "shortDescription" => $row["shortDescription"],
        "ambiance" => [
            "description" => $row["ambiance_description"],
            "features" => $row["ambiance_features"]
        ],
        "cuisine" => [
            "description" => $row["cuisine_description"],
            "menu_sections" => $row["cuisine_menu_sections"]
        ],
        "must_try" => $row["must_try"],
        "service" => [
            "description" => $row["service_description"],
            "style" => $row["service_style"]
        ],
        "reasons_to_visit" => $row["reasons_to_visit"],
        "tips_for_visitors" => $row["tips_for_visitors"],
        "location_details" => $row["location_details"],
        "additional_info" => $row["additional_info"],
        "chef_recommendations" => $row["chef_recommendations"] ?? [],
        "event_hosting" => $row["event_hosting"] ?? [],
        "nutritional_breakdown" => $row["nutritional_breakdown"] ?? [],
        "rating" => (float) $row["rating"],
        "review_count" => isset($row["review_count"]) ? (int) $row["review_count"] : 0,
        "category" => $row["category"],
        "tags" => $row["tags"],
        "image" => $row["image"],
        "locationUrl" => $row["locationUrl"],
        "menuImage" => $row["menuImage"],
        "signature_cocktails" => $row["signature_cocktails"],
        "created_at" => $row["created_at"]
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
?>