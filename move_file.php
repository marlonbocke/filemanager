<?php
session_start();
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

$absoluteDpath = $data['destination'];
$array_source = $data['source'];

foreach($array_source as $i => $item){
  $data[$i] = basename($item);
 } 

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

if(!isset($absoluteCpath)){
    echo json_encode(['error' => 'absoluteCpath is not set.  ']);
    exit;
 }

require_once 'checkpermissions.php';
 $result = checkP_Move($absoluteCpath, $data, $absoluteDpath);

if(!isset($result) || $result == false){
    echo json_encode(['error' => 'Forbidden: You do not have permission to access this file']);
    exit;
 }

$checkedCurrentPaths = $result[1];       
$checkedDestinationPath = $result[0];    


 if(!isset($checkedCurrentPaths) ) {
     echo json_encode(['error' => 'checkedCurrentPaths is not set']);
     exit;
}

if(!isset($checkedDestinationPath))
 {
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
    echo json_encode(['message' => $e->getMessage()]);    
}




