<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $entityType = $_POST['entity_type'];   // restaurant / blog / recipe
    $entityId = intval($_POST['entity_id']);
    $imageType = $_POST['image_type'] ?? 'gallery';

    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = basename($_FILES['image']['name']);
    $safeName = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $filename);
    $targetFile = $uploadDir . time() . "_" . $safeName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        // Save in master table
        $stmt = $pdo->prepare("INSERT INTO images (filename, filepath, uploaded_at) VALUES (:filename, :filepath, NOW())");
        $stmt->execute([
            ':filename' => $safeName,
            ':filepath' => $targetFile
        ]);
        $imageId = $pdo->lastInsertId();

        // Map into correct table
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
            default:
                die("Invalid entity type!");
        }

        $mapStmt->execute([
            ':entity_id' => $entityId,
            ':image_id' => $imageId,
            ':type' => $imageType
        ]);

        header("Location: index.php?success=1");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error uploading file!</div>";
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
            transition: background 0.2s, border-color 0.2s;
        }

        .drop-zone.dragover {
            background: #e9f5ff;
            border-color: #0a58ca;
            color: #0a58ca;
        }

        .drop-zone input {
            display: none;
        }

        #preview {
            margin-top: 15px;
            text-align: center;
        }

        #preview img {
            max-width: 250px;
            border-radius: 10px;
            margin-top: 10px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2>Upload Image</h2>
        <form action="" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm bg-white">

            <!-- Entity Selection -->
            <div class="mb-3">
                <label class="form-label">Entity Type</label>
                <select name="entity_type" id="entity_type" class="form-control" required>
                    <option value="">-- Select Type --</option>
                    <option value="restaurant">Restaurant</option>
                    <option value="blog">Blog</option>
                    <option value="recipe">Recipe</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Select Entity</label>
                <select name="entity_id" id="entity_id" class="form-control" required>
                    <option value="">-- Select an Entity --</option>
                </select>
            </div>

            <!-- Include jQuery + Select2 -->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

            <script>
                $(document).ready(function () {
                    $('#entity_id').select2({
                        placeholder: "Search and select...",
                        ajax: {
                            url: 'fetch_entities.php', // backend endpoint
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term, // search term
                                    type: $('#entity_type').val() // restaurant/blog/recipe
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data
                                };
                            },
                            cache: true
                        }
                    });

                    // Reset dropdown when type changes
                    $('#entity_type').on('change', function () {
                        $('#entity_id').val(null).trigger('change');
                    });
                });
            </script>


            <!-- Image Type -->
            <div class="mb-3">
                <label class="form-label">Image Type</label>
                <select name="image_type" class="form-control">
                    <option value="cover">Cover</option>
                    <option value="gallery">Gallery</option>
                    <option value="menu">Menu (for restaurants)</option>
                    <option value="steps">Steps (for recipes)</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- Drag & Drop Upload -->
            <div class="drop-zone mb-3" id="dropZone">
                <p>Drag & Drop your image here or click to select</p>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>

            <!-- Preview -->
            <div id="preview"></div>

            <button type="submit" class="btn btn-primary mt-3">Upload</button>
            <a href="index.php" class="btn btn-secondary mt-3">Back</a>
        </form>
    </div>

    <script>
        const dropZone = document.getElementById("dropZone");
        const fileInput = document.getElementById("image");
        const preview = document.getElementById("preview");

        dropZone.addEventListener("click", () => fileInput.click());

        dropZone.addEventListener("dragover", (e) => {
            e.preventDefault();
            dropZone.classList.add("dragover");
        });

        dropZone.addEventListener("dragleave", () => {
            dropZone.classList.remove("dragover");
        });

        dropZone.addEventListener("drop", (e) => {
            e.preventDefault();
            dropZone.classList.remove("dragover");

            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                showPreview(fileInput.files[0]);
            }
        });

        fileInput.addEventListener("change", () => {
            if (fileInput.files.length) {
                showPreview(fileInput.files[0]);
            }
        });

        function showPreview(file) {
            if (file && file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = `<p class="text-danger">Selected file is not an image.</p>`;
            }
        }
    </script>
</body>

</html>