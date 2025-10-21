<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once './db.php';

// 🔗 Helper function to make image URLs absolute
function make_absolute_url(string $path): string {
    if (preg_match('~^https?://~i', $path)) return $path;

    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if (!empty($ref)) {
        $p = parse_url($ref);
        $scheme = $p['scheme'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http');
        $host   = $p['host'] ?? $_SERVER['HTTP_HOST'];
        $port   = isset($p['port']) ? ':' . $p['port'] : '';
        $dir    = isset($p['path']) ? rtrim(dirname($p['path']), '/\\') : '';
        $base   = $scheme . '://' . $host . $port . $dir . '/';
        return $base . ltrim($path, '/');
    }

    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $base   = $scheme . '://' . $host . ($dir ? $dir : '') . '/';
    return $base . ltrim($path, '/');
}

// ✅ Get restaurant_id from query params
$restaurantId = $_GET['restaurant_id'] ?? null;

if (!$restaurantId || !is_numeric($restaurantId)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid or missing restaurant_id"]);
    exit;
}

try {
    // ✅ Fetch gallery images linked to this restaurant
    $stmt = $pdo->prepare("
        SELECT 
            i.id AS image_id,
            i.filename,
            i.filepath,
            ri.restaurant_id,
            ri.type
        FROM 
            restaurant_images AS ri
        JOIN 
            images AS i 
            ON ri.image_id = i.id
        WHERE 
            ri.restaurant_id = ?
            AND ri.type = 'gallery'
        ORDER BY i.id DESC
    ");
    
    $stmt->execute([$restaurantId]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$images) {
        echo json_encode([]);
        exit;
    }

    // 🔗 Convert filepaths to absolute URLs
    foreach ($images as &$img) {
        $img['filepath'] = make_absolute_url($img['filepath']);
    }

    echo json_encode($images);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Server error: " . $e->getMessage()
    ]);
}
