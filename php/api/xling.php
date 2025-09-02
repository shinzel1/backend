<?php
// Database connection
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once './db.php'; // ✅ Fixed path
function getUserIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    elseif (isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    elseif (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    elseif (isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';

    return $ipaddress;
}

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
