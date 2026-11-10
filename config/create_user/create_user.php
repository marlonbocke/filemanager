<?php
// is still in development



// session_start();
// error_log("--------------------2FA Setup----------------" . date('d.m.Y H:i:s') . "\n", 3, __DIR__ . '/debug.log');
// require_once __DIR__ . '/../vendor/autoload.php';
// require_once __DIR__ . '/../db_connections.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// use OTPHP\TOTP;
// use Psr\Clock\ClockInterface;

// // PSR-20 Clock Implementierung
// class MyClock implements ClockInterface {
//     public function now(): \DateTimeImmutable {
//         return new \DateTimeImmutable();
//     }
// }

// $clock = new MyClock();


// // Datenbank-Verbindung



// // Prüfen ob die 2FA-Spalten existieren, wenn nicht, erstellen
// function check_2fa_columns() {
   
//     $connection = connect_to_db(); // Use the connection from db_connections.php
//     // Prüfen ob totp_secret Spalte existiert
//    $result = mysqli_query($connection, "SHOW COLUMNS FROM users LIKE 'totp_secret'");
//   error_log("result: " . mysqli_num_rows($result) . "\n", 3, __DIR__ . '/debug.log');
   

//     if(mysqli_num_rows($result) == 0) {
//         mysqli_query($connection, "ALTER TABLE users ADD COLUMN totp_secret VARCHAR(255) NULL");
//     }
//     $result = mysqli_query($connection, "SHOW COLUMNS FROM users LIKE 'totp_enabled'");
//     if(mysqli_num_rows($result) == 0) {
//         mysqli_query($connection, "ALTER TABLE users ADD COLUMN totp_enabled TINYINT(1) DEFAULT 0");
//     }
    
//     mysqli_close($connection);
// }

// // Beim ersten Aufruf Spalten erstellen
// check_and_create_2fa_columns();



//     elseif ($_POST['action'] === 'setup_2fa') {
//         if (isset($_SESSION['temp_user'])) {
//             $otp = TOTP::generate($clock, 32);
//             $secret = $otp->getSecret();
            
//             // Secret temporär in Session speichern
//             $_SESSION['temp_secret'] = $secret;
            
//             $otp->setLabel("WebApp - " . $_SESSION['temp_user']);
//             $_SESSION['qr_code'] = $otp->getQrCodeUri(
//                 'https://api.qrserver.com/v1/create-qr-code/?data=[DATA]&size=300x300&ecc=M',
//                 '[DATA]'
//             );
            
//             header('Location: ?step=confirm_2fa');
//             exit;
//         }
//     }
    
//     elseif ($_POST['action'] === 'confirm_2fa') {
//         if (isset($_SESSION['temp_user']) && isset($_SESSION['temp_secret'])) {
//             $code = $_POST['code'] ?? '';
//             $username = $_SESSION['temp_user'];
//             $secret = $_SESSION['temp_secret'];
            
//             $otp = TOTP::createFromSecret($secret);
            
//             if ($otp->verify($code, null, 1)) {
//                 // Secret dauerhaft in Datenbank speichern
//                 $connection = connect_to_db();
//                 $username_escaped = mysqli_real_escape_string($connection, $username);
//                 $secret_escaped = mysqli_real_escape_string($connection, $secret);
                
//                 $sql = "UPDATE users SET totp_secret='$secret_escaped', totp_enabled=1 WHERE username='$username_escaped'";
                
//                 if (mysqli_query($connection, $sql)) {
//                     $_SESSION['logged_in'] = true;
//                     $_SESSION['username'] = $username;
                    
//                     unset($_SESSION['temp_user']);
//                     unset($_SESSION['temp_secret']);
//                     unset($_SESSION['qr_code']);
                    
//                     mysqli_close($connection);
                    
//                     $success = '2FA erfolgreich aktiviert!';
//                     header('Location: ?step=dashboard');
//                     exit;
//                 } else {
//                     $error = 'Fehler beim Speichern des 2FA-Secrets!';
//                 }
                
//                 mysqli_close($connection);




//             } else {
//                 $error = 'Ungültiger Code! Bitte versuche es erneut.';
//                 $step = 'confirm_2fa';
//             }
//         }
//     }

?>

<!-- <?php if ($step === 'setup_2fa'): ?>
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
                </form> -->
<?php endif; ?>