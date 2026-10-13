<?php 

  /// Logout-Funktion
    if (isset($_POST['logout'])) {
    $_SESSION = array();
    session_destroy();
    header("Location: index.php");
    exit;
    }

