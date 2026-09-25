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
            return $roleStmt->execute();
        }
        return false;
    }
}
?>
