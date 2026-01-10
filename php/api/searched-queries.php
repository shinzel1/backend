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
    // Query to fetch query data
    $stmt = $pdo->query("SELECT id, query,searched_at FROM search_queries ORDER BY id DESC");
    $searchedQuery = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <?php require_once './navbar/nav.php'; ?>
    <h1>Uploaded Restaurants</h1>

    <?php if (!empty($searchedQuery)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>query</th>
                    <th>date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($searchedQuery as $query): ?>
                    <tr>
                        <td><?= safeOutput($query['id']) ?></td>
                        <td><?= safeOutput($query['query']) ?></td>
                        <td><?= safeOutput($query['searched_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="empty">No query data found.</p>
    <?php endif; ?>

</body>

</html>