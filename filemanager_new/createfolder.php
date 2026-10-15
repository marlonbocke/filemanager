<?php
session_start();
$logFile = 'debug.log';
 
if (!isset($_POST['createfolder'])) {
  echo "<script>alert('Error: createfolder not set.');</script>";
  exit;
}

$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];
$requestname = basename($_POST['createfolder']);

require_once 'checkpermissions.php';
$cheched_newfolderpath = checkP_Createfolder($absoluteCpath, $requestname);

if(!isset($cheched_newfolderpath) || $cheched_newfolderpath==false) {
    http_response_code(403);
    $logLines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lastError = !empty($logLines) ? end($logLines) : 'umknown error';
    echo "<script>alert('$lastError'); window.location.href = 'main.php' ;</script>";  
    exit;
}


if(!mkdir($cheched_newfolderpath)) {
echo "<script>alert('Error: Failed to create the folder.'); window.location.href = 'main.php' ;</script>";  
exit;
}

header("Location: main.php");
exit;  

  
