<?php 
session_start();
$logFile = 'debug.log';
//Function to delete a directory and its contents recursively
// Handling deletion requests

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Forbidden');
}

if (!isset($_POST['delete'] )) {
  http_response_code(400);
  exit('Bad Request');
} 

    $absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];
    $data = basename($_POST['delete'] ?? '');
    require_once 'checkpermissions.php';
    $realFile = checkP_DownloadOpenDelete($absoluteCpath, $data);


if (!$realFile) {
   $logLines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
   $lastError = !empty($logLines) ? end($logLines) : 'umknown error';
    exit('Forbidden' .$lastError);
}

   if(is_file($realFile)) 
    {

      if (unlink($realFile)) {
           echo '<script>console.log("File deleted successfully.");</script>';
           goback();
      } else {
          echo '<script>console.log("Error deleting file.");</script>';
          exit;
      }
    } elseif(is_dir($realFile)) 
    {

      if (deleteDirectory($realFile)) {
          echo '<script>console.log("Directory deleted successfully.");</script>';
          goback();
       } else {
          echo '<script>console.log("Error deleting directory.");</script>';
           exit;
          }

      } else {
          echo '<script>console.log("The specified path is neither a file nor a directory.");</script>';
          exit;
     }




function deleteDirectory($delete_dir)
{
  if (!is_dir($delete_dir)) {
    
    return false;
  }
  
  $items = scandir($delete_dir);
  foreach ($items as $item) {
  
    if ($item === '.' || $item === '..') {
      continue;
    }
    $itemPath = $delete_dir . DIRECTORY_SEPARATOR . $item;
    if (is_dir($itemPath)) {
      deleteDirectory($itemPath); // Rekursive Löschung für Unterverzeichnisse
    } else {
      unlink($itemPath); // Datei löschen

  
    }
  }
  rmdir($delete_dir); // Leeres Verzeichnis löschen
  return true;

}

function goback()
{
  echo '<script>console.log("Returning to main page.");</script>';
      
  if (isset($_SESSION['filemanager_image']) ) 
  {
    unset($_SESSION['filemanager_image']);
  } else if (isset($_SESSION['filemanager_movie'])) {
    unset($_SESSION['filemanager_movie']);
  } else if (isset($_SESSION['filemanager_textfile'])) {
    unset($_SESSION['filemanager_textfile']);
  } else if (isset($_SESSION['filemanager_pdf'])) {
    unset($_SESSION['filemanager_pdf']);
  }
   header('Location: main.php'); // Redirect to main.php without any path
   exit; 

}


// function isPathWithinBase($path, $baseDir) {
//     $realPath = realpath($path);
//     $realBaseDir = realpath($baseDir);
    
//     if ($realPath === false || $realBaseDir === false) {
//         return false; // Ungültiger Pfad
//     }
    
//     return str_starts_with($realPath, $realBaseDir);
// }
