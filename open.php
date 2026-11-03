<?php
session_start();
$logFile = 'debug.log';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     $_SESSION['error'] = "Some error occurred while processing the request.";
    header('Location: main.php'); // Redirect to main.php without any path
    exit;
}

if (empty($_POST['file']) || empty($_POST['ID'])|| empty($_POST['button'])) {
    $_SESSION['error'] = "Some error occurred while processing the request.";
    header('Location: main.php'); // Redirect to main.php without any path
    exit;
}

$data = basename($_POST['file'] ?? '');
$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];
$ID = $_POST['ID'] ?? '';
$button = $_POST['button'];

require_once 'checkpermissions.php';
$realFile = checkP_DownloadOpenDelete($absoluteCpath, $data);
$filename = basename($realFile);

if(!isset($realFile) || $realFile==false) {
    $_SESSION['error'] = "Some error occurred while processing the request.";
    header('Location: main.php');
    exit;
}

if (!(is_dir($realFile) || is_file($realFile)) ) {
    $_SESSION['error'] = $realFile;
    header('Location: main.php'); // Redirect to main.php without any path
    exit;
}


 
if ($button == 'openfile' ) {
    handlePreview($realFile, $filename);
} elseif ($button == 'openfileWindow') {
    handleWindow($realFile, $filename);   
} else {
    $_SESSION['error'] = "Some error occurred while processing the request.";
    header('Location: main.php'); // Redirect to main.php without any path
    exit;
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
      
    } else  {

        $_SESSION['error'] = "Some went wrong.";
        header('Location: main.php'); // Redirect to main.php without any path
        exit;

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
        $_SESSION['error'] = "Some went wrong.";
        header('Location: main.php');
        exit;
    }

    header('Location: main.php');
    exit;


}
