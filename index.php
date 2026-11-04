<?php 

$file ='config/config.json';
$content = file_get_contents($file);   

if(!file_exists($file))
{
  header("Location: /config/config.html");
   echo "file was not found";
   exit;
    
} 

if(!json_validate($content))
 {
    header("Location: /config/config.html");
   exit;
 }   


header("Location: login.html");
exit;
?>