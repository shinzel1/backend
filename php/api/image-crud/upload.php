<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = basename($_FILES['image']['name']);
    $safeName = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $filename);
    $targetFile = $uploadDir . time() . "_" . $safeName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $stmt = $pdo->prepare("INSERT INTO images (filename, filepath, uploaded_at) VALUES (:filename, :filepath, NOW())");
        $stmt->execute([
            ':filename' => $safeName,
            ':filepath' => $targetFile
        ]);
        header("Location: index.php?success=1");
        exit;
    } else {
        echo "Error uploading file!";
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
        
        <!-- Drag & Drop Zone -->
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
            reader.onload = function(e) {
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
