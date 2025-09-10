<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../admin-login.php');

    exit;
}
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $code = trim($_POST['code']);
    $discount_type = $_POST['discount_type'];
    $discount_value = $_POST['discount_value'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Insert into offers table
    $stmt = $pdo->prepare("INSERT INTO offers 
        (title, description, code, discount_type, discount_value, start_date, end_date, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$title, $description, $code, $discount_type, $discount_value, $start_date, $end_date, $is_active]);

    $offer_id = $pdo->lastInsertId();

    // Handle restaurant mappings if provided
    if (!empty($_POST['restaurant_ids'])) {
        foreach ($_POST['restaurant_ids'] as $restaurant_id) {
            $mapStmt = $pdo->prepare("INSERT INTO offer_restaurant (offer_id, restaurant_id) VALUES (?, ?)");
            $mapStmt->execute([$offer_id, intval($restaurant_id)]);
        }
    }

    header("Location: index.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Add Offer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Add New Offer</h2>
        <form method="post" class="card p-4 shadow-sm bg-white">

            <!-- Restaurant Multi-Select -->
            <div class="mb-3">
                <label class="form-label">Select Restaurants (Optional)</label>
                <select name="restaurant_ids[]" id="restaurant_ids" class="form-control" multiple></select>
                <div class="form-text">Leave empty to create a <strong>general offer</strong> (applies site-wide).</div>
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
                <label class="form-label">Offer Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Coupon Code</label>
                <input type="text" name="code" class="form-control">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Discount Type</label>
                    <select name="discount_type" class="form-control">
                        <option value="percentage">Percentage</option>
                        <option value="flat">Flat</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Discount Value</label>
                    <input type="number" step="0.01" name="discount_value" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck" checked>
                <label class="form-check-label" for="activeCheck">Active</label>
            </div>

            <button type="submit" class="btn btn-primary">Save Offer</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>

</html>