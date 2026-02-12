<?php
require_once '../db.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

$uploadDir  = __DIR__ . '/uploads/';
$publicBase = 'https://backend.crowndevour.com/php/api/image-crud/uploads/';
$quality    = 45; // ✅ AVIF compression (lower = smaller)

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/**
 * Convert image binary to AVIF
 */
function convertToAvif(string $imageData, string $target, int $quality = 45): bool
{
    // ✅ Use Imagick if available
    if (extension_loaded('imagick')) {
        try {
            $img = new Imagick();
            $img->readImageBlob($imageData);
            $img->setImageFormat('avif');
            $img->setImageCompressionQuality($quality);
            $img->stripImage();
            $img->writeImage($target);
            $img->clear();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // 🔁 Fallback to GD (PHP 8.1+)
    if (function_exists('imagecreatefromstring')) {
        $im = imagecreatefromstring($imageData);
        if (!$im) return false;

        imageavif($im, $target, $quality);
        imagedestroy($im);
        return true;
    }

    return false;
}

// 🔍 Fetch restaurants whose images are NOT already AVIF
$stmt = $pdo->prepare("
    SELECT id, image 
    FROM restaurants
    WHERE image IS NOT NULL
      AND image != ''
      AND image NOT LIKE '%.avif'
    LIMIT 100
");
$stmt->execute();
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($restaurants as $row) {

    $id  = $row['id'];
    $url = trim($row['image']);

    echo "🔄 Processing restaurant ID {$id}\n";

    // Download image
    $context = stream_context_create([
        'http' => [
            'header'  => "User-Agent: Mozilla/5.0\r\n",
            'timeout' => 20
        ]
    ]);

    $imageData = @file_get_contents($url, false, $context);
    if (!$imageData) {
        echo "❌ Download failed\n";
        continue;
    }

    // Generate AVIF filename
    $fileName   = time() . '_restaurant_' . uniqid() . '.avif';
    $targetFile = $uploadDir . $fileName;

    if (!convertToAvif($imageData, $targetFile, $quality)) {
        echo "❌ AVIF conversion failed\n";
        continue;
    }

    // Save image record
    $pdo->prepare("
        INSERT INTO images (filename, filepath, uploaded_at)
        VALUES (?, ?, NOW())
    ")->execute([$fileName, 'uploads/' . $fileName]);

    // Update restaurant image
    $pdo->prepare("
        UPDATE restaurants 
        SET image = ? 
        WHERE id = ?
    ")->execute([$publicBase . $fileName, $id]);

    echo "✅ Converted to AVIF\n";
}

echo "🎉 AVIF migration completed.";
