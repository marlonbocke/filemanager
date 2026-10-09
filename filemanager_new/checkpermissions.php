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

function check_Move($absoluteCpath, $data, $absoluteDpath )
{
    if (!isset($_SESSION["user_path"])) {
        return false;
    }
    $userPath = $_SESSION['user_path'];
    $basePrefix = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    //check pathDestination
        $pathDestination = rtrim($absoluteDpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!str_starts_with($pathDestination, $basePrefix)) {
            return false;
        }

        if (!file_exists($pathDestination)) {
            return false;
        }

    $checkedDestinationPath = $pathDestination; 
    
    $checkedCurrentPath = [];
    
    //check currentpaths
    foreach ($data as $i => $item) 
    {
        $currentpaths = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (!str_starts_with($currentpaths , $basePrefix)) {
            return false;
        }
        if (!file_exists($currentpaths . $item)) {
            return false;
        }

        $checkedCurrentPaths[$i] = $currentpaths . $item;
        
    
    }

    return [$checkedDestinationPath, $checkedCurrentPaths];
}




// Actions: create folder, rename, download, open, move, delete

// function: check_CreateRename
// create folder: $absoluteCpath, $requestname, $ID
// rename:        $absoluteCpath, $requestnewname, $oldname, $ID

// function: check_DownloadOpenDelete
// download: $absoluteCpath, $data, $ID
// open:     $absoluteCpath, $data, $ID
// delete:   $absoluteCpath, $data, $ID

// function: check_Move
// move:     $absoluteCpath, $data, $absoluteDpath, $ID