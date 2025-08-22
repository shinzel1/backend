<?php
require_once '../db.php';
session_start();

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->execute([$id]);
}
header('Location: index.php');
exit;
