<?php 
session_start();    //is important to read the sessions data 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Something went wrong. Please try again later.']);
    exit;
}

use SpomkyLabs\Pki\ASN1\Component\Length;
$json = file_get_contents('php://input');
$jsondata = json_decode($json, true);
$deletefiles = $jsondata['files'];



foreach($deletefiles as $i => $item){
  $data[$i] = basename($item);
 } 

 if (!is_array($data)) {
    echo json_encode(['error' => 'data is not a vaid array']);
    exit;
}
$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

require_once 'checkpermissions.php';
 $realFileArray = checkP_DownloadOpenDelete($absoluteCpath, $data);

 if(!isset($realFileArray) || $realFileArray ==false) {
     echo json_encode(['error' => 'Forbidden: You do not have permission to access this file.']);
     exit;
}

if (!is_array($realFileArray)) {
    echo json_encode(['error' => 'Is not vaid array']);
    exit;
}


try {              
    foreach ($realFileArray as $i => $file) {
    
    if (is_file($file)) {
      
    if(!unlink($file)){
       echo json_encode(['error' => 'Can not delete']);
        exit;
      }

    }else if (is_dir($file)) {
      deleteDirectory($file);
    }
    }

    echo json_encode(['message' => 'Files delted successfully.']);   //this also applies true, if no files have been deleted  
} catch (Throwable $e) {                
    echo json_encode(['message' => $e->getMessage()]);    
}


function deleteDirectory( String $realFileArray)      //Function to delete a directory and its contents recursively
{
  if (!is_dir($realFileArray)) {
    return false;
  }
  
  $items = scandir($realFileArray);
  foreach ($items as $item) {
  
    if ($item === '.' || $item === '..') {
      continue;
    }
    $itemPath = $realFileArray . DIRECTORY_SEPARATOR . $item;
    if (is_dir($itemPath)) {
      deleteDirectory($itemPath); // recursive deletion for subdirectories
    } else {
      unlink($itemPath); //delete file

    }
  }
  rmdir($realFileArray); // delete empty directory
  return true;

  }


