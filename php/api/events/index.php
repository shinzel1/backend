<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
require_once '../db.php';
$stmt = $pdo->query("SELECT e.*, GROUP_CONCAT(r.name SEPARATOR ', ') AS restaurant_names 
                     FROM events e
                     LEFT JOIN event_restaurant er ON e.id = er.event_id
                     LEFT JOIN restaurants r ON er.restaurant_id = r.id
                     GROUP BY e.id
                     ORDER BY e.created_at DESC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="container mt-5">
        <h2>Events <a href="add.php" class="btn btn-success btn-sm">+ Add Event</a></h2>

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Event added successfully!</div>
        <?php elseif (isset($_GET['updated'])): ?>
            <div class="alert alert-info">Event updated successfully!</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-danger">Event deleted successfully!</div>
        <?php endif; ?>

        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Title</th>
                    <th>Restaurants</th>
                    <th>Dates</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= $event['id'] ?></td>
                        <td><?= htmlspecialchars($event['title']) ?></td>
                        <td><?= htmlspecialchars($event['restaurant_names'] ?? 'N/A') ?></td>
                        <td><?= $event['start_date'] ?> → <?= $event['end_date'] ?></td>
                        <td><?= htmlspecialchars($event['location']) ?></td>
                        <td><?= $event['is_active'] ? "Active" : "Inactive" ?></td>
                        <td>
                            <a href="edit.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="delete.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this event?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</body>

</html>