<?php
session_start();
require_once "../../../data/database.php";
require_once "../../../application/controllers/AuthController.php";

$error = "";
$auth = new AuthController($conn);

$pendingEmail = $_SESSION['pending_user_email'];
$pendingUserId = $_SESSION['pending_user_id'] ?? null;
$pendingRole = $_SESSION['pending_role'] ?? "attendee";

if (!$pendingUserId) {
    header("Location: ../login/index.php");
    exit;
}

$successMessage = "";
if (isset($_GET['action']) && $_GET['action'] === 'resend') {
    $resendResult = $auth->resendVerificationCode($pendingUserId, $pendingEmail);
    if ($resendResult["success"]) {
        $successMessage = "A new verification code has been sent to your email.";
    } else {
        $error = $resendResult["message"];
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code = "";
    for ($i = 1; $i <= 6; $i++) {
        $code .= $_POST["code_$i"] ?? "";
    }
    
    $result = $auth->verifyEmail($pendingUserId, $code);
    
    if ($result["success"]) {
        $_SESSION['user_id'] = $pendingUserId;
        $_SESSION['role_id'] = ($pendingRole === 'organizer') ? 2 : 1;
        
        unset($_SESSION['pending_user_id']);
        unset($_SESSION['pending_user_email']);
        unset($_SESSION['pending_role']);
        
        if ($pendingRole === 'organizer') {
            header("Location: ../../organizer/dashboard.html");
        } else {
            header("Location: ../../attendee/onboarding/index.html");
        }
        exit;
    } else {
        $error = $result["message"];
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Onboarding Step 1 — Email Verification</title>
  <link rel="stylesheet" href="../../../globals.css" />
  <link rel="stylesheet" href="./styles.css" />
</head>

<body>
  <div class="auth-layout">
    <div class="auth-panel">
      <div class="register-card verify-card">

        <h2>Verify Your Email</h2>
        <p class="subtitle">
          We've sent a 6-digit verification code to<br>
          <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($pendingEmail); ?></strong>
        </p>

        <?php if ($error): ?>
            <p style="color: var(--error); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <?php if ($successMessage): ?>
            <p style="color: var(--success); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <div class="code-row" aria-label="Verification code input">
              <input class="code-box" name="code_1" maxlength="1" inputmode="numeric" autocomplete="one-time-code" autofocus required />
              <input class="code-box" name="code_2" maxlength="1" inputmode="numeric" required />
              <input class="code-box" name="code_3" maxlength="1" inputmode="numeric" required />
              <input class="code-box" name="code_4" maxlength="1" inputmode="numeric" required />
              <input class="code-box" name="code_5" maxlength="1" inputmode="numeric" required />
              <input class="code-box" name="code_6" maxlength="1" inputmode="numeric" required />
            </div>

            <button type="submit" class="btn-primary" id="verifyBtn" style="width: 100%;">
                <?php echo ($pendingRole === 'organizer') ? 'Continue to Organizer Portal →' : 'Verify &amp; Continue →'; ?>
            </button>
        </form>

        <div class="verify-actions">
          <a class="btn-secondary" href="?action=resend">Resend Code</a>
          <a class="btn-secondary" href="../register/index.php">Change Email</a>
        </div>

      </div>
    </div>
  </div>

  <script>
    const boxes = document.querySelectorAll('.code-box');

    boxes.forEach((box, index) => {
      box.addEventListener('input', () => {
        box.value = box.value.replace(/[^0-9]/g, '').slice(0, 1);
        if (box.value && boxes[index + 1]) {
          boxes[index + 1].focus();
        }
      });

      box.addEventListener('keydown', (event) => {
        if (event.key === 'Backspace' && !box.value && boxes[index - 1]) {
          boxes[index - 1].focus();
        }
      });
    });
  </script>
</body>

</html>