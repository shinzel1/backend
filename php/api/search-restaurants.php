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

// ✅ Input handling
$input = json_decode(file_get_contents("php://input"), true);
$query = isset($input['query']) ? trim($input['query']) : null;
$category = isset($input['category']) ? trim(strtolower($input['category'])) : 'all';

if (!$query || strlen($query) < 2) {
    http_response_code(400);
    echo json_encode(["error" => "Query too short"]);
    exit;
}

// ✅ SQL LIKE search query
$words = preg_split('/\s+/', $query);
$likeClauses = [];
$params = [];

foreach ($words as $index => $word) {
    $key = ":word$index";
    $likeClauses[] = "(LOWER(name) LIKE $key OR LOWER(city) LIKE $key OR LOWER(title) LIKE $key OR LOWER(tags) LIKE $key OR LOWER(overview) LIKE $key)";
    $params[$key] = '%' . strtolower($word) . '%';
}

// ✅ Optional category filter
$categoryClause = '';
if ($category !== 'all') {
    $categoryClause = " AND LOWER(restaurantOrCafe) = :category";
    $params[':category'] = $category;
}

// ✅ Build and execute query
$sql = "SELECT * FROM restaurants WHERE " . implode(" AND ", $likeClauses) . $categoryClause . " ORDER BY rating DESC LIMIT 20";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Decode JSON fields
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

    foreach ($results as &$row) {
        foreach ($jsonFields as $field) {
            if (isset($row[$field])) {
                $decoded = json_decode($row[$field], true);
                $row[$field] = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
            }
        }
    }

    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error", "message" => $e->getMessage()]);
}
