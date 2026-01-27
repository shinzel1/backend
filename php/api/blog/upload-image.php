<?php
header('Content-Type: application/json');

$uploadDir = __DIR__ . '/uploads/blog_images/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (!isset($_FILES['upload'])) {
    exit(json_encode(['error' => ['message' => 'No file uploaded']]));
}

$file = $_FILES['upload'];

/* ===== Security Checks ===== */

if ($file['error'] !== UPLOAD_ERR_OK) {
    exit(json_encode(['error' => ['message' => 'Upload failed']]));
}

if ($file['size'] > 5 * 1024 * 1024) {
    exit(json_encode(['error' => ['message' => 'Max 5MB allowed']]));
}

$imageInfo = getimagesize($file['tmp_name']);
if (!$imageInfo) {
    exit(json_encode(['error' => ['message' => 'Invalid image file']]));
}

$mime = $imageInfo['mime'];
$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!in_array($mime, $allowed)) {
    exit(json_encode(['error' => ['message' => 'Unsupported image type']]));
}

/* ===== Load image ===== */

switch ($mime) {
    case 'image/jpeg':
        $src = imagecreatefromjpeg($file['tmp_name']);
        break;
    case 'image/png':
        $src = imagecreatefrompng($file['tmp_name']);
        break;
    case 'image/webp':
        $src = imagecreatefromwebp($file['tmp_name']);
        break;
    case 'image/gif':
        $src = imagecreatefromgif($file['tmp_name']);
        break;
    default:
        exit(json_encode(['error' => ['message' => 'Cannot process image']]));
}

if (!$src) {
    exit(json_encode(['error' => ['message' => 'Image load failed']]));
}

/* ===== Preserve transparency ===== */

imagepalettetotruecolor($src);
imagealphablending($src, true);
imagesavealpha($src, true);

/* ===== Generate filename ===== */

$filename = uniqid('blog_', true) . '.avif';
$filepath = $uploadDir . $filename;

/* ===== Convert & compress ===== */

$quality = 45; // 35–55 recommended

if (!function_exists('imageavif')) {
    exit(json_encode(['error' => ['message' => 'AVIF not supported on server']]));
}

imageavif($src, $filepath, $quality);

imagedestroy($src);

/* ===== Return URL ===== */

$url = '/uploads/blog_images/' . $filename;

echo json_encode([
    'url' => $url
]);
