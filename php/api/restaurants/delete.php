<?
header('Content-Type: application/json');
require_once '../db.php';
require_once '../auth.php';

$headers = apache_request_headers();
if (!isset($headers['Authorization']) || !verifyJWT(str_replace('Bearer ', '', $headers['Authorization']))) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing 'id'"]);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM restaurants WHERE id = :id");
    $stmt->execute([':id' => $data['id']]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

?>