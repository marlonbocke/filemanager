<?php
session_start();
$logFile = 'debug.log';
header('Content-Type: application/json');
$errorReason = [];
$cheched = [];


if (empty($_FILES['uploaded_file'])) {
    error_log("Soemthing went wrong \n", 3, 'debug.log');
    $errorReason[]= "no files uploaded";
}

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];
error_log("----------------check file-------------------- ". date('d.m.Y H:i:s') . "\n", 3, 'debug.log');
require_once 'checkpermissions.php';
$result = checkP_upload($absoluteCpath, $_FILES['uploaded_file']);

if(!isset($result) || $result==false ) { 
    error_log("Soemthing went wrong \n", 3, 'debug.log');
    $errorReason[]= "check is failed";
}


if(is_string($result)) {
    error_log("$result \n", 3, 'debug.log');
    $errorReason[] = $result;
    $response = [
    'success' => empty($errorReason), 
    'errors'  => $errorReason
];
echo json_encode($response);
exit;
}  

foreach ($result as $index => $item) 
{   
    if($item['success'] == false) {
        error_log("error:  ".$item['files'] ."   ".$item['success'] ." \n", 3, 'debug.log');
        $errorReason[] = $item['files'].":" .$item['info'];

    } else   
        $cheched[] =  $item['files'];  
     
}

foreach ($_FILES['uploaded_file']['tmp_name'] as $index => $tmpName) 
{
    $originalName = basename($_FILES['uploaded_file']['name'][$index]); 
       
    if(!(in_array($originalName, $cheched))){ 
        continue;
     } 
    $targetFile = rtrim($absoluteCpath, '/') . '/' . $originalName;
    if(!(move_uploaded_file($tmpName, $targetFile))) {
        $errorReason[] = $targetFile.":" ."failed to upload"; 
    }
}

$response = [
    'success' => empty($errorReason), 
    'errors'  => $errorReason
];

echo json_encode($response);
exit;