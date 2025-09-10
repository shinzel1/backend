<?php 
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../admin-login.php');
    exit;
}
require_once '../db.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM recipes WHERE id=?");
$stmt->execute([$id]);
$recipe = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'id' => $id,
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'slug' => strtolower(preg_replace('/\s+/', '-', $_POST['slug'])),
        'ingredients' => json_encode(explode("\n", $_POST['ingredients'])),
        'instructions' => $_POST['instructions'], // store raw HTML instead of splitting lines
        'tags' => json_encode(explode(",", $_POST['tags'])),
        'recipe_category' => $_POST['recipe_category'],
        'recipe_cuisine' => $_POST['recipe_cuisine'],
        'prep_time' => $_POST['prep_time'],
        'cook_time' => $_POST['cook_time'],
        'video_url' => $_POST['video_url'],
        'aggregate_rating' => $_POST['aggregate_rating'],
        'rating_count' => $_POST['rating_count'],
        'nutrition' => json_encode($_POST['nutrition']),
        'cover_image' => $_POST['cover_image']
    ];

    $sql = "UPDATE recipes SET 
        title=:title, description=:description, slug=:slug, ingredients=:ingredients,
        instructions=:instructions, tags=:tags, recipe_category=:recipe_category,
        recipe_cuisine=:recipe_cuisine, prep_time=:prep_time, cook_time=:cook_time,
        video_url=:video_url, aggregate_rating=:aggregate_rating, rating_count=:rating_count,
        nutrition=:nutrition, cover_image=:cover_image 
        WHERE id=:id";

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($data)) {
        $message = "<div class='alert alert-success'>Recipe updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating recipe.</div>";
    }
}
?>
<?php require_once '../navbar/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Recipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CKEditor 5 CDN -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg rounded-4">
                <div class="card-header bg-warning text-dark">
                    <h2 class="mb-0">Edit Recipe</h2>
                </div>
                <div class="card-body">

                    <?php if (!empty($message)) echo $message; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['title']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['slug']) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($recipe['description']) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Ingredients (one per line)</label>
                            <textarea name="ingredients" class="form-control" rows="4"><?= implode("\n", json_decode($recipe['ingredients'], true) ?? []) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Instructions</label>
                            <!-- CKEditor will enhance this textarea -->
                            <textarea name="instructions" id="instructions" class="form-control" rows="6"><?= htmlspecialchars($recipe['instructions']) ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" name="tags" class="form-control" 
                                   value="<?= implode(",", json_decode($recipe['tags'], true) ?? []) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input type="text" name="recipe_category" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['recipe_category']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cuisine</label>
                            <input type="text" name="recipe_cuisine" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['recipe_cuisine']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Prep Time</label>
                            <input type="text" name="prep_time" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['prep_time']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cook Time</label>
                            <input type="text" name="cook_time" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['cook_time']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Video URL</label>
                            <input type="url" name="video_url" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['video_url']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Aggregate Rating</label>
                            <input type="number" step="0.1" name="aggregate_rating" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['aggregate_rating']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Rating Count</label>
                            <input type="number" name="rating_count" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['rating_count']) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Nutrition (JSON)</label>
                            <textarea name="nutrition" class="form-control" rows="3"><?= $recipe['nutrition'] ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Cover Image URL</label>
                            <input type="text" name="cover_image" class="form-control" 
                                   value="<?= htmlspecialchars($recipe['cover_image']) ?>">
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary px-4">Update Recipe</button>
                            <a href="index.php" class="btn btn-secondary">Back</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize CKEditor -->
<script>
ClassicEditor
    .create(document.querySelector('#instructions'))
    .catch(error => {
        console.error(error);
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
