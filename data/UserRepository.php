<?php
require_once __DIR__ . '/database.php';

class UserRepository {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("
            SELECT u.user_id, u.password_hash, u.full_name, ur.role_id 
            FROM users u
            JOIN user_roles ur ON u.user_id = ur.user_id
            WHERE u.email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    public function createUser($fullName, $email, $passwordHash, $roleId) {
        $stmt = $this->conn->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullName, $email, $passwordHash);
        
        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            
            $roleStmt = $this->conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $roleStmt->bind_param("ii", $userId, $roleId);
            if ($roleStmt->execute()) {
                return $userId;
            }
        }
        return false;
    }

    public function createEmailVerificationToken($userId, $tokenHash) {
        $stmt = $this->conn->prepare("INSERT INTO email_verification_tokens (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))");
        $stmt->bind_param("is", $userId, $tokenHash);
        return $stmt->execute();
    }
    
    public function getEmailVerificationToken($tokenHash) {
        $stmt = $this->conn->prepare("SELECT user_id, expires_at, used_at FROM email_verification_tokens WHERE token_hash = ?");
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function markEmailAsVerified($userId, $tokenHash) {
        $stmt = $this->conn->prepare("UPDATE email_verification_tokens SET used_at = NOW() WHERE token_hash = ?");
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();

        $stmt2 = $this->conn->prepare("UPDATE users SET email_verified = 1 WHERE user_id = ?");
        $stmt2->bind_param("i", $userId);
        return $stmt2->execute();
    }
}
?>
