<?php
require_once __DIR__ . '/../../data/UserRepository.php';

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

        $created = $this->userRepo->createUser($fullName, $email, $passwordHash, $roleId);

        if ($created) {
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
}
?>
