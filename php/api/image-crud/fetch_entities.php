<?php
require_once '../db.php';

$type = $_GET['type'] ?? '';
$q = $_GET['q'] ?? '';

if (!$type) {
    echo json_encode([]);
    exit;
}

switch ($type) {
    case 'restaurant':
        $stmt = $pdo->prepare("SELECT id, name FROM restaurants WHERE name LIKE :q LIMIT 20");
        break;
    case 'blog':
        $stmt = $pdo->prepare("SELECT id, title as name FROM blogs WHERE title LIKE :q LIMIT 20");
        break;
    case 'recipe':
        $stmt = $pdo->prepare("SELECT id, title as name FROM recipes WHERE title LIKE :q LIMIT 20");
        break;
    default:
        echo json_encode([]);
        exit;
}

$stmt->execute([':q' => "%$q%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach ($results as $row) {
    $data[] = [
        "id" => $row['id'],
        "text" => $row['name']
    ];
}

echo json_encode($data);
?>