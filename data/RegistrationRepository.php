<?php
require_once __DIR__ . '/database.php';

class RegistrationRepository {
    private $conn;

    // Registration statuses that hold a seat at the event
    const SEAT_STATUSES = "'PENDING','APPROVED','REGISTERED'";

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Locks the event row until the transaction ends, so registrations for the same event run one at a time
    public function lockEvent($eventId) {
        $stmt = $this->conn->prepare("
            SELECT event_id, organizer_id, name, event_date, start_time, end_time, location, capacity,
                   visibility, status, registration_open, registration_close
            FROM events
            WHERE event_id = ?
            FOR UPDATE
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function countSeatsTaken($eventId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS taken FROM event_registrations
            WHERE event_id = ? AND status IN (" . self::SEAT_STATUSES . ")
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['taken'];
    }

    public function getRegistration($eventId, $userId) {
        $stmt = $this->conn->prepare("
            SELECT registration_id, event_id, user_id, status, registered_at, approved_at
            FROM event_registrations
            WHERE event_id = ? AND user_id = ?
            FOR UPDATE
        ");
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createRegistration($eventId, $userId, $status) {
        $stmt = $this->conn->prepare("INSERT INTO event_registrations (event_id, user_id, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $eventId, $userId, $status);
        return $stmt->execute();
    }

    // A user who cancelled earlier re-registers by reusing their row (event_id + user_id is unique)
    public function reactivateRegistration($registrationId, $status) {
        $stmt = $this->conn->prepare("
            UPDATE event_registrations
            SET status = ?, registered_at = CURRENT_TIMESTAMP, approved_at = NULL
            WHERE registration_id = ?
        ");
        $stmt->bind_param("si", $status, $registrationId);
        return $stmt->execute();
    }

    public function cancelRegistration($registrationId) {
        $stmt = $this->conn->prepare("UPDATE event_registrations SET status = 'CANCELLED' WHERE registration_id = ?");
        $stmt->bind_param("i", $registrationId);
        return $stmt->execute();
    }

    public function isCheckedIn($eventId, $userId) {
        $stmt = $this->conn->prepare("SELECT 1 FROM attendance WHERE event_id = ? AND user_id = ? AND checked_in = 1");
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        return (bool)$stmt->get_result()->fetch_row();
    }

    public function getUserContact($userId) {
        $stmt = $this->conn->prepare("SELECT user_id, full_name, email FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createNotification($userId, $type, $title, $message, $referenceType, $referenceId) {
        $stmt = $this->conn->prepare("
            INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issssi", $userId, $type, $title, $message, $referenceType, $referenceId);
        return $stmt->execute();
    }

    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollback() {
        $this->conn->rollback();
    }
}
?>
