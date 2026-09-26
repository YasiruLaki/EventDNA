<?php
session_start();
require_once "../../../data/database.php";
require_once "../../../application/controllers/AuthController.php";

$error = "";
$successMessage = "";
$auth = new AuthController($conn);
$token = $_GET['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    
    $result = $auth->resetPassword($token, $password, $confirmPassword);
    
    if ($result["success"]) {
        $successMessage = "Your password has been successfully reset. You can now login.";
    } else {
        $error = $result["message"] ?? "An error occurred.";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password - EventDNA</title>
  <link rel="stylesheet" href="../../../globals.css" />
  <link rel="stylesheet" href="./styles.css" />
</head>

<body>
  <div class="auth-layout">
    <div class="auth-panel">
      <div class="register-card verify-card">
        <div class="logo-container">
           <img src="../../images/logo.png" alt="EventDNA" />
        </div>

        <h2>Reset Password</h2>
        
        <p class="subtitle">
          Please enter your new password below.
        </p>

        <?php if ($error): ?>
            <p style="color: var(--error); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <p style="color: var(--success); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($successMessage); ?></p>
            <a href="../login/index.php" class="btn-primary" style="width: 100%; margin-bottom: 1.5rem; justify-content: center;">Go to Login</a>
        <?php else: ?>
            <form action="" method="post" onsubmit="this.querySelector('button[type=submit]').classList.add('btn-loading');">
              <div class="input-group">
                <label for="password" class="input-label">New Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required class="email-input" />
              </div>
              
              <div class="input-group">
                <label for="confirmPassword" class="input-label">Confirm Password</label>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required class="email-input" />
              </div>

              <button type="submit" class="btn-primary" style="width: 100%; margin-bottom: 1.5rem;">
                <span class="btn-text">Reset Password</span>
                <span class="spinner"></span>
              </button>
            </form>
        <?php endif; ?>

        <div class="verify-actions">
          <a href="../login/index.php" class="btn-secondary" style="width: 100%;">&larr; Back to Login</a>
        </div>

        <div class="footer-links">
          <a href="#">Privacy Policy</a>
          <span>&bull;</span>
          <a href="#">Terms</a>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
