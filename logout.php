<?php 

  /// Logout-Funktion
    if (isset($_POST['logout'])) {
    // Session löschen
    $_SESSION = array();
    session_destroy();
    //header("Location: https://bockelmanncloud.ddns.net/");
    //header("Location: https://hys2sa663d7g3yg8.myfritz.net/");
    header("Location: index.html");
    exit;
    }

