<?php
session_start();

if (!isset($_SESSION['active']) || $_SESSION['active'] !== true) {
    http_response_code(401);
    die(json_encode(['error' => 'Nicht autorisiert']));
}

$uploadDir = '/mnt/hdd_4T/filemanager_data/';

if (isset($_POST['path']) && str_starts_with($_POST['path'], $uploadDir)) {
    $uploadDir = rtrim($_POST['path'], '/') . '/';
}

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'mp4', 'mov', 'avi', 'mkv', 'mp3', 'wav', 'zip', 'rar', '7z', 'tar', 'gz'];
$maxFileSize = 500 * 1024 * 1024;

$uploadedFiles = [];
$errors = [];

if (isset($_FILES['files'])) {
    $files = $_FILES['files'];
    
    if (is_array($files['name'])) {
        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $result = processFile($files['tmp_name'][$i], $files['name'][$i], $uploadDir, $allowedExtensions, $maxFileSize);
                if (isset($result['success'])) {
                    $uploadedFiles[] = $result['success'];
                } else {
                    $errors[] = $result['error'];
                }
            } else {
                $errors[] = "Fehler beim Upload von: " . htmlspecialchars($files['name'][$i]);
            }
        }
    } else {
        if ($files['error'] === UPLOAD_ERR_OK) {
            $result = processFile($files['tmp_name'], $files['name'], $uploadDir, $allowedExtensions, $maxFileSize);
            if (isset($result['success'])) {
                $uploadedFiles[] = $result['success'];
            } else {
                $errors[] = $result['error'];
            }
        } else {
            $errors[] = "Fehler beim Upload";
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => count($uploadedFiles) > 0,
    'uploaded' => $uploadedFiles,
    'errors' => $errors
]);

function processFile($tmpName, $fileName, $uploadDir, $allowedExtensions, $maxFileSize) {
    $fileName = basename($fileName);
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $allowedExtensions)) {
        return ['error' => "Nicht erlaubter Dateityp: " . htmlspecialchars($extension)];
    }
    
    if (filesize($tmpName) > $maxFileSize) {
        return ['error' => "Datei zu groß: " . htmlspecialchars($fileName) . " (max. 500MB)"];
    }
    
    $targetPath = generateSafeFilename($uploadDir . $fileName);
    
    if (move_uploaded_file($tmpName, $targetPath)) {
        chmod($targetPath, 0664);
        return [
            'success' => [
                'name' => basename($targetPath),
                'size' => filesize($targetPath),
                'path' => $targetPath
            ]
        ];
    } else {
        return ['error' => "Fehler beim Speichern von: " . htmlspecialchars($fileName)];
    }
}

function generateSafeFilename($path) {
    if (!file_exists($path)) {
        return $path;
    }
    
    $info = pathinfo($path);
    $dirname = $info['dirname'];
    $filename = $info['filename'];
    $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
    
    $counter = 1;
    while (file_exists($path)) {
        $path = $dirname . '/' . $filename . '_' . $counter . $extension;
        $counter++;
    }
    
    return $path;
}
