<?php
ini_set('display_errors', 0); ini_set('log_errors', 1); error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT id, title, slug, author, created_at FROM blogs ORDER BY id DESC");
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DB error: " . $e->getMessage());
}

function safeOutput($value) {
    return htmlspecialchars($value ?: '—');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Blogs</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; padding: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

<h1>All Blogs <a href="add-blog.php">[+ Add New]</a> | <a href="admin-logout.php">Logout</a></h1>

<?php if (!empty($blogs)): ?>
    <table>
        <thead>
            <tr><th>ID</th><th>Title</th><th>Slug</th><th>Author</th><th>Created</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach ($blogs as $blog): ?>
                <tr>
                    <td><?= safeOutput($blog['id']) ?></td>
                    <td><?= safeOutput($blog['title']) ?></td>
                    <td><?= safeOutput($blog['slug']) ?></td>
                    <td><?= safeOutput($blog['author']) ?></td>
                    <td><?= safeOutput($blog['created_at']) ?></td>
                    <td>
                        <a href="edit-blog.php?id=<?= $blog['id'] ?>">Edit</a> |
                        <a href="delete-blog.php?id=<?= $blog['id'] ?>" onclick="return confirm('Delete this blog?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No blog posts found.</p>
<?php endif; ?>

</body>
</html>
