<?php
// CORS Headers for Preflight and actual requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No content
    exit();
}

require_once './db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['endpoint'], $data['keys']['auth'], $data['keys']['p256dh'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid data"]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM push_subscriptions WHERE endpoint = :endpoint");
    $stmt->execute([':endpoint' => $data['endpoint']]);

    if (!$stmt->fetchColumn()) {
        $insert = $pdo->prepare("INSERT INTO push_subscriptions (endpoint, auth, p256dh) VALUES (:endpoint, :auth, :p256dh)");
        $insert->execute([
            ':endpoint' => $data['endpoint'],
            ':auth'     => $data['keys']['auth'],
            ':p256dh'   => $data['keys']['p256dh'],
        ]);
    }

    echo json_encode(["status" => "saved"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
}
