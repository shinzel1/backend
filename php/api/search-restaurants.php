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
$query = trim($input['query'] ?? '');
$category = strtolower(trim($input['category'] ?? 'all'));

$params = [];
$whereClauses = [];

// ✅ Case 1: query is present → normal word-based matching
if ($query) {
    $words = preg_split('/\s+/', $query);
    foreach ($words as $index => $word) {
        $key = ":word$index";
        // $whereClauses[] = "(LOWER(name) LIKE $key OR LOWER(city) LIKE $key OR LOWER(title) LIKE $key OR LOWER(tags) LIKE $key OR LOWER(overview) LIKE $key)";
        $whereClauses[] = "(LOWER(name) LIKE $key OR LOWER(city) LIKE $key OR LOWER(title) LIKE $key)";
        $params[$key] = '%' . strtolower($word) . '%';
    }
}

// ✅ Case 2: query is empty, but category is specific → treat category like a keyword
else if ($category !== 'all') {
    $key = ':categoryWord';
    $whereClauses[] = "(LOWER(name) LIKE $key OR LOWER(city) LIKE $key OR LOWER(title) LIKE $key OR LOWER(tags) LIKE $key OR LOWER(overview) LIKE $key)";
    $params[$key] = '%' . $category . '%';
}

// ✅ Exit early if both query and category are empty/default
if (empty($whereClauses)) {
    http_response_code(400);
    echo json_encode(["error" => "No search query or category provided."]);
    exit;
}

// ✅ Build and run SQL
$sql = "SELECT * FROM restaurants WHERE " . implode(" AND ", $whereClauses) . " ORDER BY rating DESC LIMIT 10";

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
