<?php
session_start();
require_once "../../data/database.php";
require_once "../../application/controllers/AuthController.php";

if (isset($_SESSION['user_id']) && (int)($_SESSION['role_id'] ?? 0) === 3) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $authController = new AuthController($conn);
    // Role 3 is ADMINISTRATOR
    $result = $authController->login($email, $password, 3);
    
    if ($result['success']) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - EventDNA</title>
    <link rel="stylesheet" href="../../globals.css">
    <link rel="stylesheet" href="../auth/login/styles.css">
</head>
<body>
    <div class="split-layout" style="justify-content: center; align-items: center;">
        <div class="register-card" style="margin: 0 auto;">
            <div class="logo" style="text-align: center; margin-bottom: 2rem;">
                <img src="../images/logo.png" alt="EventDNA" style="width: 180px; margin: 0 auto;" />
                <span style="display: block; font-size: 0.85rem; font-weight: 800; color: var(--primary); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 0.5rem; text-align: center;">Admin Portal</span>
            </div>
            
            <h2 style="text-align: center;">Welcome Back</h2>
            <p class="subtitle" style="text-align: center;">Enter your credentials to access the dashboard.</p>
            
            <?php if ($error): ?>
                <p class="error-msg" style="color: var(--error); margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem; background: rgba(220,38,38,0.1); padding: 0.75rem; border-radius: 6px; font-weight: 600;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            
            <form action="" method="post">
                <div class="form-group">
                    <label for="email">Admin Email</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                            </path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <input type="email" id="email" name="email" required placeholder="admin@eventdna.com" />
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <input type="password" id="password" name="password" required placeholder="••••••••" />
                    </div>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem; padding: 1rem;">Log In as Admin</button>
            </form>
        </div>
    </div>
</body>
</html>
