//developed with AI assistance
<?php
    session_start();
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../db_connections.php';

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    use OTPHP\TOTP;
    use Psr\Clock\ClockInterface;

    // PSR-20 Clock Implementierung
    class MyClock implements ClockInterface {
        public function now(): \DateTimeImmutable {
            return new \DateTimeImmutable();
        }
    }

    $clock = new MyClock();




    // check if the 2FA columns exist, if not, create them
    function check_and_create_2fa_columns() {
         
        $connection = connect_to_db(); // Use the connection from db_connections.php
        // Prüfen ob totp_secret Spalte existiert
        $result = mysqli_query($connection, "SHOW COLUMNS FROM users LIKE 'totp_secret'");
        if(mysqli_num_rows($result) == 0) {
         exit("totp_secret column does not exist. Please check your database schema.");
        }
        $result = mysqli_query($connection, "SHOW COLUMNS FROM users LIKE 'totp_enabled'");
        if(mysqli_num_rows($result) == 0) {
            exit("totp_enabled column does not exist. Please check your database schema.");
        }
        
        mysqli_close($connection);
    }

    //check_and_create_2fa_columns(); // Call the function to check and create columns if necessary
    check_and_create_2fa_columns();

    $error = '';
    $success = '';
    $step = isset($_GET['step']) ? $_GET['step'] : 'login';

    if(!($_SERVER['REQUEST_METHOD'] === 'POST')) {
    $error = 'Invalid request method.';
    $step = 'login';
    }


    if ($_POST['action'] === 'login') 
    {
    
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $connection = connect_to_db();
        $username = mysqli_real_escape_string($connection, $username); // SQL Injection Screening
    
        $sql = "SELECT * FROM users WHERE username='$username'";
        $result_2 = mysqli_query($connection, $sql);
        
        if (!($result_2 && mysqli_num_rows($result_2) > 0)) {  
            $error = 'Invalid username or password!';
            error_log("Invaild username  \n", 3, __DIR__ . '/debug.log');        
            mysqli_close($connection);
            header('Location: index.php?step=login&error=' . urlencode($error));
            exit;

        }
            
        $row = mysqli_fetch_assoc($result_2);
            
        if (!($password == $row["credentials"])) {
        $error = 'Invalid username or password!';
        error_log("Inviald password. \n", 3, __DIR__ . '/debug.log');
        mysqli_close($connection);
        header('Location: index.php?step=login&error=' . urlencode($error));    
        exit;
        } 
               
        $_SESSION['temp_user'] = $username;
        $_SESSION['user_id'] = $row['id'];
                
        // check if 2FA is enabled for the user
        if ($row['totp_enabled'] == 0 && empty($row['totp_secret'])) {
            error_log("2FA is not enabled for this user. \n", 3, __DIR__ . '/debug.log');
            mysqli_close($connection );
            $error = '2FA is not enabled for this user. Please contact the administrator.';
            header('Location: index.php?step=login&error=' . urlencode($error));
            exit;
        } 

        error_log("Step: 1 finished. \n", 3, __DIR__ . '/debug.log');
        mysqli_close($connection);
        header('Location: index.php?step=verify_2fa');
        exit;
            
    } else if ($_POST['action'] === 'verify_2fa') {

        if (!(isset($_SESSION['temp_user'])) && !(isset($_SESSION['user_id']))) {
            $error = 'Session expired. Please log in again.';
            error_log("Session expired for user: " . $_SESSION['temp_user'] . "\n", 3, __DIR__ . '/debug.log');
            session_unset();
            session_destroy();
            $step = 'login';
            header('Location: index.php?&error=' . urlencode($error));
            exit;
        } 

            $username = $_SESSION['temp_user'];
            $code = $_POST['code'] ?? '';
            
            $connection = connect_to_db();
            $username_escaped = mysqli_real_escape_string($connection, $username);
            
            $sql = "SELECT * FROM users WHERE username='$username_escaped'";
            $result_3 = mysqli_query($connection, $sql);
            
        if ($result_3 && mysqli_num_rows($result_3) > 0) 
        {
            error_log("Step: 2: 2FA Code verification for user: " . $username . "\n", 3, __DIR__ . '/debug.log');
            $row = mysqli_fetch_assoc($result_3);
            $secret = $row['totp_secret'];
            $totpClass = 'OTPHP\\TOTP';
            $otp = $totpClass::createFromSecret($secret);
                
            if ($otp->verify($code, null, 1)) 
                {
                    unset($_SESSION['temp_user']);
                    unset($_SESSION['user_id']);
                    session_regenerate_id(true);
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION["loginID"] = $row["loginID"];
                    $_SESSION['user_path'] = $row["userpath"];
                    
                    mysqli_close($connection);
                    header('Location: /services/filemanager_new/main.php');
                    exit;
                
                } else {
                    error_log("Invalid 2FA Code for user: " . $username . "\n", 3, __DIR__ . '/debug.log');
                    $step = 'verify_2fa';
                    error_log("Step: 2: Invalid 2FA Code for user: " . $username . "\n", 3, __DIR__ . '/debug.log');
                    //echo "<script>alert('Invalid 2FA Code. Please try again.'); window.location.href = '?step=verify_2fa';</script>";
                    $error = 'Invalid 2FA Code. Please try again.';
                    header('Location: index.php?step=verify_2fa&error=' . urlencode($error));
                    exit;
                }
        }

        mysqli_close($connection);

    } 
    else  
    {
        error_log("Invalid action.\n", 3, __DIR__ . '/debug.log');
        $error = 'Invalid action.';
        $step = 'login';
        header('Location: index.php?step=login&error=' . urlencode($error));
    }
                
 

?>



