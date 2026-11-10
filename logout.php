<?php 
session_start();
  /// Logout-Funktion
    if (isset($_POST['logout'])) {
      error_log("-----------------------logout----------------" . date('d.m.Y H:i:s') . "\n", 3, 'debug.log');
    $_SESSION = array();
    session_destroy();
    header("Location: /services/filemanager_new/2FA-Setup/index.php");
    exit;
    }

