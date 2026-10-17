<?php 
session_start();
  /// Logout-Funktion
    if (isset($_POST['logout'])) {
      error_log("-----------------------logout----------------" . date('d.m.Y H:i:s') . "\n", 3, 'debug.log');
    $_SESSION = array();
    session_destroy();
    header("Location: index.php");
    exit;
    }

