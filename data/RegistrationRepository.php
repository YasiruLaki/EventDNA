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
}
?>
