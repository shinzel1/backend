<?php
require_once 'db.php';
session_start();

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
    $slug = $_POST['slug'] ?? '';
    $content = $_POST['content'] ?? '';
    $author = $_POST['author'] ?? '';

    $stmt = $pdo->prepare("UPDATE blogs SET title = ?, slug = ?, content = ?, author = ? WHERE id = ?");
    $stmt->execute([$title, $slug, $content, $author, $id]);

    header('Location: admin-blogs.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Blog #<?= htmlspecialchars($id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- CKEditor 5 Classic CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        form p {
            margin-bottom: 15px;
        }

        input[type="text"] {
            width: 400px;
            padding: 6px;
        }

        textarea {
            width: 100%;
            min-height: 200px;
        }

        button {
            padding: 8px 20px;
        }
    </style>
</head>

<body>

    <h2>Edit Blog #<?= htmlspecialchars($id) ?></h2>

    <form method="post" >
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" value="<?= htmlspecialchars($blog['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Slug:<br>
                <input type="text" name="slug" value="<?= htmlspecialchars($blog['slug']) ?>" required>
            </label>
        </div>
        <div class="form-group">
            <label>Author:<br>
                <input type="text" name="author" value="<?= htmlspecialchars($blog['author']) ?>">
            </label>
        </div>
        <div class="form-group">
            <label>Content:<br>
                <textarea name="content"
                    id="editor"><?= htmlspecialchars($blog['content'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE) ?></textarea>
            </label>
        </div>

        <button type="submit">Update</button>

    </form>

    <p><a href="admin-blogs.php">← Back to Blog List</a></p>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ClassicEditor
                .create(document.querySelector('#editor'))
                .catch(error => {
                    console.error('CKEditor init error:', error);
                });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous"></script>
</body>

</html>