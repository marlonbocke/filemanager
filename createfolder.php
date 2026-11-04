<?php
session_start();
$logFile = 'debug.log';
 
if (!isset($_POST['createfolder'])) {
  $_SESSION['error'] = "Error: Some error occurred while processing the request.";
    header('Location: main.php'); 
    exit;

}

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];
$requestname = basename($_POST['createfolder']);

require_once 'checkpermissions.php';
$cheched_newfolderpath = checkP_Createfolder($absoluteCpath, $requestname);


if(!isset($cheched_newfolderpath)) {
    error_log("Some error occurred while processing the request.\n", 3, 'debug.log');
    $_SESSION['error'] = "Error: Some error occurred while processing the request.";
    header('Location: main.php'); 
    exit;
}

if(preg_match('/^Error:/', $cheched_newfolderpath)) {
    error_log("$cheched_newfolderpath\n", 3, 'debug.log');
    $_SESSION['error'] = $cheched_newfolderpath;
    header('Location: main.php'); 
    exit;
}


if(!mkdir($cheched_newfolderpath)) {
 
    $_SESSION['error'] = "Error: Failed to create the folder.";
    header('Location: main.php'); 
    exit;



}

header("Location: main.php");
exit;  

  
