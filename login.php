<?php
session_start();


$user = trim($_POST["nutzer"] ?? "");
$password = $_POST["passwort"] ?? "";

if (empty($user) || empty($password)) {


    header("Location: index.html");
    exit;
}  

$connection = mysqli_connect("127.0.0.1", "admin", "M10%bock", "filemanager", 3306);

if (!$connection) {
   // die("Datenbankverbindung fehlgeschlagen");
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
        echo "<script>alert('Wrong password. Please try again.'); window.location.href = 'index.html';</script>";
        exit; 
      }
      
} else 
{
    echo "<script>alert('User not found. Please try again.'); window.location.href = 'index.html';</script>";
    exit; 
}  


$stmt->close();
$connection->close();
?>
