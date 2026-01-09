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
        "blogs" => [],
        "recipes" => []
    ]);
    exit;
}

function fetchWithPrioritySearch(PDO $pdo, string $table, string $searchTerm)
{
    $stmt = $pdo->query("SELECT * FROM `$table`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matchedName = [];
    $matchedTitle = [];
    $matchedOther = [];

    foreach ($rows as $row) {
        $rowLower = array_map(
            fn($v) => is_string($v) ? strtolower($v) : '',
            $row
        );

        // 1️⃣ name column
        if (!empty($rowLower['name']) && strpos($rowLower['name'], $searchTerm) !== false) {
            $matchedName[] = $row;
            continue;
        }

        // 2️⃣ title column
        if (!empty($rowLower['title']) && strpos($rowLower['title'], $searchTerm) !== false) {
            $matchedTitle[] = $row;
            continue;
        }

        // 3️⃣ any other column
        foreach ($rowLower as $col => $value) {
            if (in_array($col, ['name', 'title'], true)) {
                continue;
            }
            if ($value && strpos($value, $searchTerm) !== false) {
                $matchedOther[] = $row;
                break;
            }
        }
    }

    // Merge in priority order and remove duplicates
    $merged = array_merge($matchedName, $matchedTitle, $matchedOther);

    $unique = [];
    foreach ($merged as $item) {
        $unique[$item['id'] ?? md5(json_encode($item))] = $item;
    }

    return array_values($unique);
}
function fetchWithPrioritySearchh(PDO $pdo, string $table, string $searchTerm)
{
    $stmt = $pdo->query("SELECT * FROM `$table`");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $matchedName = [];
    $matchedTitle = [];
    $matchedOther = [];

    foreach ($rows as $row) {
        $rowLower = array_map(
            fn($v) => is_string($v) ? strtolower($v) : '',
            $row
        );

        // 1️⃣ name column
        if (!empty($rowLower['title']) && strpos($rowLower['title'], $searchTerm) !== false) {
            $matchedName[] = $row;
            continue;
        }

        // 2️⃣ title column
        if (!empty($rowLower['slug']) && strpos($rowLower['slug'], $searchTerm) !== false) {
            $matchedTitle[] = $row;
            continue;
        }

        // 3️⃣ any other column
        foreach ($rowLower as $col => $value) {
            if (in_array($col, ['name', 'title'], true)) {
                continue;
            }
            if ($value && strpos($value, $searchTerm) !== false) {
                $matchedOther[] = $row;
                break;
            }
        }
    }

    // Merge in priority order and remove duplicates
    $merged = array_merge($matchedName, $matchedTitle, $matchedOther);

    $unique = [];
    foreach ($merged as $item) {
        $unique[$item['id'] ?? md5(json_encode($item))] = $item;
    }

    return array_values($unique);
}

try {
    echo json_encode([
        "restaurants" => fetchWithPrioritySearch($pdo, 'restaurants', $searchTerm),
        "blogs" => fetchWithPrioritySearchh($pdo, 'blogs', $searchTerm),
        "recipes" => fetchWithPrioritySearchh($pdo, 'recipes', $searchTerm),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database error",
        "message" => $e->getMessage()
    ]);
}
