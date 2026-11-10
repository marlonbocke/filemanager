<?php

function connect_to_db() {
$filedata = __DIR__ . "/config/config.json";
if(!file_exists($filedata)) {
    error_log("No config data file was found.  \n", 3, 'debug.log'); 
    //return "No config data file was found. Please create a config.json file in the config folder.";
    return $filedata;
} else 
 

$jsondata = file_get_contents($filedata);
$config= json_decode($jsondata, true); 

    if (!is_array($config)) {
        error_log("No config data found  \n", 3, 'debug.log');     
        echo "<script>alert('No config data found. Please try again'); window.location.href = '/services/filemanager_new/config/config.html';</script>";
        exit;
    }
    $config_data=$config[0];
    //var_dump($config); 

        
    $ip = $config_data["ip_address"];
    $port = $config_data['port'];
    $db_username = $config_data['username'];
    $db_password = $config_data['password'];
    $databasename = $config_data['databasename'];
    
    

    if(empty($ip) || empty($port)|| empty($db_username)|| empty($db_password)|| empty($databasename) )
    {
         error_log("Could not read all json data.  \n", 3, 'debug.log'); 
         echo "<script>alert('Something went wrong.Please try again'); window.location.href = '/services/filemanager_new/config/config.html';</script>";
         exit;
    }   
    


$connection = mysqli_connect($ip, $db_username, $db_password, $databasename, $port);

if (!$connection) {
     error_log("Connection failed.  \n", 3, 'debug.log'); 
     return "<script>alert('Connection failed.'); window.location.href = '/services/filemanager_new/index.html';</script>";
     
    }
    return $connection;   
}