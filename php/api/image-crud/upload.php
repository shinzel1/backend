<?php
require_once '../db.php';
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../admin-login.php');
    exit;
}

/* ================= AVIF CONVERTER ================= */

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

/* ================= UPLOAD HANDLER ================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $entityType = $_POST['entity_type'];
    $entityId = intval($_POST['entity_id']);
    $imageType = $_POST['image_type'] ?? 'gallery';

    $uploadDir = "uploads/avif/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    try {

        $tempFile = null;

        /* ===== CASE 1: FILE UPLOAD ===== */
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tempFile = $_FILES['image']['tmp_name'];
        }

        /* ===== CASE 2: IMAGE URL ===== */ elseif (!empty($_POST['image_url'])) {
            $imageUrl = trim($_POST['image_url']);
            if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new Exception("Invalid URL.");
            }


            $imageData = @file_get_contents($imageUrl);

            if ($imageData === false) {
                throw new Exception("Failed to download image from URL.");
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'img_');
            file_put_contents($tempFile, $imageData);
        } else {
            throw new Exception("No image provided.");
        }


        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];

        $info = getimagesize($tempFile);
        if (!$info || !in_array($info['mime'], $allowedMime)) {
            throw new Exception("Invalid or unsupported image format.");
        }



        /* ===== CONVERT TO AVIF ===== */

        $avifName = uniqid("img_") . ".avif";
        $targetFile = $uploadDir . $avifName;

        convertToAvif($tempFile, $targetFile, 55);

        /* ===== SAVE MASTER IMAGE ===== */

        $stmt = $pdo->prepare(
            "INSERT INTO images (filename, filepath, uploaded_at)
             VALUES (:filename, :filepath, NOW())"
        );
        $stmt->execute([
            ':filename' => $avifName,
            ':filepath' => $targetFile
        ]);

        $imageId = $pdo->lastInsertId();

        /* ===== ENTITY MAPPING ===== */
        if ($entityType != "images") {
            switch ($entityType) {
                case 'restaurant':
                    $mapStmt = $pdo->prepare(
                        "INSERT INTO restaurant_images (restaurant_id, image_id, type)
                     VALUES (:entity_id, :image_id, :type)"
                    );
                    break;

                case 'blog':
                    $mapStmt = $pdo->prepare(
                        "INSERT INTO blog_images (blog_id, image_id, type)
                     VALUES (:entity_id, :image_id, :type)"
                    );
                    break;

                case 'recipe':
                    $mapStmt = $pdo->prepare(
                        "INSERT INTO recipe_images (recipe_id, image_id, type)
                     VALUES (:entity_id, :image_id, :type)"
                    );
                    break;

                default:
                    throw new Exception("Invalid entity type.");
            }

            $mapStmt->execute([
                ':entity_id' => $entityId,
                ':image_id' => $imageId,
                ':type' => $imageType
            ]);
        }

        header("Location: index.php?success=1");
        exit;

    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>❌ {$e->getMessage()}</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Upload Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .drop-zone {
            border: 2px dashed #0d6efd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            color: #6c757d;
            cursor: pointer;
        }

        .drop-zone.dragover {
            background: #e9f5ff;
            border-color: #0a58ca;
            color: #0a58ca;
        }

        .drop-zone input {
            display: none;
        }

        #preview img {
            max-width: 250px;
            margin-top: 10px;
            border-radius: 10px;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="container mt-5">
        <h2>Upload Image</h2>

        <form method="post" enctype="multipart/form-data" class="card p-4 shadow-sm bg-white">

            <div class="mb-3">
                <label class="form-label">Entity Type</label>
                <select name="entity_type" id="entity_type" class="form-control" required>
                    <option value="images">Standalone Image</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="blog">Blog</option>
                    <option value="recipe">Recipe</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Entity</label>
                <select name="entity_id" id="entity_id" class="form-control"></select>
            </div>

            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

            <script>
                $(function () {
                    $('#entity_id').select2({
                        placeholder: "Search...",
                        ajax: {
                            url: 'fetch_entities.php',
                            dataType: 'json',
                            delay: 250,
                            data: params => ({
                                q: params.term,
                                type: $('#entity_type').val()
                            }),
                            processResults: data => ({ results: data })
                        }
                    });

                    $('#entity_type').on('change', () => {
                        $('#entity_id').val(null).trigger('change');
                    });
                });
            </script>

            <div class="mb-3">
                <label class="form-label">Image Type</label>
                <select name="image_type" class="form-control">
                    <option value="cover">Cover</option>
                    <option value="gallery">Gallery</option>
                    <option value="menu">Menu</option>
                    <option value="steps">Steps</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="drop-zone mb-3" id="dropZone">
                <p>Drag & Drop image or click</p>
                <input type="file" name="image" id="image" accept="image/*,.avif">
            </div>

            <div class="mb-3">
                <label class="form-label">Or Image URL</label>
                <input type="text" name="image_url" class="form-control">
            </div>

            <div id="preview"></div>

            <button class="btn btn-primary mt-3">Upload</button>
            <a href="index.php" class="btn btn-secondary mt-3">Back</a>
        </form>
    </div>

    <script>
        const dz = document.getElementById("dropZone");
        const fi = document.getElementById("image");
        const preview = document.getElementById("preview");

        dz.onclick = () => fi.click();
        dz.ondragover = e => { e.preventDefault(); dz.classList.add("dragover"); };
        dz.ondragleave = () => dz.classList.remove("dragover");
        dz.ondrop = e => {
            e.preventDefault();
            dz.classList.remove("dragover");
            fi.files = e.dataTransfer.files;
            showPreview(fi.files[0]);
        };
        fi.onchange = () => showPreview(fi.files[0]);

        function showPreview(file) {
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => preview.innerHTML = `<img src="${e.target.result}">`;
            reader.readAsDataURL(file);
        }
    </script>
</body>

</html>