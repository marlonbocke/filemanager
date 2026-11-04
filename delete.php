<?php 
session_start();
$logFile = 'debug.log';
//Function to delete a directory and its contents recursively
// Handling deletion requests

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['error'] = "Error: Some error occurred while processing the request.";
  header('Location: main.php'); // Redirect to main.php without any path
  exit;  
    
}

if (!isset($_POST['delete'] )) {
  $_SESSION['error'] = "Error: Some error occurred while processing the request.";
  header('Location: main.php'); // Redirect to main.php without any path
  exit('Bad Request');
} 

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];
$data = basename($_POST['delete'] ?? '');

error_log("delete_basename: " . $data, 3, 'debug.log');
require_once 'checkpermissions.php';
$realFile = checkP_DownloadOpenDelete($absoluteCpath, $data);
error_log("-----------chhecked------------------------ \n", 3, 'debug.log');



if (!(is_dir($realFile) || is_file($realFile)) ) {
  error_log("is not a file or directory: " . $realFile . "\n", 3, 'debug.log');
  $_SESSION['error'] = $realFile;
  header('Location: main.php'); // Redirect to main.php without any path
  exit;

}



if(is_file($realFile)) {

  if (unlink($realFile)) {
      echo '<script>console.log("File deleted successfully.");</script>';
      goback();
  } else {
      echo '<script>console.log("Error deleting file.");</script>';
      exit;
  }

} elseif(is_dir($realFile)) {

  error_log("is directory: " . $realFile . "\n", 3, 'debug.log');
  if (deleteDirectory($realFile)) {
    echo '<script>console.log("Directory deleted successfully.");</script>';
    goback();
  
    } else {
      echo '<script>console.log("Error deleting directory.");</script>';
      exit;  
   }

} else {
      echo '<script>console.log("Error: The specified path is neither a file nor a directory.");</script>';
      exit;
}


function deleteDirectory($delete_dir)
{
  error_log("Function deleteDirectory called with argument: " . $delete_dir . "\n", 3, 'debug.log');
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
