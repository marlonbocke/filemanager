<!-- //developed with AI assistance -->
<?php
session_start();

if(!(isset($step))){
  $step = $_GET['step'] ?? 'login';  
} 

?>

<!doctype html>
<html lang="de">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>" />
    <title>2FA Authentifizierung</title>
  </head>


  <body>

    <?php if($_GET['error']): ?>
    <script>alert('<?php echo htmlspecialchars($_GET['error']); ?>');</script>   
    <?php endif; ?>


    <div class="container">
     <div class="card">

     <?php if ($step === 'login'): ?>
        <div class="header">
          <h1>🔐 Login</h1>
          <p>Please enter your credentials</p>
        </div>

        <form method="POST" action="2FA-Verification.php">
          <input type="hidden" name="action" value="login" />
          <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required />
          </div>
          <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required />
          </div>
          <button type="submit" class="btn">Login</button>
        </form>

    
      <?php elseif ($step === 'verify_2fa'): ?>
        <div class="header">
          <h1>🔒 2FA-Verification</h1>
          <p>Enter your 6-digit code</p>
        </div>

        <div class="info-box">
          Open your Authenticator app and enter the current 6-digit code.
        </div>

        <form method="POST" action="2FA-Verification.php">
          <input type="hidden" name="action" value="verify_2fa" />

          <div class="form-group">
            <label for="code">2FA-Code:</label>
            
            <input
              type="text"
              id="code"
              name="code"
              required
              maxlength="6"
              pattern="\d{6}"
              placeholder="123456"
              autofocus
            />  
          </div>
          <button type="submit" class="btn">Verify</button>
        </form>
        <div class="link-text">
          <a href="?step=login">Back to Login</a>
        </div>
      <?php endif; ?>
        <p
          style="
            text-align: center;
            font-size: 0.9em;
            color: #666;
            margin-top: 20px;
          "
        >
          © 2026 Ugreen NAS Server. Owned and maintained by Marlon Bockelmann.
          <a
            href="/aboutme/about-me.html"
            style="color: #667eea; text-decoration: none"
            >Marlon Bockelmann</a
          >.
        </p>
       </div>
    </div>
  </body>
</html>
