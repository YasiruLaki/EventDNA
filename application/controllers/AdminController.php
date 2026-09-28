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
    public function suspendUser($userId, $durationDays, $reason) {
        if ($userId == $_SESSION['user_id']) {
            return ["success" => false, "message" => "Cannot suspend your own account."];
        }

        $this->conn->begin_transaction();
        try {
            // 1. Update user
            $stmt = $this->conn->prepare("UPDATE users SET account_status = 'SUSPENDED', suspended_at = NOW(), suspended_until = DATE_ADD(NOW(), INTERVAL ? DAY), suspension_reason = ? WHERE user_id = ?");
            $stmt->bind_param("isi", $durationDays, $reason, $userId);
            $stmt->execute();
            
            // Get exact suspension times for event overlap check
            $stmt = $this->conn->prepare("SELECT suspended_at, suspended_until FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $suspensionStart = $res['suspended_at'];
            $suspensionEnd = $res['suspended_until'];

            // 2. Find overlapping events
            $sql = "SELECT event_id, name FROM events 
                    WHERE organizer_id = ? 
                      AND status = 'ACTIVE' 
                      AND CONCAT(event_date, ' ', start_time) < ? 
                      AND CONCAT(event_date, ' ', end_time) > ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iss", $userId, $suspensionEnd, $suspensionStart);
            $stmt->execute();
            $eventsResult = $stmt->get_result();
            $affectedEvents = [];
            while ($row = $eventsResult->fetch_assoc()) {
                $affectedEvents[] = $row;
            }

            $cancellationReason = "Organizer account suspended during scheduled event period";
            $cancelledBy = "SYSTEM";
            $cancelledAt = date('Y-m-d H:i:s');
            
            foreach ($affectedEvents as $ev) {
                $eventId = $ev['event_id'];
                
                // Cancel event
                $stmt = $this->conn->prepare("UPDATE events SET status = 'CANCELLED', cancellation_reason = ?, cancelled_by = ?, cancelled_at = ? WHERE event_id = ?");
                $stmt->bind_param("sssi", $cancellationReason, $cancelledBy, $cancelledAt, $eventId);
                $stmt->execute();

                // Revoke QR tokens
                $stmt = $this->conn->prepare("UPDATE qr_tokens SET status = 'REVOKED' WHERE event_id = ? AND type = 'EVENT_CHECKIN'");
                $stmt->bind_param("i", $eventId);
                $stmt->execute();

                // Get attendees
                $stmt = $this->conn->prepare("SELECT user_id FROM event_registrations WHERE event_id = ? AND status IN ('REGISTERED', 'APPROVED')");
                $stmt->bind_param("i", $eventId);
                $stmt->execute();
                $attRes = $stmt->get_result();
                
                // Notify attendees
                $notifySql = "INSERT INTO notifications (user_id, title, message, type, related_id) VALUES (?, ?, ?, 'EVENT_CANCELLED', ?)";
                $notifyStmt = $this->conn->prepare($notifySql);
                $title = "Event Cancelled";
                $message = "The event '" . $ev['name'] . "' has been cancelled by EventDNA.";
                
                while ($att = $attRes->fetch_assoc()) {
                    $notifyStmt->bind_param("issi", $att['user_id'], $title, $message, $eventId);
                    $notifyStmt->execute();
                }
            }

            $this->conn->commit();
            return ["success" => true, "message" => "User suspended successfully.", "cancelled_events" => count($affectedEvents)];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Failed to suspend user: " . $e->getMessage()];
        }
    }

    public function reactivateUser($userId) {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("UPDATE users SET account_status = 'ACTIVE', suspended_at = NULL, suspended_until = NULL, suspension_reason = NULL WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            $this->conn->commit();
            return ["success" => true, "message" => "User reactivated successfully."];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Failed to reactivate user."];
        }
    }
    public function disableUser($userId) {
        if ($userId == $_SESSION['user_id']) {
            return ["success" => false, "message" => "Cannot disable your own account."];
        }

        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("UPDATE users SET account_status = 'DISABLED', suspended_at = NULL, suspended_until = NULL, suspension_reason = NULL WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            
            $this->conn->commit();
            return ["success" => true, "message" => "User disabled successfully."];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Failed to disable user."];
        }
    }
    public function cancelEvent($eventId, $reason) {
        $this->conn->begin_transaction();
        try {
            // Get event details
            $stmt = $this->conn->prepare("SELECT name, status FROM events WHERE event_id = ?");
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $ev = $stmt->get_result()->fetch_assoc();
            
            if (!$ev || $ev['status'] === 'CANCELLED') {
                return ["success" => false, "message" => "Event not found or already cancelled."];
            }

            $cancelledBy = "SYSTEM";
            $cancelledAt = date('Y-m-d H:i:s');
            
            // Cancel event
            $stmt = $this->conn->prepare("UPDATE events SET status = 'CANCELLED', cancellation_reason = ?, cancelled_by = ?, cancelled_at = ? WHERE event_id = ?");
            $stmt->bind_param("sssi", $reason, $cancelledBy, $cancelledAt, $eventId);
            $stmt->execute();

            // Revoke QR tokens
            $stmt = $this->conn->prepare("UPDATE qr_tokens SET status = 'REVOKED' WHERE event_id = ? AND type = 'EVENT_CHECKIN'");
            $stmt->bind_param("i", $eventId);
            $stmt->execute();

            // Get attendees to notify
            $stmt = $this->conn->prepare("SELECT user_id FROM event_registrations WHERE event_id = ? AND status IN ('REGISTERED', 'APPROVED')");
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $attRes = $stmt->get_result();
            
            // Notify attendees
            $notifySql = "INSERT INTO notifications (user_id, title, message, type, related_id) VALUES (?, ?, ?, 'EVENT_CANCELLED', ?)";
            $notifyStmt = $this->conn->prepare($notifySql);
            $title = "Event Cancelled";
            $message = "The event '" . $ev['name'] . "' has been cancelled by an administrator. Reason: " . $reason;
            
            while ($att = $attRes->fetch_assoc()) {
                $notifyStmt->bind_param("issi", $att['user_id'], $title, $message, $eventId);
                $notifyStmt->execute();
            }

            $this->conn->commit();
            return ["success" => true, "message" => "Event cancelled successfully."];
        } catch (Exception $e) {
            $this->conn->rollback();
            return ["success" => false, "message" => "Failed to cancel event: " . $e->getMessage()];
        }
    }

    public function getAllEvents($search = '', $statusFilter = '') {
        $sql = "SELECT e.event_id, e.name, e.event_date, e.start_time, e.status, e.cancellation_reason, u.full_name as organizer_name 
                FROM events e 
                JOIN users u ON e.organizer_id = u.user_id 
                WHERE 1=1";
        $params = [];
        $types = "";

        if ($search) {
            $sql .= " AND (e.name LIKE ? OR u.full_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $types .= "ss";
        }
        if ($statusFilter) {
            $sql .= " AND e.status = ?";
            $params[] = strtoupper($statusFilter);
            $types .= "s";
        }

        $sql .= " ORDER BY e.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
