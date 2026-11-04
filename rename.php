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
  $_SESSION['error'] = "Error: Some error occurred while processing the request.";
  header('Location: main.php'); 
  exit;
  
}


$checkedNewname = $result[0];
$checkedOldname = $result[1];


if(!isset($checkedNewname) && !isset($checkedOldname)) {
  $_SESSION['error'] = $result;
  header('Location: main.php'); 
  exit;

}

if($_POST['type'] === 'directory') 
  {

    //$newname = $folderpath.'/'.$newnamerequest;
    
    if (!rename($checkedOldname, $checkedNewname)) {
      $error = error_get_last();
      $_SESSION['error'] ="Error: " . $error['message'];
      header('Location: main.php'); 
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
      $_SESSION['error'] ="Error: " . $error['message'];
      header('Location: main.php'); 
      exit;
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
