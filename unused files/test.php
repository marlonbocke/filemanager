<?php
echo "PHP works. Memory: " . ini_get('memory_limit') . "\n";
echo "Session status: " . session_status() . "\n";
$file = '/var/www/html/filemanager/IMG_1022 2.MOV';
if (file_exists($file)) {
    echo "File exists: " . filesize($file) . " bytes\n";
} else {
    echo "File not found\n";
}
