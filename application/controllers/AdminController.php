<?php
class AdminController {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllUsers($search = '', $roleFilter = '', $statusFilter = '') {
        $sql = "SELECT u.user_id, u.full_name, u.email, u.created_at, r.role_name, r.role_id,
                       u.account_status as status
                FROM users u
                JOIN user_roles ur ON u.user_id = ur.user_id
                JOIN roles r ON r.role_id = ur.role_id
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if ($search) {
            $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }

        if ($roleFilter) {
            $sql .= " AND r.role_name = ?";
            $params[] = $roleFilter;
            $types .= "s";
        }

        if ($statusFilter) {
            $sql .= " AND u.account_status = ?";
            $params[] = strtoupper($statusFilter);
            $types .= "s";
        }

        $sql .= " ORDER BY u.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getUser($id) {
        $stmt = $this->conn->prepare("SELECT u.user_id, u.full_name, u.email, ur.role_id FROM users u JOIN user_roles ur ON u.user_id = ur.user_id WHERE u.user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    public function createUser($fullName, $email, $password, $roleId) {
        // Check if email exists
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ["success" => false, "message" => "Email already exists."];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("INSERT INTO users (full_name, email, password_hash, email_verified) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("sss", $fullName, $email, $hash);
            $stmt->execute();
            $userId = $this->conn->insert_id;

            $stmt = $this->conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $roleId);
            $stmt->execute();

            $this->conn->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Failed to create user."];
        }
    }

    public function updateUser($userId, $fullName, $email, $roleId, $accountStatus, $password = '') {
        // Check email uniqueness
        $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ["success" => false, "message" => "Email already in use."];
        }

        $this->conn->begin_transaction();
        try {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->conn->prepare("UPDATE users SET full_name = ?, email = ?, password_hash = ?, account_status = ? WHERE user_id = ?");
                $stmt->bind_param("ssssi", $fullName, $email, $hash, $accountStatus, $userId);
            } else {
                $stmt = $this->conn->prepare("UPDATE users SET full_name = ?, email = ?, account_status = ? WHERE user_id = ?");
                $stmt->bind_param("sssi", $fullName, $email, $accountStatus, $userId);
            }
            $stmt->execute();

            $stmt = $this->conn->prepare("UPDATE user_roles SET role_id = ? WHERE user_id = ?");
            $stmt->bind_param("ii", $roleId, $userId);
            $stmt->execute();

            $this->conn->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Failed to update user."];
        }
    }

    public function deleteUser($userId) {
        if ($userId == $_SESSION['user_id']) {
            return ["success" => false, "message" => "Cannot delete your own account."];
        }

        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            $this->conn->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Failed to delete user."];
        }
    }
}
?>
