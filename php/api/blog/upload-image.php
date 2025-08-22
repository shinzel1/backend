<?php
header('Content-Type: application/json');

// Make sure uploads folder exists
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!empty($_FILES['upload']['name'])) {
    $fileName = time() . '_' . basename($_FILES['upload']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['upload']['tmp_name'], $targetFile)) {
        echo json_encode([
            "url" => 'uploads/' . $fileName
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            "error" => [ "message" => "File upload failed." ]
        ]);
    }
    exit;
}

http_response_code(400);
echo json_encode([ "error" => [ "message" => "No file uploaded." ] ]);
