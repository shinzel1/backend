<?php 
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once '../db.php';

try {
    $stmt = $pdo->query("SELECT id, title, slug, author, summary, tags, cover_image, created_at FROM blogs ORDER BY id DESC");
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
    <title>Admin - Blog List</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
        table { border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; }
        img.thumb { max-width: 100px; max-height: 80px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body>

<h1>All Blogs 
    <a href="add-blog.php" class="button">+ Add New</a> 
    <a href="admin-logout.php" class="button" style="background:#dc3545;">Logout</a>
</h1>

<?php if (!empty($blogs)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Cover</th>
                <th>Title</th>
                <th>Slug</th>
                <th>Author</th>
                <th>Summary</th>
                <!-- <th>Tags</th> -->
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($blogs as $blog): ?>
                <tr>
                    <td><?= safeOutput($blog['id']) ?></td>
                    <td>
                        <?php if (!empty($blog['cover_image'])): ?>
                            <img src="<?= safeOutput($blog['cover_image']) ?>" class="thumb" alt="cover">
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= safeOutput($blog['title']) ?></td>
                    <td><?= safeOutput($blog['slug']) ?></td>
                    <td><?= safeOutput($blog['author']) ?></td>
                    <td><?= safeOutput($blog['summary']) ?></td>
                    <!-- <td><?= safeOutput($blog['tags']) ?></td> -->
                    <td><?= safeOutput($blog['created_at']) ?></td>
                    <td class="actions">
                        <a href="edit-blog.php?id=<?= $blog['id'] ?>" class="button">Edit</a>
                        <a href="delete-blog.php?id=<?= $blog['id'] ?>" class="button"  onclick="return confirm('Delete this blog?')">Delete</a>
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
