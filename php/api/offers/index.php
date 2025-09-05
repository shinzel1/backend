<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
require_once '../db.php';
// Fetch all offers
$stmt = $pdo->query("SELECT * FROM offers ORDER BY created_at DESC");
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch restaurant mappings for each offer
$offerRestaurants = [];
foreach ($offers as $offer) {
    $mapStmt = $pdo->prepare("SELECT r.name 
                              FROM offer_restaurant orr
                              JOIN restaurants r ON orr.restaurant_id = r.id
                              WHERE orr.offer_id = ?");
    $mapStmt->execute([$offer['id']]);
    $offerRestaurants[$offer['id']] = $mapStmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Offers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="container mt-5">
        <h2>Offers
            <a href="add.php" class="btn btn-success btn-sm">+ Add Offer</a>
        </h2>
        <table class="table table-bordered table-striped mt-3 align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Restaurants</th>
                    <th>Title</th>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Validity</th>
                    <th>Status</th>
                    <th width="160">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($offers): ?>
                    <?php foreach ($offers as $offer): ?>
                        <tr>
                            <td><?= $offer['id'] ?></td>
                            <td>
                                <?php
                                $names = $offerRestaurants[$offer['id']] ?? [];
                                echo $names ? htmlspecialchars(implode(", ", $names)) : "<span class='text-muted'>General Offer</span>";
                                ?>
                            </td>
                            <td><?= htmlspecialchars($offer['title']) ?></td>
                            <td><?= $offer['code'] ? htmlspecialchars($offer['code']) : "-" ?></td>
                            <td><?= $offer['discount_value'] ?> (<?= $offer['discount_type'] ?>)</td>
                            <td><?= $offer['start_date'] ?> → <?= $offer['end_date'] ?></td>
                            <td>
                                <span class="badge <?= $offer['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $offer['is_active'] ? "Active" : "Inactive" ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit.php?id=<?= $offer['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete.php?id=<?= $offer['id'] ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this offer?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No offers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>