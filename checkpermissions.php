<?php
//function to check if the user has permission to access a file or directory 
session_start();

function checkP_DownloadOpenDelete($absoluteCpath, $data) 
{      
    error_log("----------------checkAccess--------------------". date('d.m.Y H:i:s') . "\n", 3, 'debug.log');   
    if (!isset($_SESSION["user_path"])) {
        error_log("Seession is not set \n", 3, 'debug.log');
        return false;
    } 
        $userPath = $_SESSION['user_path'];
        $basePath = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;      //provided with ChatGPT 
        $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;  

        if(!str_starts_with($currentpath, $basePath)) {   //check if the path is within the user's allowed path
            error_log("pathDestination is not part of the base directory \n", 3, 'debug.log');
            return false;
        }

        if(is_array($data))
        {
          $realFileArray = [];
         
          foreach($data as $i => $item)
           {
            $realFileArray[$i] = $currentpath . DIRECTORY_SEPARATOR . $item;

            if (!file_exists($realFileArray[$i])) {   //check if the file or directory exists
            error_log("$realFileArray[$i] \n", 3, 'debug.log');                  
             error_log("file or dir is not existsing \n", 3, 'debug.log');
            return false;
            }

           } 
           return $realFileArray;  
      
        } else 
        {
        $realFile = $currentpath . DIRECTORY_SEPARATOR . $data;
        // if (!file_exists($realFile) ) { 
        //                      //check if the file or directory exists
        //  error_log("$realFile \n", 3, 'debug.log');                  
        //  error_log("file or dir is not existing \n", 3, 'debug.log');
        // return false;
        // }
        
        return $realFile;  //return the real file path if all checks pass 
        }

    
}  

function checkP_Move($absoluteCpath,  $dataArray, $absoluteDpath)
{
   error_log("----------------checkAccess-------------------- ". date('d.m.Y H:i:s') . "\n", 3, 'debug.log');
    if (!isset($_SESSION["user_path"])) {
        error_log("Seession is not set \n", 3, 'debug.log');
        return false;
    }
    $userPath = $_SESSION['user_path'];
    $basePrefix = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $checkedCurrentPaths = [];

    //check pathDestination 
    $pathDestination = rtrim($absoluteDpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;    
    if (!str_starts_with($pathDestination, $basePrefix)) {
        error_log("pathDestination is not part of the base directory \n", 3, 'debug.log');
        return false;
    }
        
    if (!file_exists($pathDestination)) {
         error_log("pathDestination is not existing \n", 3, 'debug.log');
        return false;
    }

    if (!is_dir($pathDestination)) {
         error_log("pathDestination is not a directory \n", 3, 'debug.log');
        return false;    
    }
 
    //check currentpath
    $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    if (!str_starts_with($currentpath, $basePrefix)) {
        error_log("currentpath is not part of the base directory \n", 3, 'debug.log');
        return false;
    }
    //provide the absolute paths for each data item
    foreach ($dataArray as $i => $item) 
    {   
        if (file_exists($pathDestination . $item)) {   
             error_log("filename already exists \n", 3, 'debug.log');
            return false;
        }
        if (!file_exists($currentpath . $item)) {   
              error_log("file or dir is not existing \n", 3, 'debug.log');
            return false;
        }
        $checkedCurrentPaths[$i] = $currentpath . $item;
       
    }

    $checkedDestinationPath = $pathDestination;
    return [$checkedDestinationPath, $checkedCurrentPaths];
}






function checkP_rename($absoluteCpath, $requestnewname, $oldname)
{
    error_log("----------------checkAccess-------------------- ". date('d.m.Y H:i:s') . " \n", 3, 'debug.log');
     
    if (!isset($_SESSION["user_path"])) {
        error_log("Session is not set \n", 3, 'debug.log');
        return false;
    } 
    $userPath = $_SESSION['user_path'];
    $basePath = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;      //provided with ChatGPT 
    $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;  

    if(!str_starts_with($currentpath, $basePath)) {   
            error_log("pathDestination is not part of the base directory \n", 3, 'debug.log');
            return false;
    }

    $checkabsoluteOldname = $currentpath. DIRECTORY_SEPARATOR . $oldname;
     
    if (!file_exists($checkabsoluteOldname) ) {                  //check if the file or directory exists
         error_log("file or dir is not existing. $checkabsoluteOldname \n", 3, 'debug.log');
        return false;
    }
    $checkedOldname = $absoluteCpath . DIRECTORY_SEPARATOR . $oldname;

    if (file_exists($absoluteCpath . DIRECTORY_SEPARATOR . $requestnewname) ) {                 
         error_log("filename already exists \n", 3, 'debug.log');
        return false;
    }
    $checkedNewname = $absoluteCpath . DIRECTORY_SEPARATOR . $requestnewname;
    return [$checkedNewname, $checkedOldname];

}



function checkP_CreateFolder($absoluteCpath, $reqeustname)
{
    error_log("----------------checkAccess-------------------- ". date('d.m.Y H:i:s') . "\n", 3, 'debug.log');
        
    if (!isset($_SESSION["user_path"])) {
        error_log("Session is not set \n", 3, 'debug.log');
        return false;
    } 
    $userPath = $_SESSION['user_path'];
    $basePath = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;  
    
    if(!str_starts_with($currentpath, $basePath)) {   
        error_log("Access denied \n", 3, 'debug.log');
        return false;
    }

    //provided with Google AI---------
    //$requestname = trim($requestname);
    // if(empty($requestname)) {
    // error_log("Foldername is empty \n", 3, 'debug.log');
    // return false;
    // }
    $forbiddenChars = '/[\/\\\\?*:|"<>#%&{}\\^~\[\]`]/';

    if (preg_match($forbiddenChars, $requestname) || str_contains($requestname, '..')) {
        error_log("Foldername contains forbidden characters\n", 3, 'debug.log');
        return false;
    }
   //end------------- 
    $checkfoldername = $currentpath. $reqeustname;   
    if(file_exists($checkfoldername))  { 
        error_log("foldername is existing \n", 3, 'debug.log');
        return false;

    }
    return $checkfoldername;
}





// Actions: create folder, rename, download, open, move, delete, upload

// function: check_CreateRename
//            create folder: $absoluteCpath, $requestname, $ID

//  function: check_DownloadOpenDelete
//            rename:  $absoluteCpath, $requestnewname, $oldname, $ID

// function: check_DownloadOpenDelete
//           download: $absoluteCpath, $data, $ID
//           open:     $absoluteCpath, $data, $ID
//           delete:   $absoluteCpath, $data, $ID

// function: check_Move
//            move:    $absoluteCpath, $data, $absoluteDpath, $ID


// for debugging: tail -f /var/log/apache2/error.log
//or you can just use here the logs file. 