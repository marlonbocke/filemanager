<?php
//function to rename a file or directory
session_start();
$logFile = 'debug.log';
 
$absoluteCpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

//$oldname = $folderpath.'/'.$_POST['oldname'];
$oldname = basename($_POST['oldname']);
$newnamerequest = basename($_POST['renamefile'] ?? $_POST['renamedir'] ?? '');

require_once 'checkpermissions.php';
$result = checkP_rename($absoluteCpath, $newnamerequest, $oldname);

if(!isset($result) || $result==false) {
    http_response_code(403);
    $logLines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lastError = !empty($logLines) ? end($logLines) : 'umknown error';
    echo "<script>alert('$lastError');window.location.href = 'main.php';</script>";
}


$checkedNewname = $result[0];
$checkedOldname = $result[1];


if(!isset($checkedNewname) && !isset($checkedOldname)) {
  echo "<script>alert('Error: renamefile or renamedir not set.');</script>";
  exit;
}

if($_POST['type'] === 'directory') 
  {

    //$newname = $folderpath.'/'.$newnamerequest;
    
    if (!rename($checkedOldname, $checkedNewname)) {
      $error = error_get_last();
      echo "<script>alert('Error renaming directory from: ". $error['message'] . "'); window.location.href = 'main.php';</script>"; 
      exit;
    } else {
      header("Location: main.php"); 
        exit;
    }


  } elseif($_POST['type'] === 'file') 
  {  
  
    if(!rename($checkedOldname, $checkedNewname)) 
    {
      $error = error_get_last();
      echo "<script>alert('Error renaming file from: ". $error['message'] . "'); window.location.href = 'main.php';</script>";  
      
    } else {
        gobackfromRename($newname);  
    }


  }

function gobackfromRename($newname) {
    echo "<script>console.log('Going back from rename...');</script>";
    if($_POST['condition'] === 'from_image_display') {
        $_SESSION['filemanager_image'] = $newname;
        header("Location: main.php");
    } else if($_POST['condition'] === 'from_pdf_display') 
    {
       $_SESSION['filemanager_pdf'] = $newname;
        header("Location: main.php");
    } else {
        header("Location: main.php");
    }
    exit;
}
