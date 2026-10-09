<?php
session_start();

header('Content-Type: application/json');

$json = file_get_contents("php://input");
$data = json_decode($json, true);

$oldname = $data['oldname'] ?? null;
$newname = $data['newname'] ?? null;
$path = $data['path'] ?? null;
$type = $data['type'] ?? null;

if (!$oldname || !$newname || !$path) {
    echo json_encode(['status' => 'error', 'message' => 'Ungültige Parameter.']);
    exit;
}

$oldpath = $path . '/' . $oldname;
$newpath = $path . '/' . $newname;

// Prüfe ob Quelle existiert
if (!file_exists($oldpath) && !is_dir($oldpath)) {
    echo json_encode(['status' => 'error', 'message' => 'Datei/Ordner existiert nicht.']);
    exit;
}

// Prüfe ob Ziel bereits existiert
if (file_exists($newpath) || is_dir($newpath)) {
    echo json_encode(['status' => 'error', 'message' => 'Name existiert bereits.']);
    exit;
}

if (rename($oldpath, $newpath)) {
    // Wenn das aktuelle Element in einer Session ist, aktualisieren
    if (isset($_SESSION['filemanager_image']) && $_SESSION['filemanager_image'] === $oldpath) {
        $_SESSION['filemanager_image'] = $newpath;
    } elseif (isset($_SESSION['filemanager_movie']) && $_SESSION['filemanager_movie'] === $oldpath) {
        $_SESSION['filemanager_movie'] = $newpath;
    } elseif (isset($_SESSION['filemanager_pdf']) && $_SESSION['filemanager_pdf'] === $oldpath) {
        $_SESSION['filemanager_pdf'] = $newpath;
    } elseif (isset($_SESSION['filemanager_textfile']) && $_SESSION['filemanager_textfile'] === $oldpath) {
        $_SESSION['filemanager_textfile'] = $newpath;
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Erfolgreich umbenannt.']);
} else {
    $error = error_get_last();
    echo json_encode(['status' => 'error', 'message' => 'Umbenennung fehlgeschlagen: ' . ($error['message'] ?? 'Unbekannter Fehler')]);
}
?>
