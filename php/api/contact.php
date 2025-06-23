<?php
// contact.php

// Allow CORS if needed
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Only POST requests are allowed"]);
    exit;
}

// Get raw JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['name'], $data['email'], $data['message'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$name = htmlspecialchars(trim($data['name']));
$email = filter_var(trim(string: $data['email']), FILTER_VALIDATE_EMAIL);
$message = htmlspecialchars(trim($data['message']));

// If email is invalid
if (!$email) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email"]);
    exit;
}

// Connect to your database using PDO
$host = "localhost";
$dbname = "u173849767_crowndevour";
$username = "u173849767_crowndevour1";
$password = "e~Ou^k74R";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $message]);

    echo json_encode(["success" => true, "message" => "Message saved successfully"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>