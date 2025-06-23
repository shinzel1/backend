<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once './db.php'; // ✅ Ensure this path is correct

try {
    $data = json_decode(file_get_contents("php://input"), true);

    // Required fields
    if (
        !$data || 
        !isset($data['title'], $data['slug'], $data['ingredients'], $data['instructions'])
    ) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    // Assign variables with fallbacks
    $title = $data['title'];
    $slug = $data['slug'];
    $description = $data['description'] ?? '';
    $ingredients = $data['ingredients'];
    $instructions = $data['instructions'];
    $tags = json_encode($data['tags'] ?? []);
    $coverImage = $data['cover_image'] ?? '';
    $recipeCategory = $data['recipe_category'] ?? '';
    $recipeCuisine = $data['recipe_cuisine'] ?? '';
    $prepTime = $data['prep_time'] ?? '';
    $cookTime = $data['cook_time'] ?? '';
    $videoUrl = $data['video_url'] ?? '';
    $aggregateRating = $data['aggregate_rating'] ?? null;
    $ratingCount = $data['rating_count'] ?? null;
    $nutrition = $data['nutrition'] ?? '';

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO recipes (
            title, slug, description, ingredients, instructions,
            tags, cover_image, recipe_category, recipe_cuisine,
            prep_time, cook_time, video_url,
            aggregate_rating, rating_count, nutrition
        ) VALUES (
            :title, :slug, :description, :ingredients, :instructions,
            :tags, :cover_image, :recipe_category, :recipe_cuisine,
            :prep_time, :cook_time, :video_url,
            :aggregate_rating, :rating_count, :nutrition
        )
    ");

    $stmt->execute([
        ':title' => $title,
        ':slug' => $slug,
        ':description' => $description,
        ':ingredients' => $ingredients,
        ':instructions' => $instructions,
        ':tags' => $tags,
        ':cover_image' => $coverImage,
        ':recipe_category' => $recipeCategory,
        ':recipe_cuisine' => $recipeCuisine,
        ':prep_time' => $prepTime,
        ':cook_time' => $cookTime,
        ':video_url' => $videoUrl,
        ':aggregate_rating' => $aggregateRating,
        ':rating_count' => $ratingCount,
        ':nutrition' => $nutrition
    ]);

    echo json_encode(["message" => "Recipe uploaded successfully"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
