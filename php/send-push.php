<?php
require __DIR__ . '/vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

$host = "localhost";
$dbname = "crowndevour";
$username = "root";
$password = "";

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

$stmt = $pdo->query("SELECT * FROM subscriptions");
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// VAPID keys
$publicKey = 'BH3891gdPHlFTALvUamc88t8A7iK4b3EKtijMzcUHc4FJbSfazYlGoMk8Kk2fEeMG43zxc0BcK5rXYtRikaoQ74';
$privateKey = '_psGGLd-H-h5fIeaKh7njTuW8QMQ0YKwlqe-uphf-BU';

$auth = [
    'VAPID' => [
        'subject' => 'mailto:crowndevour@gmail.com',
        'publicKey' => $publicKey,
        'privateKey' => $privateKey,
    ],
];

$webPush = new WebPush($auth);

foreach ($subscriptions as $sub) {
    $subscription = Subscription::create([
        'endpoint' => $sub['endpoint'],
        'keys' => [
            'p256dh' => $sub['p256dh'],
            'auth' => $sub['auth'],
        ],
    ]);

    $payload = json_encode([
        'title' => '📝 New Blog Alert!',
        'body' => 'A new blog was just published on CrownDevour!',
        'url' => 'https://crowndevour.com/blog', // Customize this
    ]);

    $webPush->queueNotification($subscription, $payload);
}

foreach ($webPush->flush() as $report) {
    echo $report->isSuccess() ? '✅ Sent to ' . $report->getEndpoint() : '❌ Failed';
}
?>