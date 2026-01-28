<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './db.php';
require_once './auth.php';

/* ------------------------------------
   Auth (optional)
------------------------------------ */
$headers = getallheaders();
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    $user = verifyJWT($token);
}

/* ------------------------------------
   Pagination params
------------------------------------ */
$page  = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 9;
$offset = ($page - 1) * $limit;

try {

    /* ------------------------------------
       Total count
    ------------------------------------ */
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM blogs");
    $total = (int) $totalStmt->fetchColumn();

    /* ------------------------------------
       Fetch blogs
    ------------------------------------ */

    if ($limit === -1) {
        $stmt = $pdo->query("SELECT * FROM blogs ORDER BY id DESC");
    } else {
        $stmt = $pdo->prepare("
            SELECT * FROM blogs 
            ORDER BY id DESC 
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    }

    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "blogs" => $blogs,
        "total" => $total
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "message" => "Database error",
        "error" => $e->getMessage()
    ]);
}
