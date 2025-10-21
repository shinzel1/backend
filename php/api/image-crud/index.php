<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../admin-login.php');

    exit;
}
// Helper: make absolute URL from referrer/domain + relative path
function make_absolute_url(string $path): string {
    if (preg_match('~^https?://~i', $path)) return $path;

    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'];

    // ✅ Force correct base directory
    $basePath = '/php/api/image-crud/';

    // Ensure no duplicate slashes
    $path = ltrim($path, '/');

    return $scheme . '://' . $host . $basePath . $path;
}


// Fetch images with entity mapping
$sql = "
    SELECT i.id, i.filename, i.filepath, i.uploaded_at,
           'restaurant' AS entity_type, r.id AS entity_id, r.name AS entity_name, ri.type AS image_type
      FROM images i
      JOIN restaurant_images ri ON i.id = ri.image_id
      JOIN restaurants r ON ri.restaurant_id = r.id
    UNION ALL
    SELECT i.id, i.filename, i.filepath, i.uploaded_at,
           'blog' AS entity_type, b.id AS entity_id, b.title AS entity_name, bi.type AS image_type
      FROM images i
      JOIN blog_images bi ON i.id = bi.image_id
      JOIN blogs b ON bi.blog_id = b.id
    UNION ALL
    SELECT i.id, i.filename, i.filepath, i.uploaded_at,
           'recipe' AS entity_type, rc.id AS entity_id, rc.title AS entity_name, ri.type AS image_type
      FROM images i
      JOIN recipe_images ri ON i.id = ri.image_id
      JOIN recipes rc ON ri.recipe_id = rc.id
    ORDER BY id DESC
";
$stmt = $pdo->query($sql);
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

        function filterTable() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.querySelectorAll("#imagesTable tbody tr");
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? "" : "none";
            });
        }
    </script>
</head>
<body class="bg-light">
        <?php require_once '../navbar/navbar.php'; ?>

<div class="p-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📸 Image Manager</h2>
        <a href="upload.php" class="btn btn-primary">+ Upload New Image</a>
    </div>

    <!-- Search -->
    <div class="mb-3">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by filename, entity, or type..." onkeyup="filterTable()">
    </div>

    <table id="imagesTable" class="table table-bordered table-hover align-middle shadow-sm bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Thumbnail</th>
                <th>File Name</th>
                <th>File URL</th>
                <th>Entity</th>
                <th>Type</th>
                <th>Uploaded At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($images as $img): 
            $fullUrl = make_absolute_url($img['filepath']);
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
                <td>
                    <span class="badge bg-info text-dark"><?= htmlspecialchars($img['entity_type']) ?></span><br>
                    <?= htmlspecialchars($img['entity_name']) ?> (ID: <?= (int)$img['entity_id'] ?>)
                </td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($img['image_type']) ?></span></td>
                <td><?= htmlspecialchars($img['uploaded_at']) ?></td>
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
