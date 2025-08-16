<?php
require_once '../db.php';
if (!isset($_GET['id'])) {
    die("Image not found.");
}

$id = (int) $_GET['id'];

// Fetch old image
$stmt = $pdo->prepare("SELECT * FROM images WHERE id=:id");
$stmt->execute([':id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Image not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = "uploads/";
    $fileName = basename($_FILES["image"]["name"]);
    $newFileName = time() . "_" . $fileName;
    $targetFile = $targetDir . $newFileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg','jpeg','png','gif','webp'];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            // Delete old file
            if (file_exists($row['filepath'])) {
                unlink($row['filepath']);
            }

            // Update DB
            $stmt = $pdo->prepare("UPDATE images SET filename=:filename, filepath=:filepath WHERE id=:id");
            $stmt->execute([
                ':filename' => $newFileName,
                ':filepath' => $targetFile,
                ':id'       => $id
            ]);

            header("Location: index.php");
            exit;
        }
    }
}
?>

<h2>Update Image</h2>
<img src="<?= $row['filepath'] ?>" width="200"><br><br>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="image" required>
    <button type="submit">Replace</button>
</form>
