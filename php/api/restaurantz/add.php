<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
require_once '../db.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonInput = trim($_POST['restaurant_json']);

    // Validate JSON
    $data = json_decode($jsonInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = "Invalid JSON: " . json_last_error_msg();
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO restaurants (
                name, city, restaurantOrCafe, title, location, overview, shortDescription,
                ambiance_description, ambiance_features, cuisine_description, cuisine_menu_sections,
                must_try, service_description, service_style, reasons_to_visit, tips_for_visitors,
                location_details, additional_info, rating, status, category, cuisines, tags,
                locationUrl, image, gallery, menuImage, chef_recommendations, event_hosting,
                nutritional_breakdown, signature_cocktails, delivery, contact_info, reservations,
                created_at
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");

            $stmt->execute([
                $data['name'] ?? null,
                $data['city'] ?? null,
                $data['restaurantOrCafe'] ?? null,
                $data['title'] ?? null,
                $data['location'] ?? null,
                $data['overview'] ?? null,
                $data['shortDescription'] ?? null,

                $data['ambiance_description'] ?? null,
                isset($data['ambiance_features']) ? json_encode($data['ambiance_features']) : null,
                $data['cuisine_description'] ?? null,
                isset($data['cuisine_menu_sections']) ? json_encode($data['cuisine_menu_sections']) : null,

                isset($data['must_try']) ? json_encode($data['must_try']) : null,
                $data['service_description'] ?? null,
                $data['service_style'] ?? null,
                isset($data['reasons_to_visit']) ? json_encode($data['reasons_to_visit']) : null,
                isset($data['tips_for_visitors']) ? json_encode($data['tips_for_visitors']) : null,

                isset($data['location_details']) ? json_encode($data['location_details']) : null,
                isset($data['additional_info']) ? json_encode($data['additional_info']) : null,
                $data['rating'] ?? null,
                ($data['status'] ?? '') === "open" ? 1 : 0,
                isset($data['category']) ? json_encode($data['category']) : null,
                isset($data['cuisines']) ? json_encode($data['cuisines']) : null,
                isset($data['tags']) ? json_encode($data['tags']) : null,

                $data['locationUrl'] ?? null,
                $data['image'] ?? null,
                isset($data['gallery']) ? json_encode($data['gallery']) : null,
                isset($data['menuImage']) ? json_encode($data['menuImage']) : null,

                isset($data['chef_recommendations']) ? json_encode($data['chef_recommendations']) : null,
                isset($data['event_hosting']) ? json_encode($data['event_hosting']) : null,
                isset($data['nutritional_breakdown']) ? json_encode($data['nutritional_breakdown']) : null,
                isset($data['signature_cocktails']) ? json_encode($data['signature_cocktails']) : null,
                isset($data['delivery']) ? (int) $data['delivery'] : 0,
                isset($data['contact_info']) ? json_encode($data['contact_info']) : null,
                isset($data['reservations']) ? json_encode($data['reservations']) : null
            ]);

            $success = "Restaurant added successfully!";
        } catch (Exception $e) {
            $error = "Error inserting data: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Restaurant (JSON)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        textarea {
            font-family: monospace;
        }
    </style>
</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="container mt-5">
        <h2>Add Restaurant (via JSON)</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" class="card p-4 shadow-sm bg-white">
            <div class="mb-3">
                <label class="form-label">Restaurant JSON</label>
                <textarea name="restaurant_json" class="form-control" rows="25"
                    required><?= isset($_POST['restaurant_json']) ? htmlspecialchars($_POST['restaurant_json']) : '' ?></textarea>
                <small class="text-muted">Paste valid JSON structure here.</small>
            </div>

            <button type="submit" class="btn btn-primary">Save Restaurant</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>

</html>