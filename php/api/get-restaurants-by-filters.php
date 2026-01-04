<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once './db.php';

try {

    /* ---------------- INPUT (from Next.js) ---------------- */

    $data = json_decode(file_get_contents("php://input"), true);

    $city      = $data['city'] ?? null;
    $locality  = $data['locality'] ?? null;
    $cuisine   = $data['cuisine'] ?? null;
    $price     = $data['price'] ?? null; // example: "1000-2000"
    $type      = $data['type'] ?? null;  // restaurant | cafe

    /* normalize slugs */
    function normalize($value) {
        return strtolower(str_replace("-", " ", trim($value)));
    }

    /* ---------------- BASE QUERY ---------------- */

    $sql = "
        SELECT 
            id,
            title,
            slug,
            city,
            locality,
            cuisines,
            price_for_two,
            restaurantOrCafe,
            rating,
            image
        FROM restaurants
        WHERE status = 1
    ";

    $params = [];

    /* ---------------- FILTERS ---------------- */

    if ($city) {
        $sql .= " AND LOWER(city) = :city";
        $params[':city'] = normalize($city);
    }

    if ($locality) {
        $sql .= " AND LOWER(locality) = :locality";
        $params[':locality'] = normalize($locality);
    }

    if ($type) {
        $sql .= " AND LOWER(restaurantOrCafe) = :type";
        $params[':type'] = normalize($type);
    }

    if ($cuisine) {
        $sql .= " AND LOWER(cuisines) LIKE :cuisine";
        $params[':cuisine'] = "%" . normalize($cuisine) . "%";
    }

    if ($price) {
        [$min, $max] = array_pad(explode("-", $price), 2, null);

        if ($min !== null) {
            $sql .= " AND price_for_two >= :minPrice";
            $params[':minPrice'] = (int)$min;
        }

        if ($max !== null) {
            $sql .= " AND price_for_two <= :maxPrice";
            $params[':maxPrice'] = (int)$max;
        }
    }

    $sql .= " ORDER BY rating DESC, id DESC";

    /* ---------------- EXECUTE ---------------- */

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* ---------------- FORMAT ---------------- */

    foreach ($restaurants as &$r) {
        $r['url'] = "https://crowndevour.com/" . $r['slug'];
        $r['cuisines'] = array_map('trim', explode(",", $r['cuisines']));
    }

    echo json_encode($restaurants, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Database error",
        "error" => $e->getMessage()
    ]);
}
