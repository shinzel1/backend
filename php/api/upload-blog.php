<?php
// Allow from any origin (development only)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");
require_once './db.php'; // ✅ Fixed path

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Decode input JSON
$input = json_decode(file_get_contents("php://input"), true);

// Extract and sanitize fields
$title = trim($input['title'] ?? '');
$slug = trim($input['slug'] ?? '');
$author = trim($input['author'] ?? '');
$summary = trim($input['summary'] ?? '');
$content = trim($input['content'] ?? '');
$tags = $input['tags'] ?? []; // array expected
$cover_image = trim($input['cover_image'] ?? '');

// Basic validation
if (!$title || !$slug || !$content) {
    http_response_code(400);
    echo json_encode(["message" => "Title, Slug, and Content are required."]);
    exit;
}

// Convert tags to JSON string
$tags_json = json_encode(array_map('trim', $tags));

try {
    // Insert query
    $stmt = $pdo->prepare("
        INSERT INTO blogs (title, slug, author, summary, content, tags, cover_image)
        VALUES (:title, :slug, :author, :summary, :content, :tags, :cover_image)
    ");

    $stmt->execute([
        ':title' => $title,
        ':slug' => $slug,
        ':author' => $author,
        ':summary' => $summary,
        ':content' => $content,
        ':tags' => $tags_json,
        ':cover_image' => $cover_image
    ]);

    echo json_encode(["message" => "Blog uploaded successfully!"]);
    // Blog is inserted successfully, now trigger push
    // exec("php /full/path/to/send-push.php");

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo json_encode(["message" => "A blog with this slug already exists."]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Database error: " . $e->getMessage()]);
    }
}
?>