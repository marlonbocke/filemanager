<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Forbidden');
}

if (empty($_POST['file']) || empty($_POST['ID'])|| empty($_POST['button'])) {
    http_response_code(400);
    exit('Bad Request');
}

$data = basename($_POST['file'] ?? '');
$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];
$ID = $_POST['ID'] ?? '';
$button = $_POST['button'];

require_once 'checkpermissions.php';
$realFile = checkP_DownloadOpenDelete($absoluteCpath, $data);
$filename = basename($realFile);

if(!isset($realFile) || $realFile==false) {
    http_response_code(403);
    exit('Forbidden: You do not have permission to access this file.');
}
 
if ($button == 'openfile' ) {
    handlePreview($realFile, $filename);
} elseif ($button == 'openfileWindow') {
    handleWindow($realFile, $filename);   
} else {
    http_response_code(400);
    exit('Ungültige Anfrage: ' . htmlspecialchars($button));
}




function handlePreview($realFile, $filename) 
{
   
    if(strtoupper(substr($filename, -4)) == '.MOV' || strtoupper(substr($filename, -4)) == '.MP4' || strtoupper(substr($filename, -5)) == '.WEBM' || strtoupper(substr($filename, -4)) == '.AVI' || strtoupper(substr($filename, -4)) == '.MKV') 
    {
        if(isset($_SESSION['filemanager_movie'])){
            unset($_SESSION['filemanager_movie']);
        }
        $_SESSION['filemanager_movie'] = $realFile;
        exec("php /var/www/html/filemanager/open_movie.php > /dev/null 2>&1 &");  // Redirect output to /dev/null to prevent the parent script from waiting
      
    }
    else if(substr($filename, -4) == '.pdf')
    {   
        if(isset($_SESSION['filemanager_pdf'])){
            unset($_SESSION['filemanager_pdf']);
        }
        $_SESSION['filemanager_pdf'] = $realFile;
        exec("php /var/www/html/filemanager/open_pdf.php > /dev/null 2>&1 &");
        
    }
    else if(substr($filename, -5) == '.jpeg' || substr($filename, -4) == '.jpg' || substr($filename, -4) == '.png' || substr($filename, -4) == '.gif' || substr($filename, -4) == '.webp' || substr($filename, -4) == '.PNG')
    {
        if(isset($_SESSION['filemanager_image'])){
            unset($_SESSION['filemanager_image']);
        }
        $_SESSION['filemanager_image'] = $realFile; 
    }   
    else {
    exit("Something went wrong");
    }    
    header('Location: main.php');
    exit;

}

function handleWindow($realFile, $filename)
{  

    if(strtoupper(substr($filename, -4)) == '.MOV' || strtoupper(substr($filename, -4)) == '.MP4' || strtoupper(substr($filename, -5)) == '.WEBM' || strtoupper(substr($filename, -4)) == '.AVI' || strtoupper(substr($filename, -4)) == '.MKV') 
    {
        if(isset($_SESSION['filemanager_movie'])){
            unset($_SESSION['filemanager_movie']);
        }
        $_SESSION['movie_window'] = $realFile;
        exec("php /var/www/html/filemanager/open_movie.php > /dev/null 2>&1 &");  // Redirect output to /dev/null to prevent the parent script from waiting
    }
    else if(substr($filename, -4) == '.pdf')
    {   
        if(isset($_SESSION['filemanager_pdf'])){
            unset($_SESSION['filemanager_pdf']);
        }
        $_SESSION['pdf_window'] = $realFile;
        exec("php /var/www/html/filemanager/open_pdf.php > /dev/null 2>&1 &");   
    }
    else if(substr($filename, -5) == '.jpeg' || substr($filename, -4) == '.jpg' || substr($filename, -4) == '.png' || substr($filename, -4) == '.gif' || substr($filename, -4) == '.webp' || substr($filename, -4) == '.PNG')
    {
        if(isset($_SESSION['filemanager_image'])){
            unset($_SESSION['filemanager_image']);
        }
        $_SESSION['image_window'] = $realFile;
        exec("php /var/www/html/filemanager/open_pdf.php > /dev/null 2>&1 &");  
    }   
    else {
    exit("Something went wrong");
    }    

    header('Location: main.php');
    exit;


}
