<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once './db.php';

try {

    $payload = json_decode(file_get_contents("php://input"), true);

    $city = $payload['city'] ?? null;
    $type = $payload['type'] ?? null;
    $locality = $payload['locality'] ?? null;
    $cuisine = $payload['cuisine'] ?? null;
    $category = $payload['category'] ?? null;
    $tag = $payload['tag'] ?? null;
    $minRating = $payload['rating'] ?? null;

    // 💰 PRICE FILTER
    $minPrice = $payload['min_price'] ?? null;
    $maxPrice = $payload['max_price'] ?? null;

    function normalize($value)
    {
        return strtolower(trim(str_replace('-', ' ', $value)));
    }

    /* ---------------- BASE QUERY ---------------- */

    $sql = "
        SELECT *
        FROM restaurants
        WHERE 
    ";

    $params = [];

    /* ---------------- FILTERS ---------------- */

    if ($city) {
        $sql .= " AND LOWER(city) LIKE :city";
        $params[':city'] = '%' . normalize($city) . '%';
    }
    if ($locality) {
        $sql .= " AND LOWER(location) LIKE :locality";
        $params[':locality'] = '%' . normalize($locality) . '%';
    }
    if ($type) {
        $sql .= " AND LOWER(restaurantOrCafe) = :type";
        $params[':type'] = normalize($type);
    }

    if ($cuisine) {
        $sql .= " AND LOWER(cuisines) LIKE :cuisine";
        $params[':cuisine'] = '%' . normalize($cuisine) . '%';
    }

    if ($category) {
        $sql .= " AND LOWER(category) LIKE :category";
        $params[':category'] = '%' . normalize($category) . '%';
    }

    if ($tag) {
        $sql .= " AND LOWER(tags) LIKE :tag";
        $params[':tag'] = '%' . normalize($tag) . '%';
    }

    if ($minRating) {
        $sql .= " AND rating >= :rating";
        $params[':rating'] = (float) $minRating;
    }

    /* ---------------- PRICE FILTER ---------------- */

    if ($minPrice || $maxPrice) {
        $sql .= "
        AND (
            CAST(
                REPLACE(
                    SUBSTRING_INDEX(
                        JSON_UNQUOTE(JSON_EXTRACT(additional_info, '$.price_for_two')),
                        '-', 1
                    ),
                ',', ''
                ) AS UNSIGNED
            ) >= :minPrice
        ";

        $params[':minPrice'] = (int) ($minPrice ?? 0);

        if ($maxPrice) {
            $sql .= "
            AND CAST(
                REPLACE(
                    SUBSTRING_INDEX(
                        SUBSTRING_INDEX(
                            JSON_UNQUOTE(JSON_EXTRACT(additional_info, '$.price_for_two')),
                            '-', -1
                        ),
                    ' ', 1
                    ),
                ',', ''
                ) AS UNSIGNED
            ) <= :maxPrice
            ";

            $params[':maxPrice'] = (int) $maxPrice;
        }
    }

    /* ---------------- SORT ---------------- */

    $sql .= " ORDER BY rating DESC, created_at DESC";

    /* ---------------- EXECUTE ---------------- */

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database Error",
        "message" => $e->getMessage()
    ]);
}
