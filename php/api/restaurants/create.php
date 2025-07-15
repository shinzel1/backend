<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once '../db.php';
require_once '../auth.php';

$headers = getallheaders();
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

$token = str_replace('Bearer ', '', $authorizationHeader);
$user = verifyJWT($token);
if (!$user) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid or expired token"]);
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

$restaurant = json_decode(file_get_contents("php://input"), true);
if (!$restaurant) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON body"]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO restaurants (
        name, city, restaurantOrCafe, title, location, overview, shortDescription,
        ambiance_description, ambiance_features,
        cuisine_description, cuisine_menu_sections,
        must_try, service_description, service_style,
        reasons_to_visit, tips_for_visitors,
        location_details, additional_info, chef_recommendations, event_hosting, nutritional_breakdown, rating,
        category, tags, locationUrl, image, menuImage, signature_cocktails,
        status, gallery, cuisines, delivery
    ) VALUES (
        :name, :city, :restaurantOrCafe, :title, :location, :overview, :shortDescription,
        :ambiance_description, :ambiance_features,
        :cuisine_description, :cuisine_menu_sections,
        :must_try, :service_description, :service_style,
        :reasons_to_visit, :tips_for_visitors,
        :location_details, :additional_info, :chef_recommendations, :event_hosting, :nutritional_breakdown, :rating,
        :category, :tags, :locationUrl, :image, :menuImage, :signature_cocktails,
        :status, :gallery, :cuisines, :delivery
    )");

    $stmt->execute([
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
        ':status' => safe($restaurant, 'status'),
        ':gallery' => safeJson($restaurant, 'gallery'),
        ':cuisines' => safeJson($restaurant, 'cuisines'),
        ':delivery' => !empty($restaurant['delivery']) ? 1 : 0
    ]);

    echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);

    $restaurantTitle = safe($restaurant, 'name', 'New Restaurant') . " " . safe($restaurant, 'location', '');
    $url = "https://crowndevour.com/" . strtolower(safe($restaurant, 'city')) . "/" . strtolower(safe($restaurant, 'restaurantOrCafe')) . "/" . safe($restaurant, 'title');

    $dataPack = [
        'title' => '🍽️ New Restaurant Alert!',
        'body' => "Check out \"$restaurantTitle\" just added on CrownDevour!",
        'data' => [ 'url' => $url ]
    ];
    require_once "../send-push.php";
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
