<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once './db.php';

$input = json_decode(file_get_contents("php://input"), true);
$searchQuery = trim($input['query'] ?? '');
$category = trim($input['category'] ?? 'all');

if (!$searchQuery) {
    echo json_encode(['success' => false, 'message' => 'Empty search query']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO search_queries (query, category) VALUES (:query, :category)");
    $stmt->execute([
        'query' => $searchQuery,
        'category' => $category
    ]);

    echo json_encode(['success' => true, 'message' => 'Query saved']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>