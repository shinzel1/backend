<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once __DIR__ . '/db.php'; // Adjust path as needed

try {
    // Query to fetch restaurant data
    $stmt = $pdo->query("SELECT id, name,image,title, city, location, overview, shortDescription, rating FROM restaurants ORDER BY id DESC");
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function safeOutput($value, $default = '—')
{
    return htmlspecialchars(!empty($value) ? $value : $default);
}

$host = "";
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    $host = 'http://localhost:3000';
} else {
    // You are on the server
    $host = 'https://crowndevour.com';

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Uploaded Restaurants</title>
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

<body>
    <?php require_once './navbar/nav.php';?>
    <h1>Uploaded Restaurants</h1>

    <?php if (!empty($restaurants)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Cover Image</th>
                    <th>Title</th>
                    <th>City</th>
                    <th>Location</th>
                    <th>Overview</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restaurants as $restaurant): ?>
                    <tr>
                        <td><a
                                href="<?= htmlspecialchars($host) ?>/restaurants/<?= safeOutput($restaurant['id']) ?>/edit"><?= safeOutput($restaurant['id']) ?></a>
                        </td>
                        <td>
                            <?php if (!empty($restaurant['image'])): ?>
                                <img src="<?= safeOutput($restaurant['image']) ?>" class="thumb" alt="cover">
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= safeOutput($restaurant['name']) ?></td>
                        <td><?= safeOutput($restaurant['title']) ?></td>
                        <td><?= safeOutput($restaurant['city']) ?></td>
                        <td><?= safeOutput($restaurant['location']) ?></td>
                        <td><?= safeOutput($restaurant['overview']) ?></td>
                        <td><?= safeOutput($restaurant['rating']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="empty">No restaurant data found.</p>
    <?php endif; ?>

</body>

</html>