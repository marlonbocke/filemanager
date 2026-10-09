<?php
session_start();
$newpath = $_SESSION["newpath"];
unset($_SESSION['newpath']);

if ($_POST['newpath'] && $newpath == '') {

    $rnp = $_SESSION["allowedBasePathReal"].$_POST["newpath"] .'/';

    $_SESSION['newpath'] = $rnp;
  
} else if($_POST['homepath']) {
    
    $accumulatedPath = $_POST["homepath"] .'/';    
     $_SESSION["newpath"] = rtrim($accumulatedPath, '/');
    

} else if($_POST["accumulatedPath"]) {
    
    $accumulatedPath = $_POST["accumulatedPath"] .'/';
      
     $_SESSION["newpath"] = rtrim($accumulatedPath, '/');
    
} else {

    
    $rnp2= $_POST["newpath"];
    
    $_SESSION['newpath'] = rtrim($rnp2, '/');
   

} 
// echo "<script>window.location.href = 'main.php';</script>";
header("Location: main.php");
exit();

