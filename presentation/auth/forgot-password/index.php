<?php
session_start();
require_once "../../../data/database.php";
require_once "../../../application/controllers/AuthController.php";

$error = "";
$successMessage = "";
$auth = new AuthController($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $result = $auth->forgotPassword($email);
    
    if ($result["success"]) {
        $successMessage = "If the email is registered, a password reset link has been sent.";
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
  <title>Forgot Password - EventDNA</title>
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

        <h2>Forgot Password</h2>
        
        <p class="subtitle">
          Enter your email address and we'll send you a link to reset your password.
        </p>

        <?php if ($error): ?>
            <p style="color: var(--error); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <p style="color: var(--success); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>

        <form action="" method="post" onsubmit="this.querySelector('button[type=submit]').classList.add('btn-loading');">
          <div class="input-group">
            <label for="email" class="input-label">Email Address</label>
            <input type="email" id="email" name="email" placeholder="name@company.com" required class="email-input" />
          </div>

          <button type="submit" class="btn-primary" style="width: 100%; margin-bottom: 1.5rem;">
            <span class="btn-text">Send Reset Link &rarr;</span>
            <span class="spinner"></span>
          </button>
        </form>

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
