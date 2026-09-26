<?php
require_once __DIR__ . '/../../data/UserRepository.php';

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AuthController {
    private $userRepo;

    public function __construct($conn) {
        $this->userRepo = new UserRepository($conn);
    }

    public function register($fullName, $email, $password, $confirmPassword, $roleName) {
        if (empty($fullName) || empty($email) || empty($password)) {
            return ["success" => false, "message" => "All fields are required."];
        }
        if ($password !== $confirmPassword) {
            return ["success" => false, "message" => "Passwords do not match."];
        }

        $existingUser = $this->userRepo->getUserByEmail($email);
        if ($existingUser) {
            return ["success" => false, "message" => "Email is already registered."];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $roleId = ($roleName === "organizer") ? 2 : 1;

        $createdUserId = $this->userRepo->createUser($fullName, $email, $passwordHash, $roleId);

        if ($createdUserId) {
            $code = sprintf("%06d", mt_rand(1, 999999));
            $tokenHash = hash('sha256', $code);
            $this->userRepo->createEmailVerificationToken($createdUserId, $tokenHash);

            $_SESSION['pending_user_id'] = $createdUserId;
            $_SESSION['pending_user_email'] = $email;
            $_SESSION['pending_role'] = $roleName;
            $_SESSION['pending_full_name'] = $fullName;

            $subject = 'Verify Your Email - EventDNA';
            $body = "Hi $fullName,<br><br>Welcome to EventDNA! Your 6-digit verification code is: <strong>$code</strong><br><br>Enter this code on the verification page.";

            $this->sendEmail($email, $fullName, $subject, $body);

            return ["success" => true];
        } else {
            return ["success" => false, "message" => "Registration failed. Please try again."];
        }
    }

    public function login($email, $password, $expectedRoleId) {
        if (empty($email) || empty($password)) {
            return ["success" => false, "message" => "Please enter both email and password."];
        }

        $user = $this->userRepo->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['role_id'] != $expectedRoleId) {
                return ["success" => false, "message" => "Invalid role for this account."];
            }
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['full_name'] = $user['full_name'];
            
            return ["success" => true, "role_id" => $user['role_id']];
        }
        
        return ["success" => false, "message" => "Invalid email or password."];
    }

    public function verifyEmail($userId, $code) {
        if (empty($code)) {
            return ["success" => false, "message" => "Please enter the verification code."];
        }

        $tokenHash = hash('sha256', $code);
        $tokenRow = $this->userRepo->getEmailVerificationToken($tokenHash);

        if (!$tokenRow || $tokenRow['user_id'] != $userId) {
            return ["success" => false, "message" => "Invalid verification code."];
        }

        if ($tokenRow['used_at'] !== null) {
            return ["success" => false, "message" => "Code has already been used."];
        }

        if (strtotime($tokenRow['expires_at']) < time()) {
            return ["success" => false, "message" => "Code has expired."];
        }

        $this->userRepo->markEmailAsVerified($userId, $tokenHash);
        
        return ["success" => true];
    }

    public function resendVerificationCode($userId, $email) {
        $code = sprintf("%06d", mt_rand(1, 999999));
        $tokenHash = hash('sha256', $code);
        $this->userRepo->createEmailVerificationToken($userId, $tokenHash);

        $subject = 'New Verification Code - EventDNA';
        $body = "Hi,<br><br>Here is your new 6-digit verification code: <strong>$code</strong><br><br>Enter this code on the verification page.";

        if ($this->sendEmail($email, '', $subject, $body)) {
            return ["success" => true];
        } else {
            return ["success" => false, "message" => "Could not send the email."];
        }
    }

    private function sendEmail($toEmail, $toName, $subject, $body) {
        $env = parse_ini_file(__DIR__ . '/../../.env');
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $env['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $env['MAIL_USERNAME'] ?? '';
            $mail->Password = $env['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $env['MAIL_PORT'] ?? 587;

            $fromEmail = $env['MAIL_FROM_ADDRESS'] ?? $env['MAIL_USERNAME'];
            $fromName = $env['MAIL_FROM_NAME'] ?? 'EventDNA';
            
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
