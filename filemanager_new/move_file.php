<?php
header('Content-Type: application/json');
session_start();
$logFile = 'debug.log'; 
$errorReason = [];
$cheched = [];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $errorReason[] = "Something went wrong. Please try again later.";
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);



if (!isset($data['destination']) || !is_array($data['source'])) {
    $errorReason[] = "Something went wrong. Please try again later.";  
}

$array_source = $data['source'];

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

if(!realpath($data['destination'])) {
    $relativDpath = $data['destination'];    
    $absoluteDpath = $absoluteCpath. DIRECTORY_SEPARATOR . $relativDpath; 
} else  
    $absoluteDpath = $data['destination'];


foreach($array_source as $i => $item) {
  $dataArray[$i] = basename($item);
 } 


require_once 'checkpermissions.php';
 $result = checkP_Move($absoluteCpath, $dataArray, $absoluteDpath);

if(!isset($result) || $result == false){
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

$checkedCurrentPaths = $result[1];       
$checkedDestinationPath = $result[0];    

if(!isset($checkedDestinationPath)){
    error_log("checkedDestinationPath is not set \n", 3, 'debug.log');
    $errorReason[] = "checkedDestinationPath is not set"; 
    exit;
 }   

if (!is_dir($checkedDestinationPath)) {
    error_log("Directory is not a valid destination path \n", 3, 'debug.log');
    $errorReason[] = "Directory is not a valid destination path"; 
    exit;
}

if(!isset($checkedCurrentPaths) ) {
    error_log("checkedCurrentPaths is not set \n", 3, 'debug.log');
     $errorReason[] = "checkedCurrentPaths is not set";  
     exit;
}


foreach ($checkedCurrentPaths as $index => $item) 
{   
    if($item['success'] == false) {
        
        error_log("error:  ".$item['files'] ."   ".$item['info'] ." \n", 3, 'debug.log');
        $errorReason[] = $item['files'].":" .$item['info'];

    } else   
         $checked[] =  $item['files'];  
    
}


foreach($checked as $i => $source) 
{
   
    $full_destination = rtrim($checkedDestinationPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($source);  
    
    if(!rename($source, $full_destination)) {       
    error_log("failed to move\n", 3, 'debug.log');  
    $errorReason[] = "failed to move".$full_destination;     
    }

}

 
$response = [
    'success' => empty($errorReason), 
    'canceled' => false,
    'errors'  => $errorReason
];

echo json_encode($response);
exit;





