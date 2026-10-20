<?php
session_start();
$logFile = 'debug.log';
header('Content-Type: application/json');
$errorReason = [];
$cheched = [];


if (empty($_FILES['uploaded_file'])) {
    $response["errors"]= "No files uploaded";
    $errorReason[]= "check is failed";

}

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

require_once 'checkpermissions.php';
$result = checkP_upload($absoluteCpath, $_FILES['uploaded_file']);

if(!isset($result) || $result==false ) { 
    error_log("Soemthing went wrong \n", 3, 'debug.log');
    $errorReason[]= "check is failed";
}

//error_log("Data: " . print_r($result, true) . "\n", 3, 'debug.log');

foreach ($result as $index => $item) 
{   
    if($item['success'] == false) {
        error_log("error:  ".$item['files'] ."   ".$item['success'] ." \n", 3, 'debug.log');
        $errorReason[] = $item['files'].":" .$item['info'];

    } else {  
        error_log("cheched:  ".$item['files'] ."   ".$item['success'] ." \n", 3, 'debug.log');
        $cheched[] =  $item['files'];  
    } 
}

//error_log("Data: " . print_r($cheched, true) . "\n", 3, 'debug.log');
foreach ($_FILES['uploaded_file']['tmp_name'] as $index => $tmpName) 
{
    $originalName = basename($_FILES['uploaded_file']['name'][$index]); 
       
    if(!(in_array($originalName, $cheched))){ 
        error_log("$originalName     \n", 3, 'debug.log');  
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