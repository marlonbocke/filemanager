<?php
session_start();
if (!isset($_SESSION["user_path"]))  {
    http_response_code(401);
    echo "<script> alert('Not authorized. Please log in.');</script>";
    exit;
}


$folderpath = $_SESSION["newpath"] ?? $_SESSION["baseFileDir"];

//function to create an new folder
if (!isset($_POST['createfolder'])) {
  echo "<script>alert('Error: createfolder not set.');</script>";
  exit;
}

if(!is_dir($folderpath)) {
    echo "<script>alert('Error: The specified path is not a directory.');</script>";
    exit;
}


$newFolderPath = $folderpath . '/' . $_POST['createfolder'];

if(mkdir($newFolderPath)) {
  header("Location: main.php");
  exit;
} else 
  echo "<script>alert('Error: Failed to create the folder.');</script>";
    exit;
  