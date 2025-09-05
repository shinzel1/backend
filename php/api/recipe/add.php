<?php 
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'slug' => $_POST['slug'],
        'ingredients' => json_encode(explode("\n", $_POST['ingredients'])), 
        // now storing instructions as plain HTML (not line breaks)
        'instructions' => $_POST['instructions'],
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

    $sql = "INSERT INTO recipes 
    (title, description, slug, ingredients, instructions, tags, recipe_category, recipe_cuisine, prep_time, cook_time, video_url, aggregate_rating, rating_count, nutrition, cover_image) 
    VALUES (:title, :description, :slug, :ingredients, :instructions, :tags, :recipe_category, :recipe_cuisine, :prep_time, :cook_time, :video_url, :aggregate_rating, :rating_count, :nutrition, :cover_image)";

    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($data)) {
        $message = "<div class='alert alert-success'>Recipe added successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error adding recipe.</div>";
    }
}
?>

<?php require_once '../navbar/navbar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Recipe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-lg rounded-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Add Recipe</h2>
                </div>
                <div class="card-body">

                    <?php if (!empty($message)) echo $message; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Ingredients (one per line)</label>
                            <textarea name="ingredients" class="form-control" rows="4"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Instructions (Rich Text)</label>
                            <textarea name="instructions" id="instructions" class="form-control" rows="6"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" name="tags" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input type="text" name="recipe_category" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cuisine</label>
                            <input type="text" name="recipe_cuisine" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Prep Time</label>
                            <input type="text" name="prep_time" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Cook Time</label>
                            <input type="text" name="cook_time" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Video URL</label>
                            <input type="url" name="video_url" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Aggregate Rating</label>
                            <input type="number" step="0.1" name="aggregate_rating" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Rating Count</label>
                            <input type="number" name="rating_count" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Nutrition (JSON)</label>
                            <textarea name="nutrition" class="form-control" rows="3" placeholder='{"calories":200,"protein":"10g"}'></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Cover Image URL</label>
                            <input type="text" name="cover_image" class="form-control">
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-success px-4">Save Recipe</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#instructions'))
        .catch(error => {
            console.error(error);
        });
</script>

</body>
</html>
