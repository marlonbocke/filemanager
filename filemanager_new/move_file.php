<?php
 //generated with Google AI
session_start();
$logFile = 'debug.log';   
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Something went wrong. Please try again later.']);
    exit;
}

if (!isset($data['destination']) || !is_array($data['source'])) {
    echo json_encode(['error' => 'Something went wrong. Please try again later.']);
    exit;
}

$array_source = $data['source'];

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

if(!realpath($data['destination']))
   {
    $relativDpath = $data['destination'];    
    $absoluteDpath = $absoluteCpath. DIRECTORY_SEPARATOR . $relativDpath; 
   } else  
    $absoluteDpath = $data['destination'];


foreach($array_source as $i => $item){
  $dataArray[$i] = basename($item);
 } 


require_once 'checkpermissions.php';
 $result = checkP_Move($absoluteCpath, $dataArray, $absoluteDpath);

if(!isset($result) || $result == false){
    $logLines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lastError = !empty($logLines) ? end($logLines) : 'umknown error';
    echo json_encode(['error' => $lastError]);
    exit;
 }

$checkedCurrentPaths = $result[1];       
$checkedDestinationPath = $result[0];    


 if(!isset($checkedCurrentPaths) ) {
     echo json_encode(['error' => 'checkedCurrentPaths is not set']);
     exit;
}

if(!isset($checkedDestinationPath)){
    echo json_encode(['error' => '$checkedDestinationPath is not set']);
    exit;
 }   

if (!is_dir($checkedDestinationPath)) {
    echo json_encode(['error' => 'Directory is not a valid destination path']);
    exit;
}
   
try {              
    foreach ($checkedCurrentPaths as $i => $source) {
   
        if (!file_exists($source)) {  
            continue;           
        }
         $full_destination = rtrim($checkedDestinationPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($source);  

        if (!rename($source, $full_destination)) {       
            throw new RuntimeException('Failed to move: ' . $source.'   '.'Destination:     '.'   ' . $full_destination);  
        }
    }

    echo json_encode(['message' => 'Files moved successfully.']);   
} catch (Throwable $e) {                
    echo json_encode(['error' => $e->getMessage()]);   
    exit; 
}




