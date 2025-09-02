<?php
// Database connection
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once './db.php'; // ✅ Fixed path

$ip = getUserIP(); // use the function from earlier
$page = $_SERVER['REQUEST_URI'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Insert into DB
$stmt = $pdo->prepare("INSERT INTO visitor_ips (ip_address, page_url, user_agent) VALUES (:ip, :page, :agent)");
$stmt->execute([
    ':ip' => $ip,
    ':page' => $page,
    ':agent' => $user_agent
]);

echo "Welcome!!";
?>
