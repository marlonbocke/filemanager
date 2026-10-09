<?php
/**
 * Cloud Statistics API
 * Liefert Statistiken über den Cloud-Speicher
 */

header('Content-Type: application/json');

// Basis-Verzeichnis
$baseDir = '/storage/filemanager_data';

$stats = [
    'files' => 0,
    'videos' => 0,
    'images' => 0,
    'documents' => 0,
    'storage' => '0 B',
    'lastUpdate' => date('d.m.Y H:i:s')
];

// Video-Extensions
$videoExt = ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm', 'm4v'];

// Image-Extensions
$imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'];

// Document-Extensions
$docExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'];

$totalSize = 0;

/**
 * Rekursive Funktion zum Zählen von Dateien
 */
function countFiles($dir, &$stats, $videoExt, $imageExt, $docExt, &$totalSize) {
    if (!is_dir($dir)) return;
    
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || strpos($item, '.') === 0) continue;
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            countFiles($path, $stats, $videoExt, $imageExt, $docExt, $totalSize);
        } elseif (is_file($path)) {
            $stats['files']++;
            $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
            $size = filesize($path);
            $totalSize += $size;
            
            if (in_array($ext, $videoExt)) {
                $stats['videos']++;
            } elseif (in_array($ext, $imageExt)) {
                $stats['images']++;
            } elseif (in_array($ext, $docExt)) {
                $stats['documents']++;
            }
        }
    }
}

// Dateien zählen
if (is_dir($baseDir)) {
    countFiles($baseDir, $stats, $videoExt, $imageExt, $docExt, $totalSize);
}

// Speicherplatz formatieren
function formatBytes($bytes) {
    if ($bytes == 0) return '0 B';
    
    $k = 1024;
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

$stats['storage'] = formatBytes($totalSize);

echo json_encode($stats, JSON_PRETTY_PRINT);
