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

    $checkCurrentPaths = [];
    $checked = [];

    //check pathDestination 
    $pathDestination = rtrim($absoluteDpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;    
    $pathDestinationReal = realpath($pathDestination);
       
    if (!str_starts_with($pathDestinationReal, $basePathReal)) {
        error_log("pathDestination is not part of the base directory \n", 3, 'debug.log');
        $error = "pathDestination is not part of the base directory";
        return $error;
    }
        
    if (!file_exists($pathDestinationReal)) {
        error_log("pathDestination is not existing \n", 3, 'debug.log');
        $error = "pathDestination is not existing";
        return $error;
    }

    if (!is_dir($pathDestinationReal)) {
        error_log("pathDestination is not a directory \n", 3, 'debug.log');
        $error = "pathDestination is not a directory";
        return $error;    
    }
 
    //check currentpath
    $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    $currentpathReal = realpath($currentpath);  
    
    if (!str_starts_with($currentpathReal, $basePathReal)) {
        error_log("currentpath is not part of the base directory \n", 3, 'debug.log');
         $error = "currentpath is not part of the base directory";
        return $error;      
      
    }
    
    //provide the absolute paths for each data item
    foreach ($dataArray as $i => $item) 
    {
        if (file_exists($pathDestinationReal .DIRECTORY_SEPARATOR. $item)) {   
             error_log("filename already exists \n", 3, 'debug.log');
             $checked[$i] = ['success' => false, 'info' => 'filename already exists', 'files' => $item];
             continue;
        }

        if (!file_exists($currentpathReal .DIRECTORY_SEPARATOR. $item)) {   
              error_log("file or dir is not existing \n", 3, 'debug.log');
               $checked[$i] = ['success' => false, 'info' => 'file or dir is not existing', 'files' => $item];  
              continue;
        }
        $checkCurrentPaths[$i] = $currentpathReal .DIRECTORY_SEPARATOR. $item;   
        $checked[$i] = ['success' => true, 'info' => 'file is checked', 'files' => $checkCurrentPaths[$i]];  
       
    }

    $checkedDestinationPath = $pathDestinationReal;
    return [$checkedDestinationPath, $checked];
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

    if(preg_match($forbiddenChars, $requestname) || str_contains($requestname, '..')) {
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


function checkP_upload($absoluteCpath, $uploadfiles)
{
    error_log("----------------checkAccess Upload-------------------- ". date('d.m.Y H:i:s') . "\n", 3, 'debug.log');
    if (!isset($_SESSION["user_path"])) {
        error_log("Session is not set \n", 3, 'debug.log');
        return false;
    }
    $userPath = $_SESSION['user_path'];
    $basePath = rtrim($userPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;   
    $currentpath = rtrim($absoluteCpath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;  
    $basePathReal = realpath($basePath);
    $currentpathReal = realpath($currentpath); 
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'txt', 'zip'];
    $maxFileSize = 20 * 1024 * 1024;
    $checkedfiles = [];
    

    if(!str_starts_with($currentpathReal, $basePathReal)) {   
        error_log("Access denied \n", 3, 'debug.log');
        $error = "Access denied";     
        return $error;
    }
 
    if(!isset($uploadfiles['tmp_name'])) {
        error_log("Upload error  \n", 3, 'debug.log');
        $error = "Upload error";
        return $error;
    }


    foreach($uploadfiles['tmp_name'] as $index => $tmpName) 
    {

        //Ensure that uploaded file has no error 
        if(!isset($uploadfiles['error'][$index])) {
            error_log("Upload error \n", 3, 'debug.log');
            $checkedfiles[$index] = ['success' => false, 'info' => 'Upload error', 'files' => $uploadfiles['name'][$index]];
            continue;
        }
        
        // 2. Ensure file was uploaded via HTTP POST (Security check)
        if (!is_uploaded_file($tmpName)) {
            error_log("Potential file upload attack \n", 3, 'debug.log');
            $checkedfiles[$index] = ['success' => false, 'info' => 'Potential file upload attack', 'files' => $uploadfiles['name'][$index]];
            continue;
        }
        // 3. Validate file size (e.g., max 20 MB)
        if ($uploadfiles['size'][$index] > $maxFileSize) {
            error_log("File is too large \n", 3, 'debug.log');
           $checkedfiles[$index] = ['success' => false, 'info' => 'File is too large', 'files' => $uploadfiles['name'][$index]];
            continue;
        }
        // 4. Validate file extension using a whitelist
         $originalName = $uploadfiles['name'][$index] ?? '';  
         $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            error_log("Disallowed file type: " . $fileExtension . "\n", 3, 'debug.log');
           $checkedfiles[$index] = ['success' => false, 'info' => 'Disallowed file type', 'files' => $uploadfiles['name'][$index]];
            continue;
        }
          $checkedfiles[$index] = ['success' => true, 'info' => 'file is checked', 'files' => $uploadfiles['name'][$index]];
        
    }
        return $checkedfiles;
        
}



