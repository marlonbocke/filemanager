<?php 
 error_log("----------------deleteMorefile-------------------". date('d.m.Y H:i:s') . "\n", 3, 'debug.log');   
header('Content-Type: application/json');
session_start();    //is important to read the sessions data 
$logFile = 'debug.log';
$errorReason = [];
$cheched = [];


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errorReason[] = "Something went wrong. Please try again later.";
}

$json = file_get_contents('php://input');
$jsondata = json_decode($json, true);


if (!isset($jsondata['files'])) {
    $errorReason[] = "Something went wrong. Please try again later.";  
}

$deletefiles = $jsondata['files'];
$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

foreach($deletefiles as $i => $item){
  $data[$i] = basename($item);
 } 


require_once 'checkpermissions.php';
 $result = checkP_DownloadOpenDelete($absoluteCpath, $data);

 

 if(!isset($result) || $result ==false) {
    error_log("result is false \n", 3, 'debug.log');
    $errorReason[]= "check is failed";
}


if(!(is_array($result))) {
    error_log("result:  $result \n", 3, 'debug.log');
    $errorReason[] = $result;
    $response = [
    'success' => false,
    'errors'  => $errorReason
];
echo json_encode($response);
exit;
}  

foreach ($result as $index => $item) 
{   
    if($item['success'] == false) {
        
        error_log("error:  ".$item['files'] ."   ".$item['info'] ." \n", 3, 'debug.log');
        $errorReason[] = $item['files'].": " .$item['info'];

    } else   
         $checked[] =  $item['files'];  
          error_log("checked: ".$item['files']." \n", 3, 'debug.log');
    
}


foreach ($checked as $i => $file) 
{
    error_log("file: ".$file ." \n", 3, 'debug.log');
  if (is_file($file)) {
      
    if(!unlink($file)){
        error_log("failed to delete". $file . "\n", 3, 'debug.log');   
        $errorReason[] = "failed to delete". $file; 
      }

  } else if (is_dir($file)) {
      deleteDirectory($file);
  }
}

$response = [
    'success' => empty($errorReason), 
    'canceled' => false,
    'errors'  => $errorReason
];

echo json_encode($response);
exit;


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


