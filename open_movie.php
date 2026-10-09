<?php
// Optimized video streaming with chunked output & range support
session_start();
$file = $_SESSION['filemanager_movie'] ?? null;
$moviewindow = $_SESSION['movie_window'] ?? null;
$file = $_SESSION['movie_window'] ?? null;
 unset($_SESSION['movie_window']);

session_write_close();

echo "It is still a work in progress. Sorry bro :(";



// if (!$file || !file_exists($file)) {
//     http_response_code(404);
//     exit;
// }

// $size = filesize($file);
// $type = mime_content_type($file) ?: 'video/mp4';

// $start = 0;
// $end = $size - 1;

// // HTTP-Range Unterstützung verarbeiten (Ermöglicht spulen & ruckelfreies Laden)
// if (isset($_SERVER['HTTP_RANGE'])) {
//     if (preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
//         $start = intval($matches[1]);
//         if (!empty($matches[2])) {
//             $end = intval($matches[2]);
//         }
//     }
// }

// if ($start > $end || $start >= $size) {
//     http_response_code(416); // Requested Range Not Satisfiable
//     exit;
// }

// $length = $end - $start + 1;

// // HTTP-Header setzen
// header('Content-Type: ' . $type);
// header('Accept-Ranges: bytes');
// header('Cache-Control: public, max-age=86400');

// if (isset($_SERVER['HTTP_RANGE'])) {
//     http_response_code(206); // Partial Content
//     header("Content-Range: bytes {$start}-{$end}/{$size}");
// } else {
//     http_response_code(200);
// }

// header("Content-Length: {$length}");

// // Server-Puffer leeren
// if (ob_get_level()) {
//     ob_end_clean();
// }

// // Datei ab der angeforderten Stelle öffnen und in 64KB Chunks streamen
// $fp = fopen($file, 'rb');
// if ($fp !== false) {
//     fseek($fp, $start);
//     $chunkSize = 1024 * 64; // 64 KB
//     $remaining = $length;

//     while ($remaining > 0 && !feof($fp) && connection_status() == 0) {
//         $toRead = min($chunkSize, $remaining);
//         echo fread($fp, $toRead);
//         flush();
//         $remaining -= $toRead;
//     }
//     fclose($fp);
// }
// exit;
