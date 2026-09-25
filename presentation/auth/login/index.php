<?php
session_start();
require_once "../../../data/database.php";
require_once "../../../application/controllers/AuthController.php";

$error = "";
$auth = new AuthController($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $roleName = $_POST["role"] ?? "attendee";
    $expectedRoleId = ($roleName === "organizer") ? 2 : 1;

    $result = $auth->login($email, $password, $expectedRoleId);

    if ($result["success"]) {
        if ($result["role_id"] == 2) {
            header("Location: ../../organizer/dashboard.html");
        } else {
            header("Location: ../../attendee/dashboard/index.html");
        }
        exit;
    } else {
        $error = $result["message"];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EventDNA</title>
    <link rel="stylesheet" href="../../../globals.css">
    <link rel="stylesheet" href="./styles.css">
</head>

<body>
    <div class="split-layout">
        <div class="left-section">
            <h1 class="hero-title">Meet Better.<br><span class="highlight">Network Smarter.</span></h1>
            <p class="hero-description">
                Connect, collaborate, and grow with EventDNA—an intelligent platform that makes professional networking
                at events smarter, easier, and more meaningful.
            </p>
        </div>
        <div class="right-section">
            <div class="register-card">
                <h2>Login</h2>
                <p class="subtitle">Welcome back to your networking journey.</p>

                <form action="" method="post">
                    <input type="hidden" name="role" id="roleInput" value="attendee">

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z">
                                </path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            <input type="email" id="email" name="email" placeholder="name@company.com" required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label for="password" style="margin-bottom: 0;">Password</label>
                            <a href="../forgot-password/index.html"
                                style="font-size: 0.8rem; color: var(--primary); text-decoration: none; font-weight: 600;">Forgot
                                Password?</a>
                        </div>
                        <div class="input-wrapper">
                            <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            <input type="password" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <p style="color: var(--error); margin-bottom: 1rem; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <button type="submit" class="btn-primary">
                        Login
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </button>
                </form>

                <p class="login-link">Don't have an account? <a href="../register/index.php">Register Here</a></p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <span class="dot">•</span>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const role = urlParams.get('role');
        if (role) {
            document.getElementById('roleInput').value = role;
            const registerLink = document.querySelector('.login-link a');
            if (registerLink) registerLink.href = `../register/index.php?role=${role}`;
        }
    </script>
</body>

</html>