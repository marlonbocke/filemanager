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

        $basePathReal = realpath($basePath);
        $currentpathReal = realpath($currentpath);  
        

        if(!str_starts_with($currentpathReal, $basePathReal)) {   //check if the path is within the user's allowed path
            error_log("pathDestination is not part of the base directory \n", 3, 'debug.log');
            return false;
        }

        if(is_array($data))
        {
          $realFileArray = [];
         
          foreach($data as $i => $item)
           {
            $realFileArray[$i] = $currentpathReal . DIRECTORY_SEPARATOR . $item;

            if(!file_exists($realFileArray[$i])) {   //check if the file or directory exists
            error_log("$realFileArray[$i] \n", 3, 'debug.log');                  
             error_log("file or dir is not existsing \n", 3, 'debug.log');
            return false;
            }

           } 
           return $realFileArray;  
      
        } else 
        {
        $realFile = $currentpathReal . DIRECTORY_SEPARATOR . $data;
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
    $basePath = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;      //provided with ChatGPT 
    $basePathReal = realpath($basePath);

    $checkedCurrentPaths = [];

    //check pathDestination 
    $pathDestination = rtrim($absoluteDpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;    
    $pathDestinationReal = realpath($pathDestination);
       
    if (!str_starts_with($pathDestinationReal, $basePathReal)) {
        error_log("pathDestination is not part of the base directory \n", 3, 'debug.log');
        return false;
    }
        
    if (!file_exists($pathDestinationReal)) {
         error_log("pathDestination is not existing \n", 3, 'debug.log');
        return false;
    }

    if (!is_dir($pathDestinationReal)) {
         error_log("pathDestination is not a directory \n", 3, 'debug.log');
        return false;    
    }
 
    //check currentpath
    $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $currentpathReal = realpath($currentpath);  
    if (!str_starts_with($currentpathReal, $basePrefix)) {
        error_log("currentpath is not part of the base directory \n", 3, 'debug.log');
        return false;
    }
    //provide the absolute paths for each data item
    foreach ($dataArray as $i => $item) 
    {   
        if (file_exists($pathDestinationReal . $item)) {   
             error_log("filename already exists \n", 3, 'debug.log');
            return false;
        }

        if (!file_exists($currentpathReal.DIRECTORY_SEPARATOR. $item)) {   
              error_log("file or dir is not existing \n", 3, 'debug.log');
            return false;
        }
        $checkedCurrentPaths[$i] = $currentpathReal .DIRECTORY_SEPARATOR. $item;
       
    }

    $checkedDestinationPath = $pathDestinationReal;
    return [$checkedDestinationPath, $checkedCurrentPaths];
}






function checkP_rename($absoluteCpath, $requestnewname, $oldname)
{
    error_log("----------------checkAccess-------------------- ". date('d.m.Y H:i:s') . " \n", 3, 'debug.log');
    error_log("$oldname \n", 3, 'debug.log');
 
    if (!isset($_SESSION["user_path"])) {
        error_log("Session is not set \n", 3, 'debug.log');
        return false;
    } 
    $userPath = $_SESSION['user_path'];
    $basePath = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;      //provided with ChatGPT 
    $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;  
    $basePathReal = realpath($basePath);
    $currentpathReal = realpath($currentpath); 
    $info = pathinfo($oldname);
    $extension = $info['extension'] ?? '';
    error_log("name:        $requestnewname \n", 3, 'debug.log');




    if(!str_starts_with($currentpathReal, $basePathReal)) {   
        error_log("pathDestination is not part of the base directory \n", 3, 'debug.log');
        return false;
    }

    $checkabsoluteOldname = $currentpathReal. DIRECTORY_SEPARATOR . $oldname;
     
    if (!file_exists($checkabsoluteOldname) ) {                  //check if the file or directory exists
         error_log("file or dir is not existing. $checkabsoluteOldname \n", 3, 'debug.log');
        return false;
    }
    $checkedOldname = $currentpathReal . DIRECTORY_SEPARATOR . $oldname;

    if(!empty($extension)) {
       $checkNewname =  $currentpathReal. DIRECTORY_SEPARATOR . $requestnewname .'.'.$extension;
    } else    
       $checkNewname = $currentpathReal . DIRECTORY_SEPARATOR . $requestnewname;
    
    error_log(" 2 $checkNewname \n", 3, 'debug.log');

    if (file_exists($checkNewname) ) {                 
         error_log("filename already exists \n", 3, 'debug.log');
        return false;
    }
    $checkedNewname = $checkNewname;  
    error_log("checkedNename:        $checkedNewname \n", 3, 'debug.log');

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
    $basePathReal = realpath($basePath);
    $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;  
    $currentpathReal = realpath($currentpath); 
 
    if(!str_starts_with($currentpathReal, $basePathReal)) {   
        error_log("Access denied \n", 3, 'debug.log');
        return false;
    }

    //provided with Google AI---------
    $forbiddenChars = '/[\/\\\\?*:|"<>#%&{}\\^~\[\]`]/';

    if (preg_match($forbiddenChars, $requestname) || str_contains($requestname, '..')) {
        error_log("Foldername contains forbidden characters\n", 3, 'debug.log');
        return false;
    }
   //end------------- 
    $checkfoldername = $currentpathReal.DIRECTORY_SEPARATOR. $reqeustname;   
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