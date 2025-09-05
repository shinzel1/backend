<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../admin-login.php');

    exit;
}
require_once '../db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die("Invalid event ID.");
}

// Fetch event
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("event not found.");
}

// Fetch mapped restaurants
$mapStmt = $pdo->prepare("SELECT restaurant_id FROM event_restaurant WHERE event_id = ?");
$mapStmt->execute([$id]);
$selectedRestaurants = $mapStmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $code = $_POST['code'];
    $discount_type = $_POST['discount_type'];
    $discount_value = $_POST['discount_value'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Update event
    $stmt = $pdo->prepare("UPDATE events SET 
        title=?, description=?, code=?, discount_type=?, discount_value=?, start_date=?, end_date=?, is_active=? 
        WHERE id=?");
    $stmt->execute([$title, $description, $code, $discount_type, $discount_value, $start_date, $end_date, $is_active, $id]);

    // Update mappings
    $pdo->prepare("DELETE FROM event_restaurant WHERE event_id = ?")->execute([$id]);

    if (!empty($_POST['restaurant_ids'])) {
        foreach ($_POST['restaurant_ids'] as $restaurant_id) {
            $mapStmt = $pdo->prepare("INSERT INTO event_restaurant (event_id, restaurant_id) VALUES (?, ?)");
            $mapStmt->execute([$id, intval($restaurant_id)]);
        }
    }

    header("Location: index.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-light">
        <?php require_once '../navbar/navbar.php'; ?>

<div class="container mt-5">
    <h2>Edit event</h2>
    <form method="post" class="card p-4 shadow-sm bg-white">

        <!-- Restaurant Multi-Select -->
        <div class="mb-3">
            <label class="form-label">Select Restaurants</label>
            <select name="restaurant_ids[]" id="restaurant_ids" class="form-control" multiple>
                <?php foreach ($selectedRestaurants as $rid): ?>
                    <?php
                        $rStmt = $pdo->prepare("SELECT id, name FROM restaurants WHERE id = ?");
                        $rStmt->execute([$rid]);
                        $r = $rStmt->fetch(PDO::FETCH_ASSOC);
                        if ($r):
                    ?>
                        <option value="<?= $r['id'] ?>" selected><?= htmlspecialchars($r['name']) ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <script>
            $(document).ready(function () {
                $('#restaurant_ids').select2({
                    placeholder: "Search restaurants...",
                    ajax: {
                        url: '../image-crud/fetch_entities.php',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return { q: params.term, type: 'restaurant' };
                        },
                        processResults: function (data) {
                            return { results: data };
                        },
                        cache: true
                    }
                });
            });
        </script>

        <div class="mb-3">
            <label class="form-label">event Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($event['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Coupon Code</label>
            <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($event['code']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Discount Type</label>
            <select name="discount_type" class="form-control">
                <option value="percentage" <?= $event['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage</option>
                <option value="flat" <?= $event['discount_type'] === 'flat' ? 'selected' : '' ?>>Flat</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Discount Value</label>
            <input type="number" step="0.01" name="discount_value" class="form-control" value="<?= $event['discount_value'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= $event['start_date'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= $event['end_date'] ?>" required>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck" <?= $event['is_active'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="activeCheck">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Update event</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
