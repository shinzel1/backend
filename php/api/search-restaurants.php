<?php
// ✅ CORS Headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './db.php';

// ✅ Input
$input = json_decode(file_get_contents("php://input"), true);
$query = strtolower(trim($input['query'] ?? ''));
$category = strtolower(trim($input['category'] ?? 'all'));
if ($query == "") {
    $query = $category;
}
// ✅ Utility function
function fetchWithFilters($pdo, $table, $searchTerm, $nameField = 'name')
{
    $stmt = $pdo->query("SELECT * FROM $table");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matchedByName = [];
    $matchedByFull = [];

    foreach ($all as $row) {
        $rowStr = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $name = strtolower($row[$nameField] ?? '');

        if ($searchTerm && strpos($name, $searchTerm) !== false) {
            $matchedByName[] = $row;
        }
        if ($searchTerm && strpos(strtolower($rowStr), $searchTerm) !== false) {
            $matchedByFull[] = $row;
        }
        if (!$searchTerm && $category !== 'all') {
            if (strpos(strtolower($rowStr), $category) !== false) {
                $matchedByFull[] = $row;
            }
        }
    }

    // Merge & unique
    $combined = array_merge($matchedByName, $matchedByFull);
    $unique = array_values(array_reduce($combined, function ($carry, $item) {
        $hash = md5(json_encode($item));
        $carry[$hash] = $item;
        return $carry;
    }, []));

    return $unique;
}

try {
    // ✅ Restaurants (with JSON field decoding)
    $restaurants = fetchWithFilters($pdo, 'restaurants', strtolower($query), 'name');

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
        'menuImage'
    ];
    foreach ($restaurants as &$row) {
        foreach ($jsonFields as $field) {
            if (isset($row[$field])) {
                $decoded = json_decode($row[$field], true);
                $row[$field] = json_last_error() === JSON_ERROR_NONE ? $decoded : $row[$field];
            }
        }
    }
    $blogs = fetchWithFilters($pdo, 'blogs', strtolower($query), 'name');
    $recipes = fetchWithFilters($pdo, 'recipes', strtolower($query), 'title');

    echo json_encode([
        "restaurants" => $restaurants,
        "blogs" => $blogs,
        "recipes" => $recipes
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error", "message" => $e->getMessage()]);
}
