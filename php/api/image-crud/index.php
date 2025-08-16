<?php
require_once '../db.php';

// Helper: make absolute URL from referrer/domain + relative path
function make_absolute_url(string $path): string {
    // If already absolute, return as-is
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }

    // Prefer HTTP_REFERER to keep exact base path the user came from (if present)
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if (!empty($ref)) {
        $p = parse_url($ref);
        // Build base: scheme://host[:port]/dir-of-referrer/
        $scheme = $p['scheme'] ?? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http');
        $host   = $p['host'] ?? $_SERVER['HTTP_HOST'];
        $port   = isset($p['port']) ? ':' . $p['port'] : '';
        $dir    = isset($p['path']) ? rtrim(dirname($p['path']), '/\\') : '';
        $base   = $scheme . '://' . $host . $port . $dir . '/';
        return $base . ltrim($path, '/');
    }

    // Fallback: use current script directory
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];
    $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $base   = $scheme . '://' . $host . ($dir ? $dir : '') . '/';
    return $base . ltrim($path, '/');
}

// Fetch images (single file path in `filepath`)
$stmt = $pdo->query("SELECT * FROM images ORDER BY id DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Image Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert("Copied: " + text);
            });
        }
    </script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📸 Image Manager</h2>
        <a href="upload.php" class="btn btn-primary">+ Upload New Image</a>
    </div>

    <table class="table table-bordered table-hover align-middle shadow-sm bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Thumbnail</th>
                <th>File Name</th>
                <th>File URL</th>
                <th>Referrer</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($images as $img): 
            $fullUrl  = make_absolute_url($img['filepath']);
            $referrer = $_SERVER['HTTP_REFERER'] ?? 'N/A';
        ?>
            <tr>
                <td><?= (int)$img['id'] ?></td>
                <td>
                    <img src="<?= htmlspecialchars($fullUrl) ?>" class="img-thumbnail" width="80" alt="preview">
                </td>
                <td><?= htmlspecialchars($img['filename']) ?></td>
                <td>
                    <a href="<?= htmlspecialchars($fullUrl) ?>" target="_blank"><?= htmlspecialchars($fullUrl) ?></a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('<?= htmlspecialchars($fullUrl, ENT_QUOTES) ?>')">Copy</button>
                </td>
                <td><span class="text-muted small"><?= htmlspecialchars($referrer) ?></span></td>
                <td>
                    <a href="edit.php?id=<?= (int)$img['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete.php?id=<?= (int)$img['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this image?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
