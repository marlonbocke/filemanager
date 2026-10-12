<?php
//function to check if the user has permission to access a file or directory 
session_start();

function checkP_DownloadOpenDelete($absoluteCpath, $data) 
{      
if (!isset($_SESSION["user_path"])) {
        return false;
    } 
        $userPath = $_SESSION['user_path'];
        $basePrefix = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;      //provided with ChatGPT 
        $pathPrefix = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;  

        if(!str_starts_with($pathPrefix, $basePrefix)) {            //check if the path is within the user's allowed path
            return false;
        }

        if(is_array($data))
        {
       
          $realFileArray = [];
         
          foreach($data as $i => $item)
           {
            $realFileArray[$i] = $pathPrefix . DIRECTORY_SEPARATOR . $item;

            if (!file_exists($realFileArray[$i])) {                  //check if the file or directory exists
            return false;
            }

           } 
           return $realFileArray;  
      
        } else 
        {

        $realFile = $absoluteCpath . '/' . $data;
        if (!file_exists($realFile) ) {                  //check if the file or directory exists
        return false;
        }
        
        return $realFile;  //return the real file path if all checks pass 
        }

    
}  

function checkP_Move($absoluteCpath,  $dataArray, $absoluteDpath)
{
   error_log("----------------check-------------------- \n", 3, '/var/www/html/filemanager_new/debug.log');
    if (!isset($_SESSION["user_path"])) {
        error_log("Seession is not set \n", 3, '/var/www/html/filemanager_new/debug.log');
        return false;
    }
    $userPath = $_SESSION['user_path'];
    $basePrefix = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $checkedCurrentPaths = [];

    //check pathDestination 
    $pathDestination = rtrim($absoluteDpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
     
    if (!str_starts_with($pathDestination, $basePrefix)) {
        error_log("pathDestination is not part of the base directory \n", 3, '/var/www/html/filemanager_new/debug.log');
        return false;
    }
        
    if (!file_exists($pathDestination)) {
         error_log("pathDestination is not existing \n", 3, '/var/www/html/filemanager_new/debug.log');
        return false;
    }

    if (!is_dir($pathDestination)) {
         error_log("pathDestination is not a directory \n", 3, '/var/www/html/filemanager_new/debug.log');
        return false;    
    }

    $checkedDestinationPath = $pathDestination; 

    //check currentpath
    $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    if (!str_starts_with($currentpath, $basePrefix)) {
        error_log("currentpath is not part of the base directory \n", 3, '/var/www/html/filemanager_new/debug.log');
        return false;
    }
    //provide the absolute paths for each data item
    foreach ($dataArray as $i => $item) 
    {   
       $checkedCurrentPaths[$i] = $currentpath . $item;
        // if (!file_exists($checkedCurrentPaths[$i]) && !is_dir($checkedCurrentPaths[$i])) {       //this is not working 
        //     return false;
        // }

    }
    error_log("----end----------- \n", 3, '/var/www/html/filemanager_new/debug.log');
    return [$checkedDestinationPath, $checkedCurrentPaths];
}




// Actions: create folder, rename, download, open, move, delete, upload

// function: check_CreateRename
// create folder: $absoluteCpath, $requestname, $ID


// rename:        $absoluteCpath, $requestnewname, $oldname, $ID

// function: check_DownloadOpenDelete
// download: $absoluteCpath, $data, $ID
// open:     $absoluteCpath, $data, $ID
// delete:   $absoluteCpath, $data, $ID

// function: check_Move
// move:     $absoluteCpath, $data, $absoluteDpath, $ID


// for debugging: tail -f /var/log/apache2/error.log | grep --line-buffered -oE '^[A-Za-z]+ [A-Za-z]+ [0-9 :]+ [0-9]+|PathDestination:.*|BasePrefix:.*|Starts with.*|File exists\?.*|Is dir\?.*|---+'
//or you can just use here the logs file. 