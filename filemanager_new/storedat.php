<?php

$filetostore = file_get_contents("php://input");

if(isset($_GET['debug']) && $_GET['debug'] == 'true') {
    file_put_contents('storedat_debug.log', date('Y-m-d H:i:s') . " - Received data storage request\n", FILE_APPEND);
    file_put_contents('storedat_debug.log', date('Y-m-d H:i:s') . " - Raw input data length: " . strlen($filetostore) . "\n", FILE_APPEND);
}



if(isset($_POST['filepath']) ) {
    session_start();
    unset($_SESSION['storedfilesdata']);


    $filePath = $_POST['filepath'];
    $fullPath = rtrim($filePath, '/') . '/' . $fileName;
    $storeddate =[];
    
    $file=scandir($filePath);
    foreach($file as $f){
        if(is_dir($f)){
            continue;
        }
        $filedata = file_get_contents($filePath.'/'.$f);
        $base64data = base64_encode($filedata);
        $storeddate[$f] = $base64data;

            
    }    
     $_SESSION['storedfilesdata'] = $storeddate;   
    


} 
