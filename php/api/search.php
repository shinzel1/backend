<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once './db.php';

$input = json_decode(file_get_contents("php://input"), true);
$searchTerm = isset($input['query']) ? strtolower(trim($input['query'])) : '';

if (!$searchTerm || strlen($searchTerm) < 2) {
    echo json_encode([
        "restaurants" => [],
        "blogs" => []
    ]);
    exit;
}

// Utility to fetch and filter rows
function fetchAndFilter($pdo, $table, $searchTerm, $nameField = 'name') {
    $stmt = $pdo->query("SELECT * FROM $table");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matchedByName = [];
    $matchedByFull = [];

    foreach ($all as $row) {
        $rowStr = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $name = strtolower($row[$nameField] ?? '');

        if (strpos($name, $searchTerm) !== false) {
            $matchedByName[] = $row;
        }
        if (strpos(strtolower($rowStr), $searchTerm) !== false) {
            $matchedByFull[] = $row;
        }
    }

    // Merge and filter unique
    $combined = array_merge($matchedByName, $matchedByFull);
    $unique = array_values(array_reduce($combined, function ($carry, $item) {
        $hash = md5(json_encode($item));
        $carry[$hash] = $item;
        return $carry;
    }, []));

    return $unique;
}

try {
    $filteredRestaurants = fetchAndFilter($pdo, 'restaurants', $searchTerm, 'name');
    $filteredBlogs = fetchAndFilter($pdo, 'blogs', $searchTerm, 'name');

    echo json_encode([
        "restaurants" => $filteredRestaurants,
        "blogs" => $filteredBlogs
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}


?>