<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin-login.php');

    exit;
}
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("Invalid image ID");
}

// Fetch image details
$stmt = $pdo->prepare("
    SELECT i.id, i.filename, i.filepath, i.uploaded_at,
                    COALESCE(
                CASE 
                    WHEN r.id IS NOT NULL THEN 'restaurant'
                    WHEN b.id IS NOT NULL THEN 'blog'
                    WHEN rc.id IS NOT NULL THEN 'recipe'
                END,
                'images'
            ) AS entity_type,
COALESCE(r.name, b.title, rc.title, 'Standalone Image') AS entity_name,
           COALESCE(r.id, b.id, rc.id) AS entity_id,
           COALESCE(ri.type, bi.type, rci.type) AS image_type
    FROM images i
    LEFT JOIN restaurant_images ri ON i.id = ri.image_id
    LEFT JOIN restaurants r ON ri.restaurant_id = r.id
    LEFT JOIN blog_images bi ON i.id = bi.image_id
    LEFT JOIN blogs b ON bi.blog_id = b.id
    LEFT JOIN recipe_images rci ON i.id = rci.image_id
    LEFT JOIN recipes rc ON rci.recipe_id = rc.id
    WHERE i.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$image = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$image) {
    die("Image not found!");
}



function convertToAvif($sourcePath, $destinationPath, $quality = 55)
{
    $info = getimagesize($sourcePath);
    if (!$info) {
        throw new Exception("Invalid image file.");
    }

    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;

        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;

        case 'image/webp':
            $image = imagecreatefromwebp($sourcePath);
            break;

        case 'image/avif':
            return rename($sourcePath, $destinationPath);

        default:
            throw new Exception("Unsupported image type.");
    }

    if (!imageavif($image, $destinationPath, $quality)) {
        imagedestroy($image);
        throw new Exception("AVIF conversion failed.");
    }

    imagedestroy($image);
    unlink($sourcePath); // remove original
    return true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entityType = $_POST['entity_type'];
    $entityId = intval($_POST['entity_id']);
    $imageType = $_POST['image_type'] ?? 'gallery';

    // Update file if new one uploaded
    if (!empty($_FILES['image']['name'])) {

        $tempFile = $_FILES['image']['tmp_name'];

        $uploadDir = "uploads/avif/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = uniqid("img_") . ".avif";
        $targetFile = $uploadDir . $newName;

        convertToAvif($tempFile, $targetFile, 55);

        // update DB
        $stmt = $pdo->prepare("UPDATE images SET filename = :filename, filepath = :filepath WHERE id = :id");
        $stmt->execute([
            ':filename' => $newName,
            ':filepath' => $targetFile,
            ':id' => $id
        ]);
    }


    // Delete old mapping
    $pdo->prepare("DELETE FROM restaurant_images WHERE image_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM blog_images WHERE image_id = :id")->execute([':id' => $id]);
    $pdo->prepare("DELETE FROM recipe_images WHERE image_id = :id")->execute([':id' => $id]);

    // Insert new mapping
    switch ($entityType) {

        case 'restaurant':
            $mapStmt = $pdo->prepare("INSERT INTO restaurant_images (restaurant_id, image_id, type) VALUES (:entity_id, :image_id, :type)");
            break;

        case 'blog':
            $mapStmt = $pdo->prepare("INSERT INTO blog_images (blog_id, image_id, type) VALUES (:entity_id, :image_id, :type)");
            break;

        case 'recipe':
            $mapStmt = $pdo->prepare("INSERT INTO recipe_images (recipe_id, image_id, type) VALUES (:entity_id, :image_id, :type)");
            break;

        case 'images': // ✅ standalone image
            $mapStmt = null; // no mapping needed
            break;

        default:
            die("Invalid entity type!");
    }

    if ($mapStmt) {
        $mapStmt->execute([
            ':entity_id' => $entityId,
            ':image_id' => $id,
            ':type' => $imageType
        ]);
    }


    header("Location: index.php?updated=1");
    exit;
}

// Helper for absolute URL
function make_absolute_url(string $path): string
{
    if (preg_match('~^https?://~i', $path))
        return $path;
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $base = $scheme . '://' . $host . ($dir ? $dir : '') . '/';
    return $base . ltrim($path, '/');
}
$fullUrl = make_absolute_url($image['filepath']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .drop-zone {
            border: 2px dashed #0d6efd;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
        }

        .drop-zone.dragover {
            background: #e9f5ff;
            border-color: #0a58ca;
        }

        .drop-zone input {
            display: none;
        }

        #preview img {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 8px;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="container mt-5">
        <h2>Edit Image</h2>
        <form action="" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm bg-white">

            <div class="mb-3 text-center">
                <img src="<?= htmlspecialchars($fullUrl) ?>" alt="Current Image" class="img-thumbnail" width="250">
            </div>

            <div class="mb-3">
                <label class="form-label">Entity Type</label>
                <select name="entity_type" id="entity_type" class="form-control" required>
                    <option value="images" <?= $image['entity_type'] === null ? 'selected' : '' ?>>Standalone Image
                    </option>
                    <option value="restaurant" <?= $image['entity_type'] === 'restaurant' ? 'selected' : '' ?>>Restaurant
                    </option>
                    <option value="blog" <?= $image['entity_type'] === 'blog' ? 'selected' : '' ?>>Blog</option>
                    <option value="recipe" <?= $image['entity_type'] === 'recipe' ? 'selected' : '' ?>>Recipe</option>

                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Entity</label>
                <select name="entity_id" id="entity_id" class="form-control" required>
                    <option value="<?= (int) $image['entity_id'] ?>" selected>
                        <?= htmlspecialchars($image['entity_name']) ?>
                    </option>
                </select>
            </div>

            <script>
                $(document).ready(function () {
                    $('#entity_id').select2({
                        placeholder: "Search and select...",
                        ajax: {
                            url: 'fetch_entities.php',
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    type: $('#entity_type').val()
                                };
                            },
                            processResults: function (data) {
                                return { results: data };
                            }
                        }
                    });
                    $('#entity_type').on('change', function () {
                        const type = $(this).val();

                        if (type === 'images') {
                            $('#entity_id').prop('required', false);
                        } else {
                            $('#entity_id').prop('required', true);
                        }

                        $('#entity_id').val(null).trigger('change');
                    });

                });
            </script>

            <div class="mb-3">
                <label class="form-label">Image Type</label>
                <select name="image_type" class="form-control">
                    <option value="cover" <?= $image['image_type'] === 'cover' ? 'selected' : '' ?>>Cover</option>
                    <option value="gallery" <?= $image['image_type'] === 'gallery' ? 'selected' : '' ?>>Gallery</option>
                    <option value="menu" <?= $image['image_type'] === 'menu' ? 'selected' : '' ?>>Menu</option>
                    <option value="steps" <?= $image['image_type'] === 'steps' ? 'selected' : '' ?>>Steps</option>
                    <option value="other" <?= $image['image_type'] === 'other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>

            <div class="drop-zone mb-3" id="dropZone">
                <p>Drag & Drop to replace the image, or click to select a new one</p>
                <input type="file" name="image" id="image" accept="image/*">
            </div>

            <div id="preview"></div>

            <button type="submit" class="btn btn-success mt-3">Save Changes</button>
            <a href="index.php" class="btn btn-secondary mt-3">Back</a>
        </form>
    </div>

    <script>
        const dropZone = document.getElementById("dropZone");
        const fileInput = document.getElementById("image");
        const preview = document.getElementById("preview");

        dropZone.addEventListener("click", () => fileInput.click());
        dropZone.addEventListener("dragover", e => { e.preventDefault(); dropZone.classList.add("dragover"); });
        dropZone.addEventListener("dragleave", () => dropZone.classList.remove("dragover"));
        dropZone.addEventListener("drop", e => {
            e.preventDefault();
            dropZone.classList.remove("dragover");
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showPreview(fileInput.files[0]);
            }
        });
        fileInput.addEventListener("change", () => { if (fileInput.files.length) showPreview(fileInput.files[0]); });

        function showPreview(file) {
            if (file && file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = e => { preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`; }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = `<p class="text-danger">Not a valid image file.</p>`;
            }
        }
    </script>
</body>

</html>