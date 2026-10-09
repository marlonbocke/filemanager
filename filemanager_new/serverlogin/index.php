<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

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

// Datenbank-Verbindung
function get_db_connection() {
    $verbindung = mysqli_connect("127.0.0.1", "admin", "M10%bock", "filemanager", 3306);
    
    if(!$verbindung) {
        die("Datenbankverbindung fehlgeschlagen: " . mysqli_connect_error());
    }
    
    return $verbindung;
}








// Prüfen ob die 2FA-Spalten existieren, wenn nicht, erstellen
function check_and_create_2fa_columns() {
    $verbindung = get_db_connection();
    
    // Prüfen ob totp_secret Spalte existiert
    $result = mysqli_query($verbindung, "SHOW COLUMNS FROM users LIKE 'totp_secret'");
    if(mysqli_num_rows($result) == 0) {
        mysqli_query($verbindung, "ALTER TABLE users ADD COLUMN totp_secret VARCHAR(255) NULL");
    }
    
    // Prüfen ob totp_enabled Spalte existiert
    $result = mysqli_query($verbindung, "SHOW COLUMNS FROM users LIKE 'totp_enabled'");
    if(mysqli_num_rows($result) == 0) {
        mysqli_query($verbindung, "ALTER TABLE users ADD COLUMN totp_enabled TINYINT(1) DEFAULT 0");
    }
    
    mysqli_close($verbindung);
}

// Beim ersten Aufruf Spalten erstellen
check_and_create_2fa_columns();

$error = '';
$success = '';
$step = isset($_GET['step']) ? $_GET['step'] : 'login';

// Login-Verarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'login') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $verbindung = get_db_connection();
        
        // SQL Injection Schutz
        $username = mysqli_real_escape_string($verbindung, $username);
        
        $sql = "SELECT * FROM users WHERE username='$username'";
        $ergebnis = mysqli_query($verbindung, $sql);
        
        if ($ergebnis && mysqli_num_rows($ergebnis) > 0) {
            $row = mysqli_fetch_assoc($ergebnis);
            
            // Passwort prüfen
            if ($password == $row["credentials"]) {
                $_SESSION['temp_user'] = $username;
                $_SESSION['user_id'] = $row['id'];
                
                // Prüfen ob 2FA aktiviert ist
                if ($row['totp_enabled'] == 1 && !empty($row['totp_secret'])) {
                    mysqli_close($verbindung);
                    header('Location: ?step=verify_2fa');
                    exit;
                } else {
                    // Kein 2FA -> 2FA-Setup anbieten
                    mysqli_close($verbindung);
                    header('Location: ?step=setup_2fa');
                    exit;
                }
            } else {
                $error = 'Ungültiger Benutzername oder Passwort!';
            }
        } else {
            $error = 'Ungültiger Benutzername oder Passwort!';
        }
        
        mysqli_close($verbindung);
    }
    
    elseif ($_POST['action'] === 'setup_2fa') {
        if (isset($_SESSION['temp_user'])) {
            $otp = TOTP::generate($clock, 32);
            $secret = $otp->getSecret();
            
            // Secret temporär in Session speichern
            $_SESSION['temp_secret'] = $secret;
            
            $otp->setLabel("WebApp - " . $_SESSION['temp_user']);
            $_SESSION['qr_code'] = $otp->getQrCodeUri(
                'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
                '[DATA]'
            );
            
            header('Location: ?step=confirm_2fa');
            exit;
        }
    }
    
    elseif ($_POST['action'] === 'confirm_2fa') {
        if (isset($_SESSION['temp_user']) && isset($_SESSION['temp_secret'])) {
            $code = $_POST['code'] ?? '';
            $username = $_SESSION['temp_user'];
            $secret = $_SESSION['temp_secret'];
            
            $otp = TOTP::createFromSecret($secret);
            
            if ($otp->verify($code, null, 1)) {
                // Secret dauerhaft in Datenbank speichern
                $verbindung = get_db_connection();
                $username_escaped = mysqli_real_escape_string($verbindung, $username);
                $secret_escaped = mysqli_real_escape_string($verbindung, $secret);
                
                $sql = "UPDATE users SET totp_secret='$secret_escaped', totp_enabled=1 WHERE username='$username_escaped'";
                
                if (mysqli_query($verbindung, $sql)) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $username;
                    
                    unset($_SESSION['temp_user']);
                    unset($_SESSION['temp_secret']);
                    unset($_SESSION['qr_code']);
                    
                    mysqli_close($verbindung);
                    
                    $success = '2FA erfolgreich aktiviert!';
                    header('Location: ?step=dashboard');
                    exit;
                } else {
                    $error = 'Fehler beim Speichern des 2FA-Secrets!';
                }
                
                mysqli_close($verbindung);
            } else {
                $error = 'Ungültiger Code! Bitte versuche es erneut.';
                $step = 'confirm_2fa';
            }
        }
    }
    
    elseif ($_POST['action'] === 'verify_2fa') {
        if (isset($_SESSION['temp_user'])) {
            $username = $_SESSION['temp_user'];
            $code = $_POST['code'] ?? '';
            
            $verbindung = get_db_connection();
            $username_escaped = mysqli_real_escape_string($verbindung, $username);
            
            $sql = "SELECT totp_secret FROM users WHERE username='$username_escaped'";
            $ergebnis = mysqli_query($verbindung, $sql);
            
            if ($ergebnis && mysqli_num_rows($ergebnis) > 0) {
                $row = mysqli_fetch_assoc($ergebnis);
                $secret = $row['totp_secret'];
                
                $otp = TOTP::createFromSecret($secret);
                
                if ($otp->verify($code, null, 1)) {
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = $username;
                    unset($_SESSION['temp_user']);
                    
                    mysqli_close($verbindung);
                    header('Location: ?step=dashboard');
                    exit;
                } else {
                    $error = 'Ungültiger 2FA-Code!';
                    $step = 'verify_2fa';
                }
            }
            
            mysqli_close($verbindung);
        }
    }
    
    elseif ($_POST['action'] === 'disable_2fa') {
        if (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
            
            $verbindung = get_db_connection();
            $username_escaped = mysqli_real_escape_string($verbindung, $username);
            
            $sql = "UPDATE users SET totp_secret=NULL, totp_enabled=0 WHERE username='$username_escaped'";
            
            if (mysqli_query($verbindung, $sql)) {
                $success = '2FA wurde deaktiviert!';
            } else {
                $error = 'Fehler beim Deaktivieren von 2FA!';
            }
            
            mysqli_close($verbindung);
        }
    }
    
    elseif ($_POST['action'] === 'skip_2fa') {
        if (isset($_SESSION['temp_user'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $_SESSION['temp_user'];
            unset($_SESSION['temp_user']);
            header('Location: ?step=dashboard');
            exit;
        }
    }
    
    elseif ($_POST['action'] === 'logout') {
        session_destroy();
        header('Location: ?step=login');
        exit;
    } elseif ($_POST['action'] === 'redirect_filemanager') {


    $sql = "SELECT  username, loginID FROM users WHERE username='" . mysqli_real_escape_string(get_db_connection(), $_SESSION['username']) . "'";
    $verbindung = get_db_connection();
    $ergebnis = mysqli_query($verbindung, $sql);
    $row = mysqli_fetch_assoc($ergebnis);
    $loginid = $row['loginID'];
    $auth = $row['username'] ;

    mysqli_close($verbindung);




        // $auth = "true";
        // $id = 52468445;

        // Der Browser wird automatisch zu Datei_B.php geschickt


        //header('Location: filemanager/main.php?status=' . $auth . '&id=' . $id);
        // header('Location: https://192.168.178.57/filemanager/main.php?status=' . $auth . '&id=' . $id);
        header('Location: https://ugreennas/filemanager/main.php?username=' . $auth . '&id=' . $loginid);
    //     echo '<script>
    //     window.open(
    //         "filemanager/main.php?status=' . $auth . '&id=' . $id . '",
    //         "FileManagerWindow",
    //         "width=1200,height=800,resizable=yes,scrollbars=yes"
    //     );
    // </script>';
        
        
        exit;
    }
}
?>



<?php
$key = $_GET['key'] ?? '';
// Prüfen ob eingeloggt
if ($step === 'dashboard' && !isset($_SESSION['logged_in'])) {
    header('Location: ?step=login');
    exit;
}

// Aktuelle Benutzerdaten für Dashboard laden
$user_data = null;
if ($step === 'dashboard' && isset($_SESSION['username'])) {
    $verbindung = get_db_connection();
    $username_escaped = mysqli_real_escape_string($verbindung, $_SESSION['username']);
    
    $sql = "SELECT * FROM users WHERE username='$username_escaped'";
    $ergebnis = mysqli_query($verbindung, $sql);
    
    if ($ergebnis && mysqli_num_rows($ergebnis) > 0) {
        $user_data = mysqli_fetch_assoc($ergebnis);
    }
    
    mysqli_close($verbindung);

    //header("Location: dashboard.html");




// } elseif ($step === 'dashboard' && $key !== 'dsfögfpgkfjf9rjfnvc8ekdnvudndlsuxmvinfhdfkdslsdirnwleonmcövofnm') {
//     // Ungültiger Schlüssel, zurück zum Login
//     header('Location: ?step=login');
//     exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Authentifizierung</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            
    
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            max-width: 500px;
            width: 100%;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #667eea;
            font-size: 2em;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 0.95em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            width: 100%;
            font-weight: 600;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            margin-top: 10px;
        }

        .btn-danger {
            background: #dc3545;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .qr-container {
            text-align: center;
            margin: 20px 0;
        }

        .qr-container img {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            max-width: 100%;
        }

        .secret-display {
            background: #f8f9ff;
            background: url("DJI_0566.jpeg") no-repeat center center fixed;
            padding: 15px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            text-align: center;
            color: #667eea;
            font-weight: bold;
            word-break: break-all;
            margin: 15px 0;
            border: 2px dashed #667eea;
        }

        .info-box {
            background: url("background.jpeg") no-repeat center center fixed;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 0.9em;
        }

        .info-box ul {
            margin-top: 10px;
            margin-left: 20px;
        }

        .dashboard-info {
            background: url("background.jpeg") no-repeat center center fixed;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .dashboard-info h3 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            margin-left: 10px;
        }

        .status-enabled {
            background: #d4edda;
            color: #155724;
        }

        .status-disabled {
            background: #f8d7da;
            color: #721c24;
        }

        .demo-notice {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .demo-notice strong {
            color: #856404;
        }

        .link-text {
            text-align: center;
            margin-top: 15px;
            color: #666;
        }

        .link-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .link-text a:hover {
            text-decoration: underline;
        }

        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            text-align: center;
            color: #856404;
        }

        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            
            <?php if ($step === 'login'): ?>
                <div class="header">
                    <h1>🔐 Login</h1>
                    <p>Melde dich mit deinem Account an</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="username">Benutzername:</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Passwort:</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn">Anmelden</button>
                </form>

                <p style="text-align: center; font-size: 0.9em; color: #666; margin-top: 20px;">© 2025 Ugreen NAS Server. Besitzer ist <a href="https://marlonbockelmann.ddns.net/aboutme/about-me.html" style="color: #667eea; text-decoration: none;">Marlon Bockelmann</a>.</p>

            <?php elseif ($step === 'setup_2fa'): ?>
                <div class="header">
                    <h1>🛡️ 2FA Einrichten</h1>
                    <p>Erhöhe die Sicherheit deines Accounts</p>
                </div>

                <div class="info-box">
                    <strong>ℹ️ Zwei-Faktor-Authentifizierung</strong><br>
                    Die 2FA bietet zusätzlichen Schutz für deinen Account. Du benötigst eine Authenticator-App wie:
                    <ul>
                        <li>Google Authenticator</li>
                        <li>Microsoft Authenticator</li>
                        <li>Authy</li>
                    </ul>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="setup_2fa">
                    <button type="submit" class="btn">2FA jetzt aktivieren</button>
                </form>

                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="action" value="skip_2fa">
                    <button type="submit" class="btn btn-secondary">Später einrichten</button>
                </form>

            <?php elseif ($step === 'confirm_2fa'): ?>
                <div class="header">
                    <h1>📱 QR-Code scannen</h1>
                    <p>Scanne den Code mit deiner Authenticator-App</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="qr-container">
                    <img src="<?php echo htmlspecialchars($_SESSION['qr_code']); ?>" alt="QR Code">
                </div>

                <div class="info-box">
                    <strong>Schritte:</strong><br>
                    1. Öffne deine Authenticator-App<br>
                    2. Wähle "Account hinzufügen"<br>
                    3. Scanne den QR-Code<br>
                    4. Gib den 6-stelligen Code unten ein
                </div>

                <div class="secret-display">
                    Manueller Key: <?php echo htmlspecialchars($_SESSION['temp_secret']); ?>
                </div>

                <div class="warning-box">
                    ⚠️ Wichtig: Nach der Bestätigung wird dieser Key sicher in deiner Datenbank gespeichert!
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="confirm_2fa">
                    
                    <div class="form-group">
                        <label for="code">6-stelliger Code:</label>
                        <input type="text" id="code" name="code" required maxlength="6" pattern="\d{6}" placeholder="123456" autofocus>
                    </div>

                    <button type="submit" class="btn">2FA aktivieren</button>
                </form>

            <?php elseif ($step === 'verify_2fa'): ?>
                <div class="header">
                    <h1>🔒 2FA-Verifizierung</h1>
                    <p>Gib deinen 6-stelligen Code ein</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="info-box">
                    Öffne deine Authenticator-App und gib den aktuellen 6-stelligen Code ein.
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="verify_2fa">
                    
                    <div class="form-group">
                        <label for="code">2FA-Code:</label>
                        <input type="text" id="code" name="code" required maxlength="6" pattern="\d{6}" placeholder="123456" autofocus>
                    </div>

                    <button type="submit" class="btn">Verifizieren</button>
                </form>

                <div class="link-text">
                    <a href="?step=login">Zurück zum Login</a>
                </div>

            <?php elseif ($step === 'dashboard'): ?>
                <div class="header">
                    <h1>✅ Dashboard</h1>
                    <p>Willkommen, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($user_data): ?>
                    <div class="dashboard-info">
                        <h3>Account-Informationen</h3>
                        <p><strong>Benutzername:</strong> <?php echo htmlspecialchars($user_data['username']); ?></p>
                        <p>
                            <strong>2FA-Status:</strong>
                            <?php if ($user_data['totp_enabled'] == 1): ?>
                                <span class="status-badge status-enabled">✓ Aktiviert</span>
                            <?php else: ?>
                                <span class="status-badge status-disabled">✗ Deaktiviert</span>
                            <?php endif; ?>
                        </p>
                    </div>


                     <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="action" value="redirect_filemanager">
                    <button type="submit" class="btn btn-secondary">Filemanager</button>
                    </form>           

                    <!-- <?php if ($user_data['totp_enabled'] == 1): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="disable_2fa">
                            <button type="submit" class="btn btn-danger">2FA deaktivieren</button>
                        </form>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="setup_2fa">
                            <button type="submit" class="btn">2FA aktivieren</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
 -->
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-secondary">Abmelden</button>
                </form>

            <?php endif; ?>

        </div>
    </div>
</body>
</html>