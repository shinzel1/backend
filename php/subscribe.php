<?php
$allowed_origin = "https://crowndevour.com"; // Change this to your domain
if ($_SERVER['HTTP_ORIGIN'] !== $allowed_origin) {
    http_response_code(403); // Forbidden
    echo json_encode(["error" => "Unauthorized request"]);
    exit;
}

header("Access-Control-Allow-Origin: $allowed_origin");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request (for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit;
}

$valid_api_key = "FIZE3Rffi2UbSDjYn59xiPlD7WrupwUj2STwA7BMeRFXdUscRk"; // Change this to a secure value

if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== $valid_api_key) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized request"]);
    exit;
}

$host = "localhost";
$dbname = "u173849767_crowndevour";
$username = "u173849767_crowndevour1";
$password = "e~Ou^k74R";

// Connect to database using PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Get JSON request data
$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ? $data["email"] : $data["email1"];

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email address"]);
    exit;
}

try {
    // Insert email into the database
    $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (:email)");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    echo json_encode(["message" => "Subscription successful!"]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Email already subscribed or server error"]);
}
?>
