<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}
require_once '../db.php';
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM restaurants WHERE id=?");
    $stmt->execute([$_GET['id']]);
}
header("Location: index.php?deleted=1");
exit;
