<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Forbidden');
}


if (empty($_POST['file']) || empty($_POST['ID'])) {
    http_response_code(400);
    exit('Bad Request');
}

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];
$ID = $_POST['ID'] ?? '';

$data = basename($_POST['file'] ?? '');
require_once 'checkpermissions.php';
$realFile = checkP_DownloadOpenDelete($absoluteCpath, $data);


if(!isset($realFile) || $realFile==false) {
    http_response_code(403);
    exit('Forbidden: You do not have permission to access this file.');
}

 if (is_dir($realFile)) 
{   
    $userPath = $absoluteCpath.'/';

    //step1: create a zip file
    $zip = new ZipArchive();
    $zipFilename = $userPath . 'download_' . time() . '.zip';
    if ($zip->open($zipFilename, ZipArchive::CREATE) !== TRUE) {
    exit("Can not create $zipFilename .");
        } 

    //step2: send the zip file to the function
   $added = addFilesToZip($realFile, $zip);
    $closed = $zip->close();
 
    //step4: check the zip file
    if (!$added || !$closed) 
    {
        error_log('ZIP-Erstellung fehlgeschlagen: ' . $zip->getStatusString());
        http_response_code(500);
        exit('ZIP konnte nicht erstellt werden.');
    } else 
    {   
        //step5: download the zip file to the client
        $filePath = $zipFilename;
        $filename = basename($zipFilename);   
    
        //just for debugging
        clearstatcache(true, $filePath);
        error_log('added=' . var_export($added, true));
        error_log('closed=' . var_export($closed, true));
        error_log('ZIP bytes=' . var_export(filesize($filePath), true));


        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: public');
        readfile($filePath);
        unlink($filePath);
        exit;
}
          
} else
{
    $name=basename($realFile);
    header('Content-Type: image/' . pathinfo($name, PATHINFO_EXTENSION));
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Length: ' . filesize($realFile));
    header('Cache-Control: private');
    header('Pragma: public');
    readfile($realFile);
    exit;

 }     


    //step:3 insert all data into the zip file
    function addFilesToZip($realFile,  &$zip, $parentFolder = '') {
    $handle = opendir($realFile);
    if (!$handle) {
        //echo('Something went wrong'. $realFile);
        return false;
    }
    // Loop through all files and directories in the current directory
    while (($file = readdir($handle)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }
        // Construct the full path of the file or directory
        $filePath = $realFile . DIRECTORY_SEPARATOR . $file;
        $localPath = trim($parentFolder . $file, '/');
        
        // If it's a directory, add it to the zip and recursively add its contents
        if (is_dir($filePath)) {
            $zip->addEmptyDir($localPath . '/');
            addFilesToZip($filePath, $zip, $localPath . '/');
        } elseif (file_exists($filePath) && is_readable($filePath)) {
            $zip->addFile($filePath, $localPath);
        }
    }
    
    closedir($handle);
    return true;
}
