<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $content = $_POST['content'] ?? '';
    $author = $_POST['author'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO blogs (title, slug, content, author) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $content, $author]);
    header('Location: admin-blogs.php');
    exit;
}
?>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: 'textarea[name="content"]',
        height: 400,
        plugins: 'link image code preview lists table media',
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image media | preview code',
        menubar: false
    });
</script>
<h2>Add New Blog</h2>
<form method="post">
    <p>Title: <input type="text" name="title" required></p>
    <p>Slug: <input type="text" name="slug" required></p>
    <p>Author: <input type="text" name="author"></p>
    <p>Content:<br>
        <textarea name="content"><?= isset($blog['content']) ? htmlspecialchars($blog['content']) : '' ?></textarea>
    </p>
    <p><button type="submit">Add Blog</button></p>
</form>
<a href="admin-blogs.php">Back</a>