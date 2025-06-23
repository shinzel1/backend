<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/db.php'; // Adjust path as needed

try {
    // Query to fetch restaurant data
    $stmt = $pdo->query("SELECT id, name, city, location, overview, shortDescription, rating FROM restaurants ORDER BY id DESC");
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
</head>

<body>

    <h1>Uploaded Restaurants</h1>

    <?php if (!empty($restaurants)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
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
                        <td><?= safeOutput($restaurant['name']) ?></td>
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