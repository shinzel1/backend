<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ../admin-login.php');
    exit;
}
require_once '../db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM recipes WHERE id=?");
if ($stmt->execute([$id])) {
    echo "Recipe deleted!";
} else {
    echo "Error deleting recipe.";
}
?>
