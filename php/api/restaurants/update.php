<?php
// ✅ CORS Headers - Required for PUT from frontend (like Next.js)
header("Access-Control-Allow-Origin: *"); // or specify your domain
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// ✅ Only accept PUT requests for update
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only PUT requests are allowed"]);
    exit;
}


require_once '../db.php';
require_once '../auth.php'; // Uncomment if you're using auth
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
// ✅ Read JSON body
$restaurant = json_decode(file_get_contents("php://input"), true);

if (!$restaurant || !isset($restaurant['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON body or missing ID"]);
    exit;
}
function safe($array, $key, $default = null)
{
    return isset($array[$key]) && $array[$key] !== '' ? $array[$key] : $default;
}

function safeJson($array, $key)
{
    return json_encode($array[$key] ?? []);
}

try {
    $stmt = $pdo->prepare("UPDATE restaurants SET
        name = :name,
        city = :city,
        restaurantOrCafe = :restaurantOrCafe,
        title = :title,
        location = :location,
        overview = :overview,
        shortDescription = :shortDescription,
        ambiance_description = :ambiance_description,
        ambiance_features = :ambiance_features,
        cuisine_description = :cuisine_description,
        cuisine_menu_sections = :cuisine_menu_sections,
        must_try = :must_try,
        service_description = :service_description,
        service_style = :service_style,
        reasons_to_visit = :reasons_to_visit,
        tips_for_visitors = :tips_for_visitors,
        location_details = :location_details,
        additional_info = :additional_info,
        chef_recommendations = :chef_recommendations,
        event_hosting = :event_hosting,
        nutritional_breakdown = :nutritional_breakdown,
        rating = :rating,
        category = :category,
        tags = :tags,
        locationUrl = :locationUrl,
        image = :image,
        menuImage = :menuImage,
        signature_cocktails = :signature_cocktails
        WHERE id = :id");

    $stmt->execute([
        ':id' => $restaurant['id'],
        ':name' => safe($restaurant, 'name'),
        ':city' => safe($restaurant, 'city'),
        ':restaurantOrCafe' => safe($restaurant, 'restaurantOrCafe'),
        ':title' => safe($restaurant, 'title'),
        ':location' => safe($restaurant, 'location'),
        ':overview' => safe($restaurant, 'overview'),
        ':shortDescription' => safe($restaurant, 'shortDescription'),
        ':ambiance_description' => safe($restaurant['ambiance'] ?? [], 'description'),
        ':ambiance_features' => safeJson($restaurant['ambiance'] ?? [], 'features'),
        ':cuisine_description' => safe($restaurant['cuisine'] ?? [], 'description'),
        ':cuisine_menu_sections' => safeJson($restaurant['cuisine'] ?? [], 'menu_sections'),
        ':must_try' => safeJson($restaurant, 'must_try'),
        ':service_description' => safe($restaurant['service'] ?? [], 'description'),
        ':service_style' => safe($restaurant['service'] ?? [], 'style'),
        ':reasons_to_visit' => safeJson($restaurant, 'reasons_to_visit'),
        ':tips_for_visitors' => safeJson($restaurant, 'tips_for_visitors'),
        ':location_details' => safeJson($restaurant, 'location_details'),
        ':additional_info' => safeJson($restaurant, 'additional_info'),
        ':chef_recommendations' => safeJson($restaurant, 'chef_recommendations'),
        ':event_hosting' => safeJson($restaurant, 'event_hosting'),
        ':nutritional_breakdown' => safeJson($restaurant, 'nutritional_breakdown'),
        ':rating' => safe($restaurant, 'rating'),
        ':category' => safeJson($restaurant, 'category'),
        ':tags' => safeJson($restaurant, 'tags'),
        ':locationUrl' => safe($restaurant, 'locationUrl'),
        ':image' => safe($restaurant, 'image'),
        ':menuImage' => safeJson($restaurant, 'menuImage'),
        ':signature_cocktails' => safeJson($restaurant, 'signature_cocktails'),
    ]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>