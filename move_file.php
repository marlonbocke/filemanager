<?php
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

$destination = $data['destination'];
$array_source = $data['source'];





$dataCount = count($array_source);
$baseDirNormalized = rtrim($_SESSION["allowedBasePathReal"], '/\\') . DIRECTORY_SEPARATOR;      




if (!is_dir($destination)) {



    echo json_encode(['message' => 'Directory is not a valid destination path']);
    exit;
}


try {              
    foreach ($array_source as $i => $source) {
    $sourcesNormalized = rtrim($source, '/\\') . DIRECTORY_SEPARATOR;
    if (!str_starts_with($sourcesNormalized, $baseDirNormalized) == true && !(is_dir($sourcesNormalized))) 
      {
      exit;      
    } else 
        $full_destination =' ';
        $full_destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($source);  

        if (!file_exists($source)) {  
            continue;           
        }

        if (!rename($source, $full_destination)) {       
            throw new RuntimeException('Failed to move: ' . $source.'Destination:     '.$full_destination );  
        }
    }

    echo json_encode(['message' => 'Files moved successfully.']);   
} catch (Throwable $e) {                
    echo json_encode(['message' => $e->getMessage()]);    
}






