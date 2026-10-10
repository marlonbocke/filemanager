<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Forbidden');
}
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['destination']) || !is_array($data['source'])) {
    echo json_encode(['message' => 'Something went wrong. Please try again later.']);
    exit;
}

$absoluteDpath = $data['destination'];
$array_source = $data['source'];

foreach($array_source as $i => $item)
 {
  $data[$i] = basename($item);
 } 

 if (!is_array($data)) {
    echo json_encode(['message' => 'data is not a vaid array']);
    exit;
}

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

if(!isset($absoluteCpath))
{
    echo json_encode(['message' => 'absoluteCpath is not set.  ']);
    exit;
 }



require_once 'checkpermissions.php';
 $result = checkP_Move($absoluteCpath, $data, $absoluteDpath);

if(!isset($result))
{
    echo json_encode(['message' => 'WFT what is that?  ']);
    exit;
 }


 if($result == false)
 {
    echo json_encode(['message' => 'Access denied: $result false ']);
    exit;

}
$checkedCurrentPaths = $result[1];       // tatsächlich Zielordner
$checkedDestinationPath = $result[0];    // tatsächlich Quelldateien


 if(!isset($checkedCurrentPaths) ) {
     echo json_encode(['message' => 'Something went wrong: checkedCurrentPaths']);
     exit;

}

if(!isset($checkedDestinationPath))
 {
    echo json_encode(['message' => 'Something went wrong']);
    exit;
 }   

if (!is_dir($checkedDestinationPath)) {

    echo json_encode(['message' => 'Directory is not a valid destination path']);
    exit;
}
   
try {              
    foreach ($checkedCurrentPaths as $i => $source) {

       
        if (!file_exists($source)) {  
            continue;           
        }
     $full_destination = rtrim($checkedDestinationPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($source);  

        // if (file_exists($full_destination)) { 
            
        // throw new RuntimeException('Failed to move: ' . $source.'   '.'Destination:     '.'   ' . $full_destination);  
        // }


        if (!rename($source, $full_destination)) {       
            throw new RuntimeException('Failed to move: ' . $source.'   '.'Destination:     '.'   ' . $full_destination);  
        }
    }

    echo json_encode(['message' => 'Files moved successfully.']);   
} catch (Throwable $e) {                
    echo json_encode(['message' => $e->getMessage()]);    
}




