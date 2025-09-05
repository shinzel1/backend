<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../admin-login.php');

    exit;
}
require_once '../db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    die("Invalid offer ID.");
}

// Delete mappings first
$pdo->prepare("DELETE FROM event_restaurant WHERE event_id = ?")->execute([$id]);

// Delete offer
$pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);

header("Location: index.php?deleted=1");
exit;
?>
