<?php

if(isset($_SESSION['preview_image_path'])){
    $imagePath = $_SESSION['preview_image_path'];
    echo '<html>
    <head>
    <title>Image Preview</title>
    </head>
    <body style="margin:0; padding:0; overflow:auto;">
    <img src="' . htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') . '" alt="Image Preview" style="width:100%; height:auto; display:block; margin:0 auto;">
    </body>
    </html>';
    unset($_SESSION['preview_image_path']);
}

