<?php

session_start();

/* Nur POST erlauben */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Forbidden');
}


/* Pflichtfelder prüfen */
if (!isset($_POST['file'], $_POST['path'], $_POST['ID'])) {

http_response_code(0);
    exit('Ungültige Anfrage');
}

$filename = basename($_POST['file']);
$userPath = $_POST['path'];
$realFile = $userPath . '/' . $filename;
$button = $_POST['button'];


if ($button == 'openfile' ) {
    handlePreview($realFile, $filename);

} elseif ($button == 'openfileWindow') {

    handleWindow($realFile, $userPath, $filename);   
} else {

    http_response_code(400);
    exit('Ungültige Anfrage: ' . htmlspecialchars($button));
}




function handlePreview($realFile, $filename) 
{
    session_start();
if(strtoupper(substr($filename, -4)) == '.MOV' || strtoupper(substr($filename, -4)) == '.MP4' || strtoupper(substr($filename, -5)) == '.WEBM' || strtoupper(substr($filename, -4)) == '.AVI' || strtoupper(substr($filename, -4)) == '.MKV'){
        
        
        if(isset($_SESSION['filemanager_movie'])){
            unset($_SESSION['filemanager_movie']);
        }

        $_SESSION['filemanager_movie'] = $realFile;
        // Redirect output to /dev/null to prevent the parent script from waiting
        exec("php /var/www/html/filemanager/open_movie.php > /dev/null 2>&1 &"); 
       header('Location: main.php');

        exit;
}
  else if(substr($filename, -4) == '.pdf')
{   

        if(isset($_SESSION['filemanager_pdf'])){
            unset($_SESSION['filemanager_pdf']);
        }
        $_SESSION['filemanager_pdf'] = $realFile;
         exec("php /var/www/html/filemanager/open_pdf.php > /dev/null 2>&1 &");
        header('Location: main.php');
        exit;
    }
     else if(substr($filename, -5) == '.jpeg' || substr($filename, -4) == '.jpg' || substr($filename, -4) == '.png' || substr($filename, -4) == '.gif' || substr($filename, -4) == '.webp' || substr($filename, -4) == '.PNG')
    {

        if(isset($_SESSION['filemanager_image'])){
            unset($_SESSION['filemanager_image']);
        }

        $_SESSION['filemanager_image'] = $realFile; 
          header('Location: main.php');
        exit;

    }   
    else
{
    echo "Something went wrong";
}


}

function handleWindow($realFile, $userPath, $filename)
  {  
    session_start();
        //----------works only in Safari-----------------------------
        // echo '           
        //     <script>
        //     window.open(
        //     "open_pdf.php",
        //     "PDF_Viewer",
        //     "width=1000,height=700,resizable=yes,scrollbars=yes"
        //         );
        //     window.location.href = "main.php?newpath=' . urlencode($userPath) . '";
        //     </script>';
        //-----------------------------------------------------------------------------

      if(substr($filename, -4) == '.pdf')
    {      
        $_SESSION['pdf_window'] = $realFile;
        session_write_close();
         exec("php /var/www/html/filemanager/open_pdf.php > /dev/null 2>&1 &");
        // header('Location: main.php?newpath=' . urlencode($userPath));
        header('Location: main.php');

      } else if(substr($filename, -5) == '.jpeg' || substr($filename, -4) == '.jpg' || substr($filename, -4) == '.png' || substr($filename, -4) == '.gif' || substr($filename, -4) == '.webp' || substr($filename, -4) == '.PNG')
    
      { 
         

         session_start();
        $_SESSION['image_window'] = $realFile;
        exec("php /var/www/html/filemanager/open_pdf.php > /dev/null 2>&1 &");
        // header('Location: main.php?newpath=' . urlencode($userPath));
        header('Location: main.php');
        exit;
    } else if(strtoupper(substr($filename, -4)) == '.MOV' || strtoupper(substr($filename, -4)) == '.MP4' || strtoupper(substr($filename, -5)) == '.WEBM' || strtoupper(substr($filename, -4)) == '.AVI' || strtoupper(substr($filename, -4)) == '.MKV')
    {
        session_start();
        $_SESSION['movie_window'] = $realFile;
      session_write_close();
         exec("php /var/www/html/filemanager/open_movie.php > /dev/null 2>&1 &");
       // header('Location: main.php?newpath=' . urlencode($userPath)); 
        header('Location: main.php');

    }

}

    


