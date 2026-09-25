<?php

class RegistrationRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getEventById($eventId) {
        $stmt = $this->conn->prepare("
            SELECT e.*, u.full_name AS organizer_name
            FROM events e
            JOIN users u ON e.organizer_id = u.user_id
            WHERE e.event_id = ?
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getEventInterests($eventId) {
        $stmt = $this->conn->prepare("
            SELECT i.interest_name
            FROM event_interests ei
            JOIN interests i ON ei.interest_id = i.interest_id
            WHERE ei.event_id = ?
            ORDER BY i.interest_name ASC
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRegistration($eventId, $userId) {
        $stmt = $this->conn->prepare("SELECT * FROM event_registrations WHERE event_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function countActiveRegistrations($eventId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM event_registrations WHERE event_id = ? AND status IN ('REGISTERED', 'APPROVED')");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function createRegistration($eventId, $userId, $status) {
        $this->conn->begin_transaction();
        try {
            $lockStmt = $this->conn->prepare("SELECT capacity FROM events WHERE event_id = ? FOR UPDATE");
            $lockStmt->bind_param("i", $eventId);
            $lockStmt->execute();
            $event = $lockStmt->get_result()->fetch_assoc();

            $existingStmt = $this->conn->prepare("SELECT registration_id, status FROM event_registrations WHERE event_id = ? AND user_id = ? FOR UPDATE");
            $existingStmt->bind_param("ii", $eventId, $userId);
            $existingStmt->execute();
            $existing = $existingStmt->get_result()->fetch_assoc();

            if ($existing && $existing['status'] !== 'CANCELLED') {
                $this->conn->rollback();
                return "DUPLICATE";
            }

            if ($status === 'REGISTERED' && $this->countActiveRegistrations($eventId) >= $event['capacity']) {
                $this->conn->rollback();
                return "FULL";
            }

            if ($existing) {
                $stmt = $this->conn->prepare("UPDATE event_registrations SET status = ?, registered_at = NOW(), approved_at = NULL WHERE registration_id = ?");
                $stmt->bind_param("si", $status, $existing['registration_id']);
            } else {
                $stmt = $this->conn->prepare("INSERT INTO event_registrations (event_id, user_id, status) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $eventId, $userId, $status);
            }

            if (!$stmt->execute()) {
                $this->conn->rollback();
                return "ERROR";
            }

            $this->conn->commit();
            return "OK";
        } catch (Exception $e) {
            $this->conn->rollback();
            return "ERROR";
        }
    }

    public function getRegistrationById($registrationId) {
        $stmt = $this->conn->prepare("SELECT * FROM event_registrations WHERE registration_id = ?");
        $stmt->bind_param("i", $registrationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateStatus($registrationId, $status) {
        if ($status === 'APPROVED') {
            $stmt = $this->conn->prepare("UPDATE event_registrations SET status = ?, approved_at = NOW() WHERE registration_id = ?");
        } else {
            $stmt = $this->conn->prepare("UPDATE event_registrations SET status = ? WHERE registration_id = ?");
        }
        $stmt->bind_param("si", $status, $registrationId);
        return $stmt->execute();
    }
}
?>
