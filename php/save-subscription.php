<?php
$host = "localhost"; 
$dbname = "crowndevour";
$username = "root";
$password = "";

$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

$input = json_decode(file_get_contents("php://input"), true);
$endpoint = $input['endpoint'];
$p256dh = $input['keys']['p256dh'];
$auth = $input['keys']['auth'];

// Check if it already exists
$stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE endpoint = ?");
$stmt->execute([$endpoint]);
$count = $stmt->fetchColumn();

if ($count == 0) {
    $stmt = $conn->prepare("INSERT INTO subscriptions (endpoint, p256dh, auth) VALUES (?, ?, ?)");
    $stmt->execute([$endpoint, $p256dh, $auth]);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'already subscribed']);
}
?>