<?php
// Optimized streaming - chunked reading to avoid memory exhaustion
session_start();
$file = $_SESSION['filemanager_movie'] ?? null;
session_write_close();

if (!$file || !file_exists($file)) {
    http_response_code(404);
    exit;
}

$size = filesize($file);
$type = mime_content_type($file);

$start = 0;
$end = $size - 1;

if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
    $start = max(0, intval($matches[1] ?? 0));
    $end = isset($matches[2]) && is_numeric($matches[2]) ? min($size - 1, intval($matches[2])) : $size - 1;
    if ($start > $end) {
        http_response_code(416);
        exit;
    }
}

header('Content-Type: ' . $type);
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=86400');
header('Content-Length: ' . ($end - $start + 1));

if (isset($_SERVER['HTTP_RANGE'])) {
    http_response_code(206);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
}

// Chunked streaming - 256KB chunks
$fp = fopen($file, 'rb');
fseek($fp, $start);
$chunkSize = 256 * 1024; // 256KB
$remaining = $end - $start + 1;

while ($remaining > 0) {
    $toRead = min($chunkSize, $remaining);
    $data = fread($fp, $toRead);
    echo $data;
    flush();
    $remaining -= strlen($data);
}
fclose($fp);