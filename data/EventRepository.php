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

    // Public-facing details of an event's organizer, from their user account and organizer profile
    public function getOrganizer($organizerId) {
        $stmt = $this->conn->prepare("
            SELECT u.user_id, u.full_name, p.organization, p.job_title, p.field, p.bio, p.profile_photo,
                   (SELECT COUNT(*) FROM events e WHERE e.organizer_id = u.user_id AND e.status != 'CANCELLED') AS events_count
            FROM users u
            LEFT JOIN profiles p ON p.user_id = u.user_id
            WHERE u.user_id = ?
        ");
        $stmt->bind_param("i", $organizerId);
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

    public function getUpcomingEvents() {
        $stmt = $this->conn->prepare("
            SELECT e.*,
                   (SELECT COUNT(*) FROM event_registrations r
                     WHERE r.event_id = e.event_id AND r.status IN (" . self::SEAT_STATUSES . ")) AS registered_count
            FROM events e
            WHERE e.status != 'CANCELLED'
              AND TIMESTAMP(e.event_date, e.end_time) > ?
            ORDER BY e.event_date ASC, e.start_time ASC
        ");
        if (!$stmt) {
            die('Error preparing getUpcomingEvents: ' . $this->conn->error);
        }
        // Hide events that have already ended, using PHP's clock so the cutoff matches the app's timezone
        $now = date('Y-m-d H:i:s');
        $stmt->bind_param("s", $now);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // The public event that hasn't finished yet with the most attendees, soonest first on a tie
    public function getMostAttendedUpcomingEvent() {
        $stmt = $this->conn->prepare("
            SELECT e.*,
                   (SELECT COUNT(*) FROM event_registrations r
                     WHERE r.event_id = e.event_id AND r.status IN (" . self::SEAT_STATUSES . ")) AS registered_count
            FROM events e
            WHERE e.status != 'CANCELLED' AND e.visibility = 'PUBLIC'
              AND TIMESTAMP(e.event_date, e.end_time) > ?
            ORDER BY registered_count DESC, e.event_date ASC, e.start_time ASC
            LIMIT 1
        ");
        if (!$stmt) {
            die('Error preparing getMostAttendedUpcomingEvent: ' . $this->conn->error);
        }
        // Compare against PHP's clock so the cutoff matches the app's timezone, not the database server's
        $now = date('Y-m-d H:i:s');
        $stmt->bind_param("s", $now);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getRegisteredEvents($userId) {
        $stmt = $this->conn->prepare("
            SELECT e.*, r.registration_id, r.status AS reg_status, r.registered_at AS reg_date
            FROM events e
            JOIN event_registrations r ON e.event_id = r.event_id
            WHERE r.user_id = ? AND r.status IN ('REGISTERED', 'APPROVED', 'CHECKED_IN', 'PENDING')
            ORDER BY e.event_date ASC, e.start_time ASC
        ");
        if (!$stmt) {
            die('Error preparing getRegisteredEvents: ' . $this->conn->error);
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRegistration($eventId, $userId) {
        $stmt = $this->conn->prepare("
            SELECT registration_id, status, receive_updates, registered_at, user_id
            FROM event_registrations
            WHERE event_id = ? AND user_id = ?
        ");
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getRegistrationById($registrationId) {
        $stmt = $this->conn->prepare("
            SELECT registration_id, event_id, user_id, status, receive_updates, registered_at
            FROM event_registrations
            WHERE registration_id = ?
        ");
        $stmt->bind_param("i", $registrationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function registerAttendee($eventId, $userId, $receiveUpdates) {
        $event = $this->getEventById($eventId);
        if (!$event) return false;

        $status = ($event['visibility'] === 'INVITE_ONLY') ? 'PENDING' : 'REGISTERED';

        $stmt = $this->conn->prepare("
            INSERT INTO event_registrations (event_id, user_id, status, receive_updates)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = ?, receive_updates = ?,
                                    registered_at = CURRENT_TIMESTAMP, approved_at = NULL
        ");
        $stmt->bind_param("iissis", $eventId, $userId, $status, $receiveUpdates, $status, $receiveUpdates);
        if ($stmt->execute()) {
            return $status;
        }
        return false;
    }

    public function cancelRegistration($eventId, $userId) {
        $stmt = $this->conn->prepare("
            DELETE FROM event_registrations
            WHERE event_id = ? AND user_id = ? AND status IN (" . self::SEAT_STATUSES . ")
        ");
        $stmt->bind_param("ii", $eventId, $userId);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    public function setReceiveUpdates($eventId, $userId, $receiveUpdates) {
        $stmt = $this->conn->prepare("
            UPDATE event_registrations SET receive_updates = ?
            WHERE event_id = ? AND user_id = ? AND status IN (" . self::SEAT_STATUSES . ")
        ");
        $stmt->bind_param("iii", $receiveUpdates, $eventId, $userId);
        return $stmt->execute();
    }

    public function getEventAttendees($eventId) {
        $stmt = $this->conn->prepare("
            SELECT u.user_id, u.full_name, u.email, r.status, r.registered_at, r.registration_id,
                   COALESCE(a.checked_in, 0) AS checked_in, a.checked_in_at
            FROM event_registrations r
            JOIN users u ON r.user_id = u.user_id
            LEFT JOIN attendance a ON a.event_id = r.event_id AND a.user_id = r.user_id
            WHERE r.event_id = ?
            ORDER BY r.registered_at DESC
        ");
        $stmt->bind_param("i", $eventId);
        if (!$stmt->execute()) return [];
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateRegistrationStatus($registrationId, $eventId, $status) {
        $stmt = $this->conn->prepare("
            UPDATE event_registrations 
            SET status = ?, approved_at = CASE WHEN ? IN ('APPROVED', 'REGISTERED') THEN CURRENT_TIMESTAMP ELSE approved_at END
            WHERE registration_id = ? AND event_id = ?
        ");
        $stmt->bind_param("ssii", $status, $status, $registrationId, $eventId);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
}
?>
