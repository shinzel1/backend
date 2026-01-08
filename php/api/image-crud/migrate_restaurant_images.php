<?php
require_once '../db.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

$uploadDir = __DIR__ . '/uploads/';
$publicBase = 'https://backend.crowndevour.com/php/api/image-crud/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Fetch restaurants that still use external images
$stmt = $pdo->prepare("
    SELECT id, image 
    FROM restaurants
    WHERE image IS NOT NULL
      AND image != ''
      AND image NOT LIKE :local
    LIMIT 30
");
$stmt->execute([
    ':local' => $publicBase . '%'
]);

$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($restaurants as $row) {

    $restaurantId = $row['id'];
    $sourceUrl = trim($row['image']);

    echo "Processing restaurant ID {$restaurantId}\n";

    // Download with User-Agent (important for Google)
    $context = stream_context_create([
        "http" => [
            "header" => "User-Agent: Mozilla/5.0\r\n",
            "timeout" => 20
        ]
    ]);

    $imageData = @file_get_contents($sourceUrl, false, $context);
    if ($imageData === false) {
        echo "❌ Download failed\n";
        continue;
    }

    // Detect extension
    $path = parse_url($sourceUrl, PHP_URL_PATH);
    $ext = pathinfo($path, PATHINFO_EXTENSION);

    if (!$ext || strlen($ext) > 4) {
        $ext = 'jpg';
    }

    // Generate safe filename
    $fileName = time() . '_img_' . uniqid() . '.' . $ext;
    $relative = 'uploads/' . $fileName;
    $targetFile = $uploadDir . $fileName;

    if (!file_put_contents($targetFile, $imageData)) {
        echo "❌ Save failed\n";
        continue;
    }

    // Insert into images table
    $imgStmt = $pdo->prepare("
        INSERT INTO images (filename, filepath, uploaded_at)
        VALUES (:filename, :filepath, NOW())
    ");
    $imgStmt->execute([
        ':filename' => $fileName,
        ':filepath' => $relative
    ]);

    // Update restaurant image column
    $updateStmt = $pdo->prepare("
        UPDATE restaurants
        SET image = :image
        WHERE id = :id
    ");
    $updateStmt->execute([
        ':image' => $publicBase . $fileName,
        ':id' => $restaurantId
    ]);

    echo "✅ Migrated successfully\n";
}

echo "Migration batch finished.";
