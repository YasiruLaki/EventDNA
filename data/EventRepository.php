<?php
require_once __DIR__ . '/database.php';

class EventRepository {
    private $conn;

    // Registration statuses that hold a seat at the event
    const SEAT_STATUSES = "'PENDING','APPROVED','REGISTERED'";

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function createEvent($organizerId, $e) {
        $stmt = $this->conn->prepare("
            INSERT INTO events (organizer_id, name, description, cover_photo, event_date, start_time, end_time,
                                location, address, capacity, visibility, registration_open, registration_close)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "issssssssisss",
            $organizerId, $e['name'], $e['description'], $e['cover_photo'], $e['event_date'],
            $e['start_time'], $e['end_time'], $e['location'], $e['address'], $e['capacity'],
            $e['visibility'], $e['registration_open'], $e['registration_close']
        );

        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return false;
    }

    public function updateEvent($eventId, $e) {
        $stmt = $this->conn->prepare("
            UPDATE events
            SET name = ?, description = ?, cover_photo = ?, event_date = ?, start_time = ?, end_time = ?,
                location = ?, address = ?, capacity = ?, visibility = ?, registration_open = ?, registration_close = ?
            WHERE event_id = ?
        ");
        $stmt->bind_param(
            "ssssssssisssi",
            $e['name'], $e['description'], $e['cover_photo'], $e['event_date'], $e['start_time'],
            $e['end_time'], $e['location'], $e['address'], $e['capacity'], $e['visibility'],
            $e['registration_open'], $e['registration_close'], $eventId
        );
        return $stmt->execute();
    }

    public function cancelEvent($eventId) {
        $stmt = $this->conn->prepare("UPDATE events SET status = 'CANCELLED' WHERE event_id = ?");
        $stmt->bind_param("i", $eventId);
        return $stmt->execute();
    }

    public function getEventById($eventId) {
        $stmt = $this->conn->prepare("
            SELECT e.*,
                   (SELECT COUNT(*) FROM event_registrations r
                     WHERE r.event_id = e.event_id AND r.status IN (" . self::SEAT_STATUSES . ")) AS registered_count,
                   (SELECT COUNT(*) FROM attendance a
                     WHERE a.event_id = e.event_id AND a.checked_in = 1) AS checked_in_count
            FROM events e
            WHERE e.event_id = ?
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getEventsByOrganizer($organizerId) {
        $stmt = $this->conn->prepare("
            SELECT e.event_id, e.name, e.cover_photo, e.event_date, e.start_time, e.end_time, e.location, e.capacity,
                   e.visibility, e.status, e.registration_open, e.registration_close,
                   (SELECT COUNT(*) FROM event_registrations r
                     WHERE r.event_id = e.event_id AND r.status IN (" . self::SEAT_STATUSES . ")) AS registered_count,
                   (SELECT COUNT(*) FROM attendance a
                     WHERE a.event_id = e.event_id AND a.checked_in = 1) AS checked_in_count
            FROM events e
            WHERE e.organizer_id = ?
            ORDER BY e.event_date DESC, e.start_time DESC
        ");
        $stmt->bind_param("i", $organizerId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getInterests() {
        $result = $this->conn->query("SELECT interest_id, interest_name FROM interests WHERE status = 'ACTIVE' ORDER BY interest_name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getEventInterestIds($eventId) {
        $stmt = $this->conn->prepare("SELECT interest_id FROM event_interests WHERE event_id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'interest_id');
    }

    public function getEventInterestNames($eventId) {
        $stmt = $this->conn->prepare("
            SELECT i.interest_name FROM event_interests ei
            JOIN interests i ON i.interest_id = ei.interest_id
            WHERE ei.event_id = ?
            ORDER BY i.interest_name
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'interest_name');
    }

    public function setEventInterests($eventId, $interestIds) {
        $del = $this->conn->prepare("DELETE FROM event_interests WHERE event_id = ?");
        $del->bind_param("i", $eventId);
        if (!$del->execute()) {
            return false;
        }

        $ins = $this->conn->prepare("INSERT INTO event_interests (event_id, interest_id) VALUES (?, ?)");
        foreach ($interestIds as $interestId) {
            $ins->bind_param("ii", $eventId, $interestId);
            if (!$ins->execute()) {
                return false;
            }
        }
        return true;
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
