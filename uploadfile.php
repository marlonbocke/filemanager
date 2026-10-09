<?php
session_start();
header('Content-Type: application/json');
$response = [
    "success" => false,
    "files" => [],
    "errors" => [],
    
];

if (empty($_FILES['uploaded_file'])) {
    $response["errors"][] = "No files uploaded";
    echo json_encode($response);
    exit;
}
$newPath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

foreach ($_FILES['uploaded_file']['tmp_name'] as $index => $tmpName) {
    $originalName = basename($_FILES['uploaded_file']['name'][$index]);
    $targetFile = rtrim($newPath, '/') . '/' . $originalName;
    
    if (move_uploaded_file($tmpName, $targetFile)) {
        $response["files"][] = $originalName;
        $response["success"] = true;
    } else {
        $response["errors"][] = "Failed: " . $targetFile;
    }
}

echo json_encode($response);
