<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
require_once '../db.php';

$stmt = $pdo->query("SELECT id,title, name, city, location, status, rating ,image
                     FROM restaurants ORDER BY created_at DESC");
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
function safeOutput($value, $default = '—')
{
    return htmlspecialchars(!empty($value) ? $value : $default);
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Restaurants</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f7f7f7;
        }

        h1 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .empty {
            color: #999;
            font-style: italic;
        }
    </style>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }

        table {
            border-collapse: collapse;
            background: #fff;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f0f0f0;
        }

        img.thumb {
            max-width: 100px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="p-5">
        <h2>Restaurants <a href="add.php" class="btn btn-success btn-sm">+ Add Restaurant</a></h2>
        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Cover Image</th>
                    <th>Title</th>
                    <th>City</th>
                    <th>Location</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $res): ?>

                    <tr>
                        <td><a href="edit.php?id=<?= safeOutput($res['id']) ?>" target="_blank"><?= safeOutput($res['id']) ?></a>
                        </td>
                        <td>
                            <?php if (!empty($res['image'])): ?>
                                <img src="<?= safeOutput($res['image']) ?>" class="thumb" alt="cover">
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= safeOutput($res['name']) ?></td>
                        <td><?= safeOutput($res['title']) ?></td>
                        <td><?= safeOutput($res['city']) ?></td>
                        <td><?= safeOutput($res['location']) ?></td>
                        <td><?= safeOutput($res['rating']) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</body>

</html>