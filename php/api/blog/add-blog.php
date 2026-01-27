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
    <script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>

</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Add New Blog</h2>
        <form method="post" class="card p-4 shadow-sm bg-white" onsubmit="localStorage.removeItem('blog_draft')">
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
                <textarea name="content" id="editor" rows="12"></textarea>
            </div>

            <div class="mt-2 text-muted small">
                <span id="wordCount">Words: 0</span> |
                <span id="charCount">Characters: 0</span> |
                <span id="readingTime">Reading time: 0 min</span>
            </div>

            <button type="submit" class="btn btn-primary">Add Blog</button>
            <a href="index.php" class="btn btn-secondary ms-2">Cancel</a>
        </form>
    </div>

    <script>
        let editorInstance;

        document.addEventListener('DOMContentLoaded', function () {

            ClassicEditor
                .create(document.querySelector('#editor'), {
                    toolbar: {
                        items: [
                            'heading', '|',
                            'bold', 'italic', 'underline', 'strikethrough', '|',
                            'link', 'bulletedList', 'numberedList', '|',
                            'blockQuote', 'codeBlock', 'insertTable', '|',
                            'undo', 'redo'
                        ]
                    },
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2' },
                            { model: 'heading3', view: 'h3', title: 'Heading 3' }
                        ]
                    }
                })
                .then(editor => {
                    editorInstance = editor;

                    // Restore autosave
                    const savedContent = localStorage.getItem('blog_draft');
                    if (savedContent) {
                        editor.setData(savedContent);
                    }

                    editor.model.document.on('change:data', () => {
                        const text = editor.getData().replace(/<[^>]*>/g, '');
                        updateStats(text);

                        // Autosave
                        localStorage.setItem('blog_draft', editor.getData());
                    });
                })
                .catch(error => console.error(error));

            function updateStats(text) {
                const words = text.trim().split(/\s+/).filter(w => w.length > 0).length;
                const chars = text.length;
                const readTime = Math.max(1, Math.ceil(words / 200));

                document.getElementById('wordCount').innerText = `Words: ${words}`;
                document.getElementById('charCount').innerText = `Characters: ${chars}`;
                document.getElementById('readingTime').innerText = `Reading time: ${readTime} min`;
            }
        });
    </script>
    <script>
        const titleInput = document.querySelector('input[name="title"]');
        const slugInput = document.querySelector('input[name="slug"]');

        titleInput.addEventListener('input', () => {
            slugInput.value = titleInput.value
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
        });
    </script>

</body>

</html>