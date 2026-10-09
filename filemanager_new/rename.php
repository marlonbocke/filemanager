<?php
//function to rename a file or directory
session_start();



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


$folderpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];
$info = pathinfo($_POST['oldname']);
$extension = $info['extension'] ?? '';
$oldname = $folderpath.'/'.$_POST['oldname']; 
$newnamerequest = $_POST['renamefile'] ?? $_POST['renamedir'] ?? '';



if (!isset($_SESSION["user_path"]))  {
    http_response_code(401);
    echo "<script> alert('Not authorized. Please log in.');</script>";
    exit;
}

if(!isset($_POST['renamefile']) && !isset($_POST['renamedir'])) 
{
  echo "<script>alert('Error: renamefile or renamedir not set.');</script>";
  exit;
  
}


if(!file_exists($oldname)) {
    echo "<script>alert('Error: The file or directory does not exist.');</script>";
    exit;
}


if ($_POST['type'] === 'directory') 
  {

    $newname = $folderpath.'/'.$newnamerequest;
    
    if (rename($oldname, $newname)) 
    {
        header("Location: main.php"); 
        exit;

    } else {
      $error = error_get_last();
      echo "<script>alert('Error renaming directory from: ". $error['message'] . "');</script>";  
      exit;
    }


  } elseif ($_POST['type'] === 'file') 
  {  
    $newname = $folderpath.'/'.$newnamerequest.'.' . $extension;

    if(!rename($oldname, $newname)) 
    {
      $error = error_get_last();
      echo "<script>alert('Error renaming file from: ". $error['message'] . "');</script>";  
      exit;
    } else {
        gobackfromRename($newname);  
    }


  }