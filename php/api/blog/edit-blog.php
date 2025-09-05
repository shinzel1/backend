<?php
require_once '../db.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// Validate and get blog ID
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die('Invalid blog ID');
}

// Fetch blog from database
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    die('Blog not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = strtolower(preg_replace('/\s+/', '-', $_POST['slug'])) ?? '';
    $author = $_POST['author'] ?? '';
    $summary = $_POST['summary'] ?? '';
    $content = $_POST['content'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $cover_image = $_POST['cover_image'] ?? '';

    $stmt = $pdo->prepare("UPDATE blogs SET title = ?, slug = ?, author = ?, summary = ?, content = ?, tags = ?, cover_image = ? WHERE id = ?");
    $stmt->execute([$title, $slug, $author, $summary, $content, $tags, $cover_image, $id]);

    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Blog #<?= htmlspecialchars($id) ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- CKEditor 5 Classic CDN -->
    <script
        src="https://cdn.jsdelivr.net/npm/ckeditor5-build-classic-base64-upload-adapter@latest/build/ckeditor.js"></script>
</head>

<body class="container py-4">
    <?php require_once '../navbar/navbar.php'; ?>

    <h2 class="mb-4">Edit Blog #<?= htmlspecialchars($id) ?></h2>

    <form method="post" class="row g-3">
        <div class="col-md-6">
            <label for="title" class="form-label">Title</label>
            <input type="text" name="title" id="title" class="form-control"
                value="<?= htmlspecialchars($blog['title']) ?>" required>
        </div>

        <div class="col-md-6">
            <label for="slug" class="form-label">Slug</label>
            <input type="text" name="slug" id="slug" class="form-control" value="<?= htmlspecialchars($blog['slug']) ?>"
                required>
        </div>

        <div class="col-md-6">
            <label for="author" class="form-label">Author</label>
            <input type="text" name="author" id="author" class="form-control"
                value="<?= htmlspecialchars($blog['author']) ?>">
        </div>

        <div class="col-12">
            <label for="summary" class="form-label">Summary</label>
            <textarea name="summary" id="summary" class="form-control"
                rows="3"><?= htmlspecialchars($blog['summary']) ?></textarea>
        </div>

        <div class="col-12">
            <label for="tags" class="form-label">Tags (comma-separated)</label>
            <input type="text" name="tags" id="tags" class="form-control"
                value="<?= htmlspecialchars($blog['tags']) ?>">
        </div>

        <div class="col-12">
            <label for="cover_image" class="form-label">Cover Image URL</label>
            <input type="url" name="cover_image" id="cover_image" class="form-control"
                value="<?= htmlspecialchars($blog['cover_image']) ?>">
        </div>

        <div class="col-12">
            <label for="editor" class="form-label">Content</label>
            <textarea name="content"
                id="editor"><?= htmlspecialchars($blog['content'], ENT_QUOTES | ENT_SUBSTITUTE) ?></textarea>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Update Blog</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

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


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
</body>

</html>