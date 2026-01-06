<?php
require_once '../db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Restaurant ID missing.");
}

// Fetch existing data
$stmt = $pdo->prepare("SELECT * FROM restaurants WHERE id = ?");
$stmt->execute([$id]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    die("Restaurant not found.");
}

$error = "";
$success = "";
function ensureJson($value)
{
    $decoded = json_decode($value, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $value; // already valid JSON
    }
    // try to convert comma-separated input into JSON array
    $parts = array_map('trim', explode(',', $value));
    return json_encode($parts, JSON_UNESCAPED_UNICODE);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE restaurants SET
            name=?, city=?, restaurantOrCafe=?, title=?, location=?, overview=?, shortDescription=?,
            ambiance_description=?, ambiance_features=?, cuisine_description=?, cuisine_menu_sections=?,
            must_try=?, service_description=?, service_style=?, reasons_to_visit=?, tips_for_visitors=?,
            location_details=?, additional_info=?, rating=?, category=?, cuisines=?, tags=?,
            locationUrl=?, image=?, gallery=?, menuImage=?, chef_recommendations=?, event_hosting=?,
            nutritional_breakdown=?, signature_cocktails=?, delivery=?, contact_info=?, reservations=?,faq=?
            WHERE id=?");

        $stmt->execute([
            $_POST['name'],
            $_POST['city'],
            $_POST['restaurantOrCafe'],
            $_POST['title'],
            $_POST['location'],
            $_POST['overview'],
            $_POST['shortDescription'],
            $_POST['ambiance_description'],
            ensureJson($_POST['ambiance_features']),
            $_POST['cuisine_description'],
            ensureJson($_POST['cuisine_menu_sections']),
            ensureJson($_POST['must_try']),
            $_POST['service_description'],
            $_POST['service_style'],
            ensureJson($_POST['reasons_to_visit']),
            ensureJson($_POST['tips_for_visitors']),
            ensureJson($_POST['location_details']),
            ensureJson($_POST['additional_info']),
            $_POST['rating'],
            ensureJson($_POST['category']),
            ensureJson($_POST['cuisines']),   // ✅ fixes your error
            ensureJson($_POST['tags']),
            $_POST['locationUrl'],
            $_POST['image'],
            ensureJson($_POST['gallery']),
            ensureJson($_POST['menuImage']),
            ensureJson($_POST['chef_recommendations']),
            ensureJson($_POST['event_hosting']),
            ensureJson($_POST['nutritional_breakdown']),
            ensureJson($_POST['signature_cocktails']),
            isset($_POST['delivery']) ? 1 : 0,
            ensureJson($_POST['contact_info']),
            ensureJson($_POST['reservations']),
            ensureJson($_POST['faq']),
            $id
        ]);


        $success = "Restaurant updated successfully!";
    } catch (Exception $e) {
        $error = "Error updating data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php require_once '../navbar/navbar.php'; ?>

    <div class="container mt-5">
        <h2>Edit Restaurant: <?= htmlspecialchars($restaurant['name']) ?></h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" class="card p-4 shadow-sm bg-white">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#basic">Basic</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ambiance">Ambiance</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#cuisine">Cuisine</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#service">Service</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#location">Location</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#media">Media</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#other">Other</a></li>
            </ul>

            <div class="tab-content mt-3">
                <!-- Basic Info -->
                <div class="tab-pane fade show active" id="basic">
                    <div class="mb-3"><label>Name</label>
                        <input type="text" name="name" class="form-control"
                            value="<?= htmlspecialchars($restaurant['name']) ?>">
                    </div>
                    <div class="mb-3"><label>City</label>
                        <input type="text" name="city" class="form-control"
                            value="<?= htmlspecialchars($restaurant['city']) ?>">
                    </div>
                    <div class="mb-3"><label>Restaurant or Cafe</label>
                        <input type="text" name="restaurantOrCafe" class="form-control"
                            value="<?= htmlspecialchars($restaurant['restaurantOrCafe']) ?>">
                    </div>
                    <div class="mb-3"><label>Title</label>
                        <input type="text" name="title" class="form-control"
                            value="<?= htmlspecialchars($restaurant['title']) ?>">
                    </div>
                    <div class="mb-3"><label>Location</label>
                        <input type="text" name="location" class="form-control"
                            value="<?= htmlspecialchars($restaurant['location']) ?>">
                    </div>
                    <div class="mb-3"><label>Overview</label>
                        <textarea rows="6" name="overview"
                            class="form-control"><?= htmlspecialchars($restaurant['overview']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Short Description</label>
                        <input type="text" name="shortDescription" class="form-control"
                            value="<?= htmlspecialchars($restaurant['shortDescription']) ?>">
                    </div>
                    <div class="mb-3"><label>Rating</label>
                        <input type="number" step="0.1" name="rating" class="form-control"
                            value="<?= htmlspecialchars($restaurant['rating']) ?>">
                    </div>
                </div>

                <!-- Ambiance -->
                <div class="tab-pane fade" id="ambiance">
                    <div class="mb-3"><label>Ambiance Description</label>
                        <textarea rows="6" name="ambiance_description"
                            class="form-control"><?= htmlspecialchars($restaurant['ambiance_description']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Ambiance Features (JSON)</label>
                        <textarea rows="6" name="ambiance_features"
                            class="form-control"><?= htmlspecialchars($restaurant['ambiance_features']) ?></textarea>
                    </div>
                </div>

                <!-- Cuisine -->
                <div class="tab-pane fade" id="cuisine">
                    <div class="mb-3"><label>Cuisine Description</label>
                        <textarea rows="6" name="cuisine_description"
                            class="form-control"><?= htmlspecialchars($restaurant['cuisine_description']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Cuisine Menu Sections (JSON)</label>
                        <textarea rows="6" name="cuisine_menu_sections"
                            class="form-control"><?= htmlspecialchars($restaurant['cuisine_menu_sections']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Must Try (JSON)</label>
                        <textarea rows="6" name="must_try"
                            class="form-control"><?= htmlspecialchars($restaurant['must_try']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Chef Recommendations (JSON)</label>
                        <textarea rows="6" name="chef_recommendations"
                            class="form-control"><?= htmlspecialchars($restaurant['chef_recommendations']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Signature Cocktails (JSON)</label>
                        <textarea rows="6" name="signature_cocktails"
                            class="form-control"><?= htmlspecialchars($restaurant['signature_cocktails']) ?></textarea>
                    </div>
                </div>

                <!-- Service -->
                <div class="tab-pane fade" id="service">
                    <div class="mb-3"><label>Service Description</label>
                        <textarea rows="6" name="service_description"
                            class="form-control"><?= htmlspecialchars($restaurant['service_description']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Service Style</label>
                        <input type="text" name="service_style" class="form-control"
                            value="<?= htmlspecialchars($restaurant['service_style']) ?>">
                    </div>
                    <div class="mb-3"><label>Reasons to Visit (JSON)</label>
                        <textarea rows="6" name="reasons_to_visit"
                            class="form-control"><?= htmlspecialchars($restaurant['reasons_to_visit']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Tips for Visitors (JSON)</label>
                        <textarea rows="6" name="tips_for_visitors"
                            class="form-control"><?= htmlspecialchars($restaurant['tips_for_visitors']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Event Hosting (JSON)</label>
                        <textarea rows="6" name="event_hosting"
                            class="form-control"><?= htmlspecialchars($restaurant['event_hosting']) ?></textarea>
                    </div>
                </div>

                <!-- Location -->
                <div class="tab-pane fade" id="location">
                    <div class="mb-3"><label>Location Details (JSON)</label>
                        <textarea rows="6" name="location_details"
                            class="form-control"><?= htmlspecialchars($restaurant['location_details']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Additional Info (JSON)</label>
                        <textarea rows="6" name="additional_info"
                            class="form-control"><?= htmlspecialchars($restaurant['additional_info']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Location URL</label>
                        <input type="text" name="locationUrl" class="form-control"
                            value="<?= htmlspecialchars($restaurant['locationUrl']) ?>">
                    </div>
                    <div class="mb-3"><label>Category (JSON)</label>
                        <textarea rows="6" name="category"
                            class="form-control"><?= htmlspecialchars($restaurant['category']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Cuisines (JSON)</label>
                        <textarea rows="6" name="cuisines"
                            class="form-control"><?= htmlspecialchars($restaurant['cuisines']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Tags (JSON)</label>
                        <textarea rows="6" name="tags"
                            class="form-control"><?= htmlspecialchars($restaurant['tags']) ?></textarea>
                    </div>
                </div>

                <!-- Media -->
                <div class="tab-pane fade" id="media">
                    <div class="mb-3"><label>Main Image URL</label>
                        <input type="text" name="image" class="form-control"
                            value="<?= htmlspecialchars($restaurant['image']) ?>">
                    </div>
                    <div class="mb-3"><label>Gallery (JSON)</label>
                        <textarea rows="6" name="gallery"
                            class="form-control"><?= htmlspecialchars($restaurant['gallery']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Menu Images (JSON)</label>
                        <textarea rows="6" name="menuImage"
                            class="form-control"><?= htmlspecialchars($restaurant['menuImage']) ?></textarea>
                    </div>
                </div>

                <!-- Other -->
                <div class="tab-pane fade" id="other">
                    <div class="mb-3"><label>Contact Info (JSON)</label>
                        <textarea rows="6" name="contact_info"
                            class="form-control"><?= htmlspecialchars($restaurant['contact_info']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Reservations (JSON)</label>
                        <textarea rows="6" name="reservations"
                            class="form-control"><?= htmlspecialchars($restaurant['reservations']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>Nutritional Breakdown (JSON)</label>
                        <textarea rows="6" name="nutritional_breakdown"
                            class="form-control"><?= htmlspecialchars($restaurant['nutritional_breakdown']) ?></textarea>
                    </div>
                    <div class="mb-3"><label>FAQ Breakdown (JSON)</label>
                        <textarea rows="6" name="faq"
                            class="form-control"><?= htmlspecialchars($restaurant['faq']) ?></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="delivery" class="form-check-input" id="deliveryCheck"
                            <?= $restaurant['delivery'] ? "checked" : "" ?>>
                        <label for="deliveryCheck" class="form-check-label">Delivery Available</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
            <a href="index.php" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>