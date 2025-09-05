<?php
require_once '../db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM recipes WHERE id=?");
if ($stmt->execute([$id])) {
    echo "Recipe deleted!";
} else {
    echo "Error deleting recipe.";
}
?>
