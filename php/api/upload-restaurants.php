<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once './db.php'; // ✅ Fixed path

$jsonUrl = 'https://crowndevour.com/data/CafeRestaurants.json'; // Your JSON URL
$jsonContents = file_get_contents($jsonUrl);

if ($jsonContents === false) {
  die("❌ Failed to fetch JSON from the URL.");
}

$jsonData = json_decode($jsonContents, true);

if (!is_array($jsonData)) {
  die("❌ The JSON is invalid or not an array.");
}

// Helper function to safely fetch values or return null
function safe($array, $key, $default = null)
{
  return isset($array[$key]) && $array[$key] !== '' ? $array[$key] : $default;
}

function safeJson($array, $key)
{
  return json_encode($array[$key] ?? []);
}

$stmt = $pdo->prepare("
  INSERT INTO restaurants (
    name, city, restaurantOrCafe, title, location, overview, shortDescription,
    ambiance_description, ambiance_features,
    cuisine_description, cuisine_menu_sections,
    must_try, service_description, service_style,
    reasons_to_visit, tips_for_visitors,
    location_details, additional_info, rating,
    category, tags, locationUrl, image, menuImage
  ) VALUES (
    :name, :city, :restaurantOrCafe, :title, :location, :overview, :shortDescription,
    :ambiance_description, :ambiance_features,
    :cuisine_description, :cuisine_menu_sections,
    :must_try, :service_description, :service_style,
    :reasons_to_visit, :tips_for_visitors,
    :location_details, :additional_info, :rating,
    :category, :tags, :locationUrl, :image, :menuImage
  )
");

$insertedCount = 0;

foreach ($jsonData as $restaurant) {
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
    ':rating' => safe($restaurant, 'rating'),
    ':category' => safeJson($restaurant, 'category'),
    ':tags' => safeJson($restaurant, 'tags'),
    ':locationUrl' => safe($restaurant, 'locationUrl'),
    ':image' => safe($restaurant, 'image'),
    ':menuImage' => safeJson($restaurant, 'menuImage'),
  ]);
  $insertedCount++;
}

echo "✅ Successfully inserted $insertedCount restaurant record(s).";
?>