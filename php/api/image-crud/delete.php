<?php
require_once '../db.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Get image path
    $stmt = $pdo->prepare("SELECT filepath FROM images WHERE id=:id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if (file_exists($row['filepath'])) {
            unlink($row['filepath']); // delete from server
        }

        $stmt = $pdo->prepare("DELETE FROM images WHERE id=:id");
        $stmt->execute([':id' => $id]);
    }

    header("Location: index.php");
    exit;
}
?>
