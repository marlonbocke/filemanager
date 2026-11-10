<?php
session_start();
error_log("-----------------------login----------------" . date('d.m.Y H:i:s') . "\n", 3, 'debug.log');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . 'debug.log');
error_reporting(E_ALL);


$user = trim($_POST["nutzer"] ?? "");
$password = $_POST["passwort"] ?? "";

if (empty($user) || empty($password)) {
    error_log("No user or password  \n", 3, 'debug.log'); 
    header("Location: /services/filemanager_new/index.html");
    exit;
}  

require_once 'db_connections.php';

$connection = connect_to_db(); // Use the connection from db_connections.php


$stmt = $connection->prepare("SELECT username, credentials, loginID, userpath FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows > 0) 
{
    
    $row = $result->fetch_assoc();
    $_SESSION['db_userData'] = $row;
    if ($password==$row["credentials"]) 
    {
        session_regenerate_id(true); 
        $_SESSION["username"] = $row["username"];
        $_SESSION["loginID"] = $row["loginID"];
        $_SESSION['user_path'] = $row["userpath"];
        header("Location: main.php");
        exit;

    } else {
        //error_log("Wrong password: $password. Correct password: " . $row['credentials'] . "\n", 3, 'debug.log');
        echo "<script>alert('Wrong password. Please try again.'); window.location.href = 'index.php';</script>";
        exit; 
      }
      
} else 
{
    error_log("User was not found in the Database  \n", 3, 'debug.log'); 
    echo "<script>alert('User was not found. Please try again.'); window.location.href = 'index.html';</script>";
    exit; 
}  

$stmt->close();
$connection->close();


?>
