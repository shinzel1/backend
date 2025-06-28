<?php
require 'vendor/autoload.php';
require_once './db.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

$auth = [
    'VAPID' => [
        'subject' => 'mailto:you@example.com',
        'publicKey' => 'BH3891gdPHlFTALvUamc88t8A7iK4b3EKtijMzcUHc4FJbSfazYlGoMk8Kk2fEeMG43zxc0BcK5rXYtRikaoQ74',
        'privateKey' => '_psGGLd-H-h5fIeaKh7njTuW8QMQ0YKwlqe-uphf-BU',
    ],
];

$webPush = new WebPush($auth);

// Fetch all subscriptions
$stmt = $pdo->query("SELECT * FROM push_subscriptions");
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Notification payload
$payload = json_encode([
    'title' => '🆕 New Blog Post!',
    'body' => 'Check out the latest blog now on CrownDevour.',
]);

foreach ($subscriptions as $sub) {
    $subscription = Subscription::create([
        'endpoint' => $sub['endpoint'],
        'publicKey' => $sub['p256dh'],
        'authToken' => $sub['auth'],
    ]);

    $webPush->sendNotification($subscription, $payload);
}
