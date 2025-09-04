<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
require_once '../db.php';
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = $_POST['location'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO events (title, description, start_date, end_date, location, is_active) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $start_date, $end_date, $location, $is_active]);

    $event_id = $pdo->lastInsertId();

    if (!empty($_POST['restaurant_ids'])) {
        foreach ($_POST['restaurant_ids'] as $restaurant_id) {
            $pdo->prepare("INSERT INTO event_restaurant (event_id, restaurant_id) VALUES (?, ?)")
                ->execute([$event_id, intval($restaurant_id)]);
        }
    }

    header("Location: index.php?added=1");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Add Event</h2>
    <form method="post" class="card p-4 shadow-sm bg-white">

        <!-- Restaurant Multi-Select -->
        <div class="mb-3">
            <label class="form-label">Select Restaurants</label>
            <select name="restaurant_ids[]" id="restaurant_ids" class="form-control" multiple></select>
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
            <label class="form-label">Event Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control">
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="is_active" class="form-check-input" id="activeCheck" checked>
            <label class="form-check-label" for="activeCheck">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Add Event</button>
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
