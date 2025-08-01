<?php
require_once __DIR__ . '/db.php'; // Adjust path as needed
require_once __DIR__ . '/../vendor/autoload.php'; // Adjust path as needed
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
$stmt = $pdo->query("SELECT * FROM push_subscriptions");
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($subs as $sub) {
    $subscription = Subscription::create([
        'endpoint' => $sub['endpoint'],
        'publicKey' => $sub['p256dh'],
        'authToken' => $sub['auth'],
    ]);
    $webPush->queueNotification($subscription, json_encode($dataPack,
    ));
}

foreach ($webPush->flush() as $report) {
    if (!$report->isSuccess()) {
        $endpoint = $report->getRequest()->getUri()->__toString();
        $del = $pdo->prepare("DELETE FROM push_subscriptions WHERE endpoint = :endpoint");
        $del->execute([':endpoint' => $endpoint]);
    }
}
?>