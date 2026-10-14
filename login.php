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
    header("Location: index.html");
    exit;
}  



$file ="config/config.json";
if(!file_exists($file)) {
    error_log("No config data file was found.  \n", 3, 'debug.log'); 
    echo "<script>alert('No config data file found.'); window.location.href = 'config/config.html';</script>";
} else 
 

$jsondata = file_get_contents($file);
$config= json_decode($jsondata, true); 

    if (!is_array($config)) {
        error_log("No config data found  \n", 3, 'debug.log');     
        echo "<script>alert('No config data found. Please try again'); window.location.href = '/config/config.html';</script>";
        exit;
    }
    $config_data=$config[0];
    //var_dump($config); 

        
    $ip = $config_data["ip_address"];
    $port = $config_data['port'];
    $db_username = $config_data['username'];
    $db_password = $config_data['password'];
    $databasename = $config_data['databasename'];
    
    

    if(empty($ip) || empty($port)|| empty($db_username)|| empty($db_password)|| empty($databasename) )
    {
         error_log("Could not read all json data.  \n", 3, 'debug.log'); 
         echo "<script>alert('Something went wrong. Please try again'); window.location.href = 'config.html';</script>";
         exit;
    }   
    


$connection = mysqli_connect($ip, $db_username, $db_password, $databasename, $port);

if (!$connection) {
     echo "<script>alert('Connection failed.'); window.location.href = 'index.html';</script>";
     exit;
    }   

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
        $userpath = $row["userpath"];
        $_SESSION['user_path'] = $userpath;
        header("Location: /filemanager_new/main.php");
        exit;

    } else {
        error_log("Wrong password: $password. Correct password: " . $row['credentials'] . "\n", 3, 'debug.log');
        echo "<script>alert('Wrong password. Please try again.'); window.location.href = 'index.html';</script>";
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
