    <?php
    session_start();

    if (isset($_SESSION["user_path"]) && !isset($_SESSION["newpath"]))  {

      $baseFileDir = $_SESSION["user_path"];    //store userpath directory in baseFileDir 
      $_SESSION["baseFileDir"] = $baseFileDir;

      $fileDir = $baseFileDir;
      $allowedBasePath = $baseFileDir;
      $allowedBasePathReal = realpath($allowedBasePath);   
      $_SESSION["allowedBasePathReal"] = $allowedBasePathReal;


    } else if (isset($_SESSION['newpath']))
    { 
      $baseFileDir = $_SESSION["user_path"];
      $allowedBasePath = $baseFileDir;
      $allowedBasePathReal = realpath($allowedBasePath);   
      $_SESSION["allowedBasePathReal"] = $allowedBasePathReal;

    }
    else if(!isset($_SESSION["user_path"]) && !isset($_SESSION["newpath"])) {
      header('location: index.html');
    }



    $breadcrumbParts = [];
    $requestedNewPath = $_SESSION['newpath'] ?? $_SESSION['user_path']  ;
    $display_newpath = $_SESSION['newpath'] ?? $_SESSION['user_path'] ;
    $type = [];
    $filenames = [];
    $filesizes = [];

    $userRequestedPath = $requestedNewPath;
    $baseDirReal = $_SESSION["allowedBasePathReal"] ;    //real path of base directory
    $requestedRealpath = realpath($userRequestedPath);

    //created by google gemini AI for improving the security 
      $baseDirNormalized = rtrim($baseDirReal, '/\\') . DIRECTORY_SEPARATOR;      
      $requestedNormalized = rtrim($requestedRealpath, '/\\') . DIRECTORY_SEPARATOR;
    //-----------------------------------------------
    
       if (str_starts_with($requestedNormalized, $baseDirNormalized) == true && is_dir($requestedRealpath)) 
      {
        // The requested path is valid and within the allowed base directory
      $fileDir = rtrim($userRequestedPath, '/').DIRECTORY_SEPARATOR;      //set the file directory to the requested path
      $fileDir =  $requestedNormalized; 
      $relativeDisplayPath = ltrim(str_replace($baseDirReal, '', $requestedRealpath), '/'); //set the relative display path for breadcrumb navigation

    } else 
    {
     header('location: index.html');
    } 
    



    $scanDir = rtrim($fileDir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    $files = scandir($scanDir);
    foreach ($files as $file) {
      //loop for storing every filename
      if ($file === '.' || $file === '..' || $file === '.DS_Store' || $file === '._.DS_Store' || $file === '.Spotlight-V100' || $file === '.Trashes' || $file === '.' || str_starts_with($file, '._')) {    //skip some files with "." or ..
          continue;
      }
      $filePath = $fileDir.$file;                //set the file path

      if (is_file($filePath)) {

          $type[] = 'file';
          $date[] = date('d.m.Y H:i:s', filemtime($filePath));                                   
          $filenames[] = $file;                             //saving the filename in the array
          $filesizes[] = filesize($filePath);

      } elseif (is_dir($filePath)) {
          // It's a directory, skip or handle accordingly
          $type[] = 'Directory';
          $date[] = date('d.m.Y H:i:s', filemtime($filePath));
          $filenames[] = $file;
          $filesizes[] = filesize($filePath);
      }

    }

      if(!isset($_SESSION['newpath'])) 
      {
      $folderpath = $_SESSION['baseFileDir'];

      }
      else 
      {
       $folderpath = $_SESSION['newpath'];
      }


     //Constructing the relative web path for file operations
      $relativeWebPath = $baseFileDir.'/'.ltrim($relativeDisplayPath, '/').'/';
      $breadcrumbParts = [];
      if ($relativeDisplayPath !== '') {
      $breadcrumbParts = array_values(array_filter(explode('/', trim($relativeDisplayPath, '/')), static function ($part) {
      return $part !== '';
       }));
      }
     


       if (isset($_POST['clear_image_session']) || isset($_POST['clear_video_session']) || isset($_POST['clear_pdf_session']) || isset($_POST['clear_textfile_session'])) {

          unset($_SESSION['filemanager_image']);
          unset($_SESSION['filemanager_movie']);
    unset($_SESSION['filemanager_pdf']);
    unset($_SESSION['filemanager_textfile']);
    header("Location: main.php");
    exit;
    }









     function listDirectories($dir, $basePath = '')
        {
            $items = scandir($dir);
            
            echo '<ul class="directory-tree">';

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                } 

                $fullPath = $dir.'/'.$item;
                if (is_dir($fullPath)) {
                   $relativePath = $basePath.'/'.$item;
                 
                   echo '<input class="radioElements" type="radio" id="target_folder" name="target_folder" value="'.htmlspecialchars($relativePath, ENT_QUOTES, 'UTF-8').'">';
                   echo ' ';
                   echo '<label class="listElements">'.htmlspecialchars($item, ENT_QUOTES, 'UTF-8').'</label><br>';
                  
                    listDirectories($fullPath, $relativePath);
                }
            }
            echo '</ul>'; //generated by Google AI

        }


    ?>

    <!DOCTYPE html>
    <html>

    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple File Manager</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    </head>

    <body>
    <div class="container">
    <div class="header">
    <h1>📁 Simple Filemanager</h1>
      <div class="user-info">
      <span>👤 User: <?php echo htmlspecialchars($_SESSION['username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></span>
      <span>🖥️ IP: <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <div class="header-actions">
    <form method="post" action="logout.php">
      <input type="hidden" name="logout" value="true">
      <button type="submit" class="logout-btn">🚪 Logout</button>
    </form>
    <?php if (isset($_SESSION['filemanager_image']) || isset($_SESSION['filemanager_pdf']) || isset($_SESSION['filemanager_movie'])): ?>

    <form method="post">
    <button name="clear_pdf_session" class="logout-btn">back</button>
    </form>

    <?php else: ?>  
    <?php endif; ?>
    </div>
    </div> 
    <div class="content">
    <?php if (!(isset($_SESSION['filemanager_image']) || isset($_SESSION['filemanager_pdf']) || isset($_SESSION['filemanager_movie']))): ?>
      <div class="toolbar">
        <form>
          <button class="createfolder" id="createfolder" onclick="document.getElementById('create_folder_div').style.display = 'block';"
            type="button" name="request_createfolder">📁 create folder</button>
        </form>
        <form method="post" enctype="multipart/form-data">          
          <input type="file" multiple name="uploaded_file[]" value="" id="fileInput" class="visually-hidden">
          <button type="button" class="upload-trigger" id="uploadfile" onclick="document.getElementById('fileInput').click()">📤 files
            upload</button>
        </form>
        <form>
          <button class="selectfiles" id="takeaction_button"
            type="button" onclick="takeaction()"> Move </button>
        </form>
        <form>
          <button class="cancel" id="deletefiles"
            type="button" onclick="func_deletefiles()"> Delete files </button>
        </form>
        <form>
          <button class="cancel" id="removealldata_button"
            type="button" onclick="removealldata()"> Cancel </button>
        </form>
      </div>  
    <?php endif; ?>   

    <nav>
      <span>Current Directory:</span>
        <span class="droparea" id="droparea_0" folderpath="<?php echo htmlspecialchars($_SESSION['baseFileDir'], ENT_QUOTES, 'UTF-8'); ?>" ></form>
          <form method="POST" action="requestpath.php">
            <input type="hidden" name="homepath" value="<?php echo htmlspecialchars($_SESSION['baseFileDir'], ENT_QUOTES, 'UTF-8'); ?>">
            <button class="currentdir_buttons"> Home </button>
          </form>
          </span>
        <?php
        $accumulatedPath = $_SESSION['baseFileDir'];
        foreach ($breadcrumbParts as $index => $part):
          $accumulatedPath .= '/'.$part;
        ?>
        <span class="droparea" id="droparea_<?php echo $index;  ?>" folderpath="<?php echo htmlspecialchars($accumulatedPath, ENT_QUOTES, 'UTF-8'); ?>"> 
          <form method="post" action="requestpath.php" >
          <input type="hidden" name="accumulatedPath" value="<?php echo htmlspecialchars($accumulatedPath, ENT_QUOTES, 'UTF-8'); ?>">  
          <button class="currentdir_buttons"><?php echo htmlspecialchars($part, ENT_QUOTES, 'UTF-8');?></button>
          </form>

            <!-- <?php if ($index < count($breadcrumbParts) - 1): ?>
              <span> / </span>
            <?php endif; ?> -->
        </span>
        <?php endforeach; ?>
    </nav>
    </div>
      <!-- Display uploaded image preview and rename option -->
    <?php if (isset($_SESSION['filemanager_image'])): ?>
    <div class="preview-panel">
      <?php // Display the uploaded image 
        $imageData = base64_encode(file_get_contents($_SESSION['filemanager_image']));
        $src = 'data: '.mime_content_type($_SESSION['filemanager_image']).';base64,'.$imageData;
        $name = basename($_SESSION['filemanager_image']);
        $displayname = pathinfo($_SESSION['filemanager_image'], PATHINFO_FILENAME);
        ?>

        <div class="preview-actions">
          <form method="post" action="rename.php" >
            <input type="text" name="renamefile" value="<?php echo htmlspecialchars($displayname, ENT_QUOTES, 'UTF-8'); ?>" 
             class="preview-input">
            <input type="hidden" name="oldname" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="type" value="file">
            <input type="hidden" name="condition" value="from_image_display">
          </form>

          <form method="post" action="delete.php">
            <input type="hidden" name="delete" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="condition" value="from_image_display">
            <button type="submit" name="delete_image_file"
                class="preview-button-danger">delete</button>
          </form>
          <form method="post" action="download.php">
            <input type="hidden" name="file" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="ID" value="539485786049387579038">
            <input type="hidden" name="button" value="download">
            <button type="submit" name="download_image_file"
              class="preview-button-blue">Download</button>
          </form> 
        </div>
          <img src="<?php echo htmlspecialchars($src, ENT_QUOTES, 'UTF-8'); ?>" alt="Uploaded Image"
          class="preview-image">
            
    <?php elseif (isset($_SESSION['filemanager_movie'])): ?>
    <?php
      $videoPath = $_SESSION['filemanager_movie'];
      $name = basename($videoPath);
      $displayname = pathinfo($videoPath, PATHINFO_FILENAME);
      $Type = mime_content_type($videoPath);

      // Try Jellyfin first
      $useJellyfin = false;
      $jellyfinUrl = null;
      $jellyfinError = null;

      if (function_exists('curl_init')) {
        include_once 'jellyfin-api.php';
        if (isJellyfinAvailable()) {
          $jellyfinUrl = getJellyfinStreamUrl($videoPath);
          if ($jellyfinUrl) {
            $useJellyfin = true;
          }
        }
      }
      ?>
      <div class="preview-actions">
        <form method="post" action="rename.php">
          <input type="text" name="renamefile"
            value="<?php echo htmlspecialchars($displayname, ENT_QUOTES, 'UTF-8'); ?>"
            class="preview-input">
          <input type="hidden" name="oldname" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="type" value="file">
          <input type="hidden" name="condition" value="from_image_display">
        </form>
        
        <form method="post" action="delete.php">
          <input type="hidden" name="delete" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="condition" value="from_image_display">
          <button type="submit" name="delete_image_file"
            class="preview-button-danger">Delete</button>
        </form>
        <form method="post" action="download.php">
          <input type="hidden" name="file" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="ID"
            value="5">
          <input type="hidden" name="button" value="download">
          <button type="submit" name="download_image_file"
            class="preview-button-blue">Dowload</button>
        </form>
      </div>

       <?php if ($useJellyfin): ?>
           //Jellyfin is not in use
      <?php else: ?> 
        It is still a work in progress. Sorry bro :(
        
      <?php endif; ?>
    </div>
    <?php elseif (isset($_SESSION['filemanager_textfile'])): ?>

    <div class="preview-panel">
      <?php // Display the uploaded text file 

        $textFilePath = $_SESSION['filemanager_textfile'];
        $textContent = htmlspecialchars(file_get_contents($textFilePath), ENT_QUOTES, 'UTF-8');
        ?>
      <textarea readonly
        class="text-preview"><?php echo $textContent; ?></textarea>
      <form method="post" class="preview-back-form">
        <button name="clear_textfile_session"
          class="preview-button-danger">Go
          back</button>
      </form>
    </div>

    <?php elseif (isset($_SESSION['filemanager_pdf'])):

    $name = basename($_SESSION['filemanager_pdf']);
    $displayname = pathinfo($_SESSION['filemanager_pdf'], PATHINFO_FILENAME);
    ?>
    <div class="preview-actions">
        <form method="post" action="rename.php">
          <input type="text" name="renamefile"
            value="<?php echo htmlspecialchars($displayname, ENT_QUOTES, 'UTF-8'); ?>"
            class="preview-input">
          <input type="hidden" name="oldname" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="type" value="file">
          <input type="hidden" name="condition" value="from_pdf_display">
        </form>
        <form method="post" action="delete.php">
          <input type="hidden" name="delete" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="condition" value="from_image_display">
          <button type="submit" name="delete_image_file"
            class="preview-button-danger">Löschen</button>
        </form>
        <form method="post" action="download.php">
          <input type="hidden" name="file" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="ID" value="539485786049387579038">
          <input type="hidden" name="button" value="download">
          <button type="submit" name="download_image_file"
            class="preview-button-blue">Download</button>
        </form>
        <form method="post" action="open.php">
          <input type="hidden" name="file" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
          <input type="hidden" name="path" value="<?php echo $_SESSION['newpath']; ?>">
          <input type="hidden" name="ID" value="539485786049387579038">
          <input type="hidden" name="button" value="openfileWindow">
          <button  onclick="openwindow()"type="submit" name="pdf_open_in_new_window"
          class="preview-button-blue">Open in new window</button>
        </form>  
      </div>
      <div class="preview-actions">
      </div>
      <iframe src="open_pdf.php" class="pdf-preview-frame"></iframe>
    </div>

    </div>
    <?php else: ?>


    <!--Show all files and folders in the current directory -->
    <table id="table01" class="file-list-table" scroll="yes">
    <!-- <thead>
      <tr>
        <th class="item">
          <span class="item-type table-header-label">Name</span>
          <span class="item-type table-header-label"></span>
          <span class="item-type table-header-label">Options</span>

        </th>
      </tr>
    </thead> -->
    <div id="create_folder_div" class="create-folder-panel">
    <form method="post" action="createfolder.php" class="create-folder-form">
      <input type="text" name="createfolder" placeholder="Name of the new folder" required class="create-folder-name">
      <button type="submit" class="inline-primary-button">Create</button>
      <button name="cancel" type="button" onclick="document.getElementById('create_folder_div').style.display='none';" class="inline-danger-button">Cancel</button>
      </form>
    </div>

   <table id="table" class="file-list-table" scroll="yes">

    <?php for ($i = 0; $i < count($filenames); $i++): ?>

      <tr id="<?php echo $filenames[$i]; ?>">
        <?php if ($type[$i] === 'Directory'): ?>

          <td class="item" id="<?php echo $filenames[$i]; ?>" draggable="true" data_path="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
            
            <div name="checkbox_div" id="<?php echo $filenames[$i];?>"> 
             <input type="checkbox" id="checkbox_<?php echo$filenames[$i];?>" name="select" value="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>" class="selection-checkbox"></input> 
            </div>

            <img src="images/folder_icon2.png" alt="Folder Icon" class="file-icon">
            <section class="droparea" id="droparea_<?php echo $i; ?>" droppable="true" folderpath="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i].'/', ENT_QUOTES, 'UTF-8'); ?>">
              <form method="post" action="requestpath.php">
                <input type="hidden" name="newpath" value="<?php echo htmlspecialchars($_SESSION['newpath'].'/'.$filenames[$i].'/', ENT_QUOTES, 'UTF-8'); ?>">
                <button class="directory-name-button" type="submit"><?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?></button>
              </form>
            </section>  
          <div id="rename_divdir_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>"
              class="hidden">
              <form method="post" action="rename.php" class="rename-form">
                <input type="text" name="renamedir" value="<?php echo htmlspecialchars(basename($filenames[$i]), ENT_QUOTES, 'UTF-8'); ?>" autofocus
                  onfocus="this.select();" />
                <input type="hidden" name="oldname" value="<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="type" value="directory">
                <button type="submit"
                  class="inline-primary-button">Speichern</button>
                <button name="cancel" type="button"
                  onclick="document.getElementById('rename_divdir_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>').style.display='none';"
                  class="inline-danger-button">Cancel</button>
              </form>
            </div>
            </td>

            <td>
            <div>
              <span><?php echo htmlspecialchars($date[$i], ENT_QUOTES, 'UTF-8'); ?></span>
              <span>Folder</span>
            </div>
            <button class="items_buttons_options_dir" id="button_more"
              onclick="manage_window(event, 'opendir_menu', 'directory','<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>')">More</button>
          </td>

        <?php elseif ($type[$i] === 'file'): ?>

          <?php if (substr($filenames[$i], -5) == '.jpeg' || substr($filenames[$i], -4) == '.jpg' || substr($filenames[$i], -4) == '.png' || substr($filenames[$i], -4) == '.gif' || substr($filenames[$i], -4) == '.DNG' || substr($filenames[$i], -4) == '.PNG'): ?>
            <?php $imageData = base64_encode(file_get_contents($_GET['newpath'].'/'.$filenames[$i]));
                  $previewscr = 'data: '.mime_content_type($_GET['newpath'].'/'.$filenames[$i]).';base64,'.$imageData;?>

          <td class="item" draggable="true" data_path="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
              <div name="checkbox_div" id="<?php echo $filenames[$i];?>"> 
             <input type="checkbox" id="checkbox_<?php echo$filenames[$i];?>" name="select" value="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>" class="selection-checkbox"></input> 
            </div>
              <img class="img file-icon" src="images/image.png" alt="Image Icon">
              <form method="POST" action="open.php">
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="path" value="<?php echo $folderpath; ?>">
                <input type="hidden" name="ID" value="539485786049387579038">
                <input type="hidden" name="button" value="openfileWindow">
                <button class="items_buttons"
                type="submit"><?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?></button>
              </form>
              <!-- Rename image file----------- -->
              <div id="rename_divfile_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>"
                class="hidden">
                <form method="POST" action="rename.php" class="rename-form">
                  <input type="text" name="renamefile"
                    value="<?php echo htmlspecialchars(pathinfo($filenames[$i], PATHINFO_FILENAME), ENT_QUOTES, 'UTF-8'); ?>" autofocus onfocus="this.select();" />
                  <input type="hidden" name="oldname"
                    value="<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="type" value="file">
                  <button type="submit"
                    class="inline-primary-button">Speichern</button>
                  <button name="cancel" type="button"
                    onclick="document.getElementById('rename_divfile_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>').style.display='none';"
                    class="inline-danger-button">Cancel</button>
                </form>   
              </div>
           </td> 
            <td>
              <div>
                <span ><?php echo htmlspecialchars($date[$i], ENT_QUOTES, 'UTF-8'); ?></span>
                <span>Image</span>
              </div>
              <button class="items_buttons_options_file" id="button_more"
                onclick="manage_window(event, 'openfile_menu', 'file', '<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>')">More</button>
            </td>
            
          <?php elseif(strtolower(pathinfo($filenames[$i], PATHINFO_EXTENSION)) === 'mov' || strtolower(pathinfo($filenames[$i], PATHINFO_EXTENSION)) === 'mp4'): ?>      
           <td class="item" draggable="true" data_path="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
            <div name="checkbox_div" id="<?php echo $filenames[$i];?>"> 
             <input type="checkbox" id="checkbox_<?php echo$filenames[$i];?>" name="select" value="<?php echo htmlspecialchars($folderpath.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>" class="selection-checkbox"></input> 
            </div>
              <img class="img file-icon" src="images/mov_icon.png" alt="File Icon">
              <form method="POST" action="open.php">
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="path" value="<?php echo $_SESSION['newpath']; ?>">
                <input type="hidden" name="ID" value="539485786049387579038">
                <input type="hidden" name="button" value="openfileWindow">
                <button class="items_buttons"
                  type="submit"><?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?></button>
              </form>
              <div id="rename_divfile_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>"
                class="hidden">
                <form method="post" action="rename.php" class="rename-form">
                  <input type="text" name="renamefile"
                    value="<?php echo htmlspecialchars(pathinfo($filenames[$i], PATHINFO_FILENAME), ENT_QUOTES, 'UTF-8'); ?>" autofocus
                    onfocus="this.select();" />
                  <input type="hidden" name="oldname"
                    value="<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="type" value="file">
                  <button type="submit"
                    class="inline-primary-button">Speichern</button>
                  <button name="cancel" type="button"
                    onclick="document.getElementById('rename_divfile_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>').style.display='none';"
                    class="inline-danger-button">Cancel</button>
                </form>
              </div> 
            </td>  
              <td>
              <div>
                <span><?php echo htmlspecialchars($date[$i], ENT_QUOTES, 'UTF-8'); ?></span>
                <span>Video</span>
              </div>
              <button class="items_buttons_options_file" id="button_more"
                onclick="manage_window(event, 'openfile_menu', 'file', '<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>')">More</button>
              </td> 

          <?php elseif (substr($filenames[$i], -4) == '.pdf'): ?>      
            <td class="item" draggable="true" data_path="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
              <div name="checkbox_div" id="<?php echo $filenames[$i];?>"> 
             <input type="checkbox" id="checkbox_<?php echo$filenames[$i];?>" name="select" value="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>" class="selection-checkbox"></input> 
            </div>
              <img class="img file-icon" src="images/file_icon2.png" alt="File Icon">
              <form method="POST" action="open.php">
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="path" value="<?php echo htmlspecialchars($folderpath, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="ID" value="539485786049387579038">
                <input type="hidden" name="button" value="openfileWindow">
                <button class="items_buttons"
                  type="submit"><?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?></button>
              </form>
                <div id="rename_divfile_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>" class="hidden">

                <form method="post" action="rename.php" class="rename-form">
                  <input type="text" name="renamefile"
                    value="<?php echo htmlspecialchars(pathinfo($filenames[$i], PATHINFO_FILENAME), ENT_QUOTES, 'UTF-8'); ?>" autofocus
                    onfocus="this.select();" />
                  <input type="hidden" name="oldname" value="<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="type" value="file">
                  <button type="submit"
                    class="inline-primary-button">Save</button>
                  <button name="cancel" type="button"
                    onclick="document.getElementById('rename_divfile_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>').style.display='none';"
                    class="inline-danger-button">Cancel</button>
                  </form>
                </div>
            </td> 
            <td>
              <div>
                <span><?php echo htmlspecialchars($date[$i], ENT_QUOTES, 'UTF-8'); ?></span>
                <span>PDF file</span>
              </div>
              <button class="items_buttons_options_file" id="button_more"
                onclick="manage_window(event, 'openfile_menu', 'file', '<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>')">More</button>
            </td> 

          <?php else: ?>
            <td class="item" draggable="true" data_path="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
              <div name="checkbox_div" id="<?php echo $filenames[$i];?>"> 
             <input type="checkbox" id="checkbox_<?php echo$filenames[$i];?>" name="select" value="<?php echo htmlspecialchars($folderpath.'/'.$filenames[$i], ENT_QUOTES, 'UTF-8'); ?>" class="selection-checkbox"></input> 
            </div>
              <img class="img file-icon" src="images/unbekannte-mail.png" alt="File Icon">
              <form method="POST" action="open.php">
                <input type="hidden" name="file" value="<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="path" value="<?php echo htmlspecialchars($folderpath, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="ID" value="539485786049387579038">
                <input type="hidden" name="button" value="openfileWindow">
                <button class="items_buttons"
                  type="submit"><?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?></button>
              </form>
            <div id="rename_divfile_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>"
                class="hidden">
                <form method="post" action="rename.php" class="rename-form">
                  <input type="text" name="renamefile"
                    value="<?php echo htmlspecialchars(pathinfo($filenames[$i], PATHINFO_FILENAME), ENT_QUOTES, 'UTF-8'); ?>" autofocus
                    onfocus="this.select();" />
                  <input type="hidden" name="oldname"
                    value="<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>">
                  <input type="hidden" name="type" value="file">
                  <button type="submit"
                    class="inline-primary-button">Save</button>
                  <button name="cancel" type="button"
                    onclick="document.getElementById('rename_divfile_<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>').style.display='none';"
                    class="inline-danger-button">Cancel</button>
                </form>
              </div>
            </td> 
            <td>
              <div>
                <span><?php echo htmlspecialchars($date[$i], ENT_QUOTES, 'UTF-8'); ?></span>
                <span>File</span>
              </div>
              <button class="items_buttons_options_file" id="button_more"
                onclick="manage_window(event, 'openfile_menu', 'file', '<?php echo htmlspecialchars($filenames[$i], ENT_QUOTES, 'UTF-8'); ?>')">More</button>
            </td>
          <?php endif; ?>

        <?php endif; ?>
      </tr>
    <?php endfor; ?>
    </tbody>
    </table>
    <?php endif; ?>
    </div>

    <?php if (isset($_SESSION["pdf_window"]) || isset($_SESSION["image_window"])): ?>
      <script>
        const url = '/filemanager_new/open_pdf.php';
        const target = '_blank';
        const features = 'width=800,height=600,popup=yes,resizable=yes,scrollbars=yes';
        window.open(url, target, features);
      </script>
    <?php elseif (isset($_SESSION["movie_window"])): ?>
      <script>
        const url = '/filemanager_new/open_movie.php';
        const target = '_blank';
        const features = 'width=800,height=600,popup=yes,resizable=yes,scrollbars=yes';
        window.open(url, target, features);
      </script>
    <?php endif; ?>


    <!-- Global File Options Modal -->
    <div id="file_options_Modalfile" class="hidden">
      <form id="rename_form_file" onsubmit="return false;">
        <input type="hidden" name="request_renamefile" value="">
        <input type="hidden" name="type" value="">
        <button class="items_buttons" type="button" onclick="showRenameDiv('file')">
          <span>✏️</span> Rename
        </button>
      </form>
      <form method="POST" action="download.php" id="download_form_file">
        <input type="hidden" name="file" value="">
        <input type="hidden" name="ID" value="539485786049387579038">
        <input type="hidden" name="button" value="download">
        <button class="items_buttons" type="submit"><span>⬇️</span> Download</button>
      </form>
      <form method="POST" action="delete.php" id="delete_form_file">
        <input type="hidden" name="delete" value="">
        <input type="hidden" name="newpath" value="<?php echo $folderpath ?>">
        <button class="items_buttons" type="submit"><span>🗑️</span> Delete </button>
      </form>
      <form id="movefolder">
        <button
          class="items_buttons"
          id="move_to_folder_button"
          onclick="manage_window(event, 'movedata_view','file', document.getElementById('move_to_folder_button').value, '<?php echo $folderpath; ?>'  )">
          <span>📁</span> Move to folder
        </button>
      </form>

      <form method="POST" action="open.php" id="openpreviewpdf_form">
        <input type="hidden" name="file" value="">
        <input type="hidden" name="path" value="<?php echo $folderpath; ?>">
        <input type="hidden" name="ID" value="539485786049387579038">
        <input type="hidden" name="button" value="openfile">
        <button class="items_buttons" type="submit"><span>👁️</span> Preview</button>
      </form>
    </div>

    <!-- Global Directory Options Modal -->
    <div id="dir_options_Modalfile" class="hidden">
      <form id="rename_form_directory" onsubmit="return false;">
        <input type="hidden" name="request_renamedir" value="">
        <button class="items_buttons" type="button" onclick="showRenameDiv('directory')">
          <span>✏️</span> Rename
        </button>
      </form>

      <form method="POST" action="download.php" id="download_form_directory">
        <input type="hidden" name="file" value="">
        <input type="hidden" name="ID" value="539485786049387579038">
        <input type="hidden" name="button" value="download">
        <button class="items_buttons" type="submit"><span>⬇️</span> Download</button>
      </form>

      <form method="POST" action="delete.php" id="delete_form_directory">
        <input type="hidden" name="delete" value="">
        <input type="hidden" name="newpath" value="<?php echo $folderpath; ?>">
        <button class="items_buttons" type="submit"><span>🗑️</span> Delete</button>
      </form>
      <button
        class="items_buttons"
        id="move_to_folder_button"
        onclick="manage_window(event, 'movedata_view', 'directory', document.getElementById('move_to_folder_button').value, '<?php echo $folderpath ?>'  )" >
        <span>📁</span> Move to folder
      </button>
    </div>

         <!-- Movedata View -->
    <div  id="movedata_view" class="hidden">
      <input id="movedata_item" name="movedata_item" value=""> 
      <br>
      <label class="SelectTargetFolder">Select Target Folder:</label>
      <div class="elementmoveDataView">
      <br>
      <br>
        <input class="radioElements" type="radio" id="target_folder" name="target_folder" value="<?php echo htmlspecialchars($_SESSION["user_path"], ENT_QUOTES, 'UTF-8')?>">
        <label class="listElements">Home</label><br>     
       <?php listDirectories($allowedBasePath, $allowedBasePath);?>
       <br>
       <br>
       </div>
      <button class="movedataWindow_buttons" type="button" 
      onclick="
      move_file(document.getElementById('movedata_item').value, document.querySelector('input[name=target_folder]:checked').value)">
      <span>➡️</span> Move Here
      </button>

      <button class="movedataWindow_buttons" type="button" name="closeWindow_button" value="movedataView" onclick="close_moveRename_Options()">
      <span>✕</span>close
      </button>
    </div>

    <div id="div_loader" class="hidden">
      Data is being loaded, please wait...
      <br>
      <div class="box">
        <div id="progressText">0%</div>
        <div class="bar">
          <div id="barFill"></div>
        </div>
      </div>
    </div>

    </div>
    </body>

    </html>


        
<script>
// alert("Please note that the file manager is still in development. Some features may not work as expected. If you encounter any issues, please report them to the development team. Thank you for your understanding and support!");
// alert("Rename functionality is not working correctly. Please improve rename.php before you improve other features.");
  let currentfilename;
  let currentRow = null;
  let currentButton = null;
  let currentMenu = null;
  let renameDivFile = null;
  let renameDivDir = null;



  // BEGIN CHANGE: position the options menu below its More button
  function positionMenu(menu, button) {
 
      const rect = button.getBoundingClientRect(); // Get the position of the clicked button
      const modalWidth = 320; // Approximate width of the modal
        let left = rect.left - modalWidth / 2 + rect.width / 2; // Center the modal horizontally
        if (left + modalWidth > window.innerWidth) { // Ensure the modal doesn't go off-screen
          left = window.innerWidth - modalWidth - 10; // 10px padding from the right edge
        }
        if (left < 10) { // Ensure the modal doesn't go off-screen on the left
          left = 1; // 10px padding from the left edge
        }
        menu.style.top = (rect.top - menu.offsetHeight - 10) + 'px'; // Position above the button with 10px margin
        menu.style.left = left + 'px';   // Set the calculated left position
    

  }
  // END CHANGE: position the options menu below its More button

  function manage_window(event, window, type, filename, currentdirectory) {
    
    closeOptions(currentRow, currentMenu, currentButton);
      currentfilename = filename;
      
      const file_options = document.getElementById('file_options_Modalfile');
      const dir_options = document.getElementById('dir_options_Modalfile');
      

      const renameDivFile = document.getElementById('rename_divfile_' + filename);
      const renameDivDir = document.getElementById('rename_divdir_' + filename);

      const movedataView = document.getElementById('movedata_view');


      currentRow = document.getElementById(currentfilename);
      event.preventDefault();
      event.stopPropagation();
      currentButton = event.currentTarget || event.target;
       

      if (window === 'movedata_view') {

        if (currentRow) {
             currentRow.style.backgroundColor = '#804f4f';
        }
        file_options.style.display = 'none';
        dir_options.style.display = 'none';
        document.getElementById('movedata_item').value = currentdirectory + '/' + filename;
        movedataView.style.display = 'block';
        


      } else if (type === 'file' && window === 'openfile_menu') { 
        if (currentRow) {
         currentRow.style.backgroundColor = '#583f3f';
        }
        dir_options.style.display='none';
        // Open file options
        document.getElementById('rename_form_file').querySelector('input[name="request_renamefile"]').value = filename;   
        document.getElementById('download_form_file').querySelector('input[name="file"]').value = filename;
        document.getElementById('delete_form_file').querySelector('input[name="delete"]').value = filename;
        document.querySelector('#rename_form_file input[name="type"]').value = 'file';
        document.getElementById('move_to_folder_button').value = filename;
        document.getElementById('openpreviewpdf_form').querySelector('input[name="file"]').value = filename;
        file_options.style.display = 'block';
        currentMenu = file_options;
        // CHANGE: anchor the file menu below the clicked More button
        positionMenu(file_options, currentButton);

      } else if (type === 'directory' && window === 'opendir_menu') {
        if (currentRow) {
          currentRow.style.backgroundColor = '#e0e0e0';
        }
        // Open directory options modal
        file_options.style.display='none';
        document.getElementById('rename_form_directory').querySelector('input[name="request_renamedir"]').value = filename;
        document.getElementById('download_form_directory').querySelector('input[name="file"]').value = filename;
        document.getElementById('delete_form_directory').querySelector('input[name="delete"]').value = filename;
        document.getElementById('move_to_folder_button').value = filename;
        dir_options.style.display = 'block';
        currentMenu = dir_options;
        // CHANGE: anchor the directory menu below the clicked More button
        positionMenu(dir_options, currentButton);
      } 

    }  


    // Function to show rename input field
    function showRenameDiv(type) {
      typoffile = type;
      const modalfile = document.getElementById('file_options_Modalfile');
      const modaldir = document.getElementById('dir_options_Modalfile');

      if (type === 'file') {
        const filename = modalfile.querySelector('input[name="request_renamefile"]').value;
        renameDivFile = document.getElementById('rename_divfile_' + filename);
         
        if (renameDivFile) {
          currentMenu='rename_options';
          renameDivFile.style.display = 'block';
          renameDivFile.querySelector('input[name="renamefile"]').select();
          modalfile.style.display='none';
          
          } 
      
      } else if (type === 'directory') {
        const filename = modaldir.querySelector('input[name="request_renamedir"]').value;
        renameDivDir = document.getElementById('rename_divdir_' + filename);

        if (renameDivDir) {
          currentMenu='rename_options';
          renameDivDir.style.display = 'block';
          renameDivDir.querySelector('input[name="renamedir"]').select();
          modaldir.style.display = 'none';
        } 
      }
    }

   document.addEventListener('click', function (event) {
    
       closeOptions(currentRow, currentButton, renameDivDir, renameDivFile);
      
    });

    window.addEventListener('resize', function () {
      if (currentMenu && currentButton) {
        positionMenu(currentMenu, currentButton);
      }
    });

    window.addEventListener('scroll', function () {
      if (currentMenu && currentButton) {
        positionMenu(currentMenu, currentButton);
      }
    }, true);


    // Close modals when clicking outside of them
    // // Close all windows            
    function closeOptions(currentRow, currentMenu, currentButton, renameDivDir, renameDivFile) {
  

      const modalfile = document.getElementById('file_options_Modalfile');
      const modaldir = document.getElementById('dir_options_Modalfile');
      
     

      if(modalfile.style.display === 'block' || modaldir.style.display === 'block' )
      {
         if (currentRow) {
        currentRow.style.backgroundColor = '';
        currentRow = null;
      }
      currentMenu = null;
      currentButton = null;
       //renameDivDir.style.display='none';
      // renameDivFile.style.display='none';
      if (modalfile.style.display === 'block') {
        modalfile.style.display = 'none';
      } else if (modaldir.style.display === 'block') {
        modaldir.style.display = 'none';
      }

      } 

    }

  function close_moveRename_Options()
  {

    if(document.getElementsByName('closeWindow_button')[0].value =="movedataView")
    {
      const movedataView = document.getElementById('movedata_view');
     console.log("close button pressed   " +document.getElementsByName('closeWindow_button')[0].value);

      if (movedataView.style.display === 'block') {
         movedataView.style.display = 'none';
            const cr1 = document.getElementById(currentfilename);
            cr1.style.backgroundColor = '';


       } 

    }
    
  }

  </script>

  <script>
        document.getElementById('takeaction_button').style.display ='none'; 
        document.getElementById('removealldata_button').style.display ='none';
        document.getElementById('deletefiles').style.display ='none';
        
        const items = document.querySelectorAll('.item');
        let data = [];    
        
        items.forEach(item => {
        item.addEventListener('mouseover', (event) => {
        event.currentTarget.style.backgroundColor = '#d4c0c0';
        const row = event.currentTarget.closest('tr');              
        const rowdiv_id = row.querySelector('div[name="checkbox_div"]');
        const checkbox = document.getElementById('checkbox_'+rowdiv_id.id);

        checkbox.style.display='block';  
        select(checkbox);

      });

      item.addEventListener('mouseleave', (event) => {
        event.currentTarget.style.backgroundColor = '';
        const row = event.currentTarget.closest('tr');              
        const rowdiv_id = row.querySelector('div[name="checkbox_div"]');
        let checkbox = document.getElementById('checkbox_'+rowdiv_id.id);
        if(!(checkbox.checked))
        { 
        checkbox.style.display='none';
        select(checkbox);   
        }      
      });
  
      item.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', item.getAttribute('data_path'));
      });
    });

 
    function select(checkbox)
    {
        
    if(checkbox.checked && !(data.includes(checkbox.value)))
    {
      data.push(checkbox.value);
        //console.log("data is not in array"+checkbox.value); 
      } else if(!(checkbox.checked) && data.includes(checkbox.value)) 
      { 
          //console.log("data should remove from arry"+checkbox.value); 

        //----------------------------------------------------------------------
          // Source - https://stackoverflow.com/a/5767357
          // Posted by Tom Wadley, modified by community. See post 'Timeline' for change history
          // Retrieved 2026-09-18, License - CC BY-SA 4.0
          const index = data.indexOf(checkbox.value)  
          if(index > -1)   // only splice array when item is found
          {
          data.splice(index, 1);    // 2nd parameter means remove one item only
          }      
          //----------------------------------------------------
      }
  
       if(data.length < 2)
        {
          document.getElementById('takeaction_button').style.display ='none';
          document.getElementById('removealldata_button').style.display ='none';
          document.getElementById('deletefiles').style.display ='none';
          document.getElementById('createfolder').style.display ='block';
         document.getElementById('uploadfile').style.display ='block';
          
         
        } else 
        {
         document.getElementById('takeaction_button').style.display ='block';
         document.getElementById('removealldata_button').style.display ='block';
         document.getElementById('createfolder').style.display ='none';
         document.getElementById('uploadfile').style.display ='none';
         document.getElementById('deletefiles').style.display ='block';
          
         

        }
    }

    function takeaction() {

      //   if(data.length==0)
      //   {
      //   alert("Please select some date");

      //   }    
      // console.log("action");
      
      //  console.log("action_arry_lenght:      "+data.length);  

      // for( var i = 0; i < data.length; i++)
      //   {
      //   console.log("action :"+data[i]);
      //   }  

        const movedataView = document.getElementById('movedata_view');
         document.getElementById('movedata_item').value = data;
        movedataView.style.display = 'block';


    }   
    
    function removealldata()
    {
      //alert("Are you sure? ");
        data = [];
        //some parts are generated by genimi 
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
          checkbox.checked = false;
       checkbox.style.display='none';
      document.getElementById('takeaction_button').style.display ='none';
      document.getElementById('removealldata_button').style.display ='none';
      document.getElementById('deletefiles').style.display ='none';
       document.getElementById('createfolder').style.display ='block';
         document.getElementById('uploadfile').style.display ='block';
    
    });
  

  }
  
     
  
    function move_file(data_path, path) {

     const moveData = [];
      console.log('MoveData:    '+moveData.length);
      console.log('data:    '+data.length);
        

      if(data.length >= 2 )
        { 
      console.log("more files are selected");

        moveData.push(...data);
       for( var i = 0; i < moveData.length; i++)
        {
        console.log("moveData :"+moveData[i]);
        }  
      } else if(data_path)
      {
        console.log(" a single file is selected");
        moveData.push(data_path);
       for( var i = 0; i < moveData.length; i++)
        {
        console.log("moveData :"+moveData[i]);
        }
      } 
     
       console.log('destination ' + path);

      fetch('move_file.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          source: moveData,
          destination: path
        })
      })
         .then(response => response.json())
         .then(dataOut => {
          console.log(dataOut.message);
          //alert('Feedback:  '+dataOut.message);
           window.location.reload();
        })
        .catch(error => {
          console.log(error);
          alert('Error:' + error);
          window.location.reload();
        });

      }

   function  func_deletefiles() {
    console.log("deletefiles");

    if (confirm("Press a button!\nEither OK or Cancel.")) {
      
      fetch('deleteMorefiles.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          files: data 
      
        })
      })
         .then(response => response.json())
         .then(dataOut => {
          console.log(dataOut.message);
          //alert('Feedback:  '+dataOut.message);
           window.location.reload();
        })
        .catch(error => {
          console.log(error);
          alert('Error:  ' + error);
           //window.location.reload();
        });

    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', updateDropAreas);
  function updateDropAreas() {
      const dropAreas = document.querySelectorAll('.droparea');
      dropAreas.forEach(dropArea => {
        dropArea.addEventListener('dragover', (e) => {
          e.preventDefault();
          dropArea.style.backgroundColor = '#28a234ff';
        });

        dropArea.addEventListener('dragleave', () => {
          dropArea.style.backgroundColor = '';
        });

        dropArea.addEventListener('drop', (e) => {
          e.preventDefault();
          dropArea.style.backgroundColor = '';
          const data_path = e.dataTransfer.getData('text/plain');
          const path = dropArea.getAttribute('folderpath');


          move_file(data_path, path);


        });
      });
    }
</script>


<!--JavaScript Section for File Uploads-->
  <script>
   
 document.getElementById('fileInput').addEventListener('change', function () {
      handleFileUpload(this.files);
    });
    function handleFileUpload(files) {
      //alert('Datei-Upload gestartet' + files.length + ' Datei(en) ausgewählt.');
      const formData = new FormData();
      const currentPath = '<?php echo $_GET['newpath'] ?? ""; ?>';
      //formData.append('newpath', currentPath);
      for (let i = 0; i < files.length; i++) {
        formData.append('uploaded_file[]', files[i]);
      }
      formData.append('newpath', '<?php echo $_GET['newpath'] ?? ""; ?>');
      loadscreen(formData);
      fetch('uploadfile.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
          console.log(data);
          if (data.success) window.location.reload();
          else if (data.errors) alert(`Fehler:\n${data.errors.join("\n")}`);
        })
        .catch(error => {
          console.error('Fehler bei der Serveranfrage:', error);
          alert('Fehler beim Upload: ' + error.message);
        });

    }



    function loadscreen(formData) {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "uploadfile.php", true);
      document.getElementById("div_loader").style.display = "flex";
      xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
          const percent = Math.round((e.loaded / e.total) * 100);
          document.getElementById("progressText").innerText = percent + "%";
          document.getElementById("barFill").style.width = percent + "%";
        }
      };
      xhr.onload = function () { document.getElementById("div_loader").style.display = "none"; };
      xhr.onerror = function () { alert("Upload fehlgeschlagen"); };
      xhr.send(formData);
    }

  </script>


 <!-- JavaScript Section for Modals and Keyboard Events -->
  <script>

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
      } else if (event.key === 'Escape') {
         window.location.href = 'https://ugreennas/filemanager_new/main.php'; // Replace with your desired URL  
      }
    });

  </script>


<!-- // ----------------------------------------------------------------------
    // let number = 1;
    // let tdv = []; 
    // tdv.length = 2;    
  
  //  const table = document.querySelector('.table');
    

  //     table.addEventListener('mouseover', (event) => {

  //     const row = event.currentTarget.closest('tr'); 
  //      console.log("row value:"+row);
  //      //row.event.Target.style.backgroundColor = '#d4c0c0';
        


  //      });

        // Source - https://stackoverflow.com/a/67724247
      // Posted by chrwahl, modified by community. See post 'Timeline' for change history
      // Retrieved 2026-09-13, License - CC BY-SA 4.0
      //functionality by stackoverflow, modified by marlon 


      //   document.getElementById('table').addEventListener('mouseover', e => {
      //    let td = e.target.closest('td[class="item"]');
      //     number++;
      //    tdv[number] = td.id;
        
      //   if (td) {
      //   e.target.style.backgroundColor='#d4c0c0';
        
      //    console.log(number, e.target.innerText);
      //     // console.log(number, tdv[tdv.length-2]);

      //   } 
      //    console.log("test", tdv[1]);
  

       
  



    
      //   // let tr = e.target.closest('tr');

      //   // if(tr)
      //   // {
      //   //    const checkbox = document.getElementById('checkbox_'+tr.id);
      //   //     console.log(checkbox);
      //   //     checkbox.style.display='block';
      //   // }
      // });

      // --------------------------------------------------------------------------------------------------------------------------------------------
       


      // Source - https://stackoverflow.com/a/5767357
// Posted by Tom Wadley, modified by community. See post 'Timeline' for change history
// Retrieved 2026-09-18, License - CC BY-SA 4.0

const array = [2, 5, 9];

console.log(array);

const index = array.indexOf(5);
if (index > -1) { // only splice array when item is found
  array.splice(index, 1); // 2nd parameter means remove one item only
}

// array = [2, 9]
console.log(array);

//https://stackoverflow.com/questions/22938341/count-the-number-of-checked-checkboxes-in-html -->
