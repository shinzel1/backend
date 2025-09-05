<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../admin-login.php');

    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = strtolower(preg_replace('/\s+/', '-', $_POST['slug'])) ?? '';
    $author = $_POST['author'] ?? '';
    $summary = $_POST['summary'] ?? '';
    $content = $_POST['content'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $cover_image = $_POST['cover_image'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, author, summary, content, tags, cover_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $author, $summary, $content, $tags, $cover_image]);

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add New Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script
        src="https://cdn.jsdelivr.net/npm/ckeditor5-build-classic-base64-upload-adapter@latest/build/ckeditor.js"></script>
</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Add New Blog</h2>
        <form method="post" class="card p-4 shadow-sm bg-white">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Author</label>
                <input type="text" name="author" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Summary</label>
                <input type="text" name="summary" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Tags (comma-separated)</label>
                <input type="text" name="tags" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Cover Image URL</label>
                <input type="text" name="cover_image" class="form-control">
            </div>

            <div class="mb-4">
                <label class="form-label">Content</label>
                <textarea name="content" id="editor"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Add Blog</button>
            <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ClassicEditor
                .create(document.querySelector('#editor'), {
                    ckfinder: {
                        uploadUrl: '' // Not needed for Base64
                    },
                    simpleUpload: {
                        // This enables Base64 inline images
                        uploadUrl: '',
                        withCredentials: false,
                        headers: {}
                    }
                })
                .catch(error => {
                    console.error('CKEditor init error:', error);
                });
        });
    </script>
</body>

</html>