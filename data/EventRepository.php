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
                     WHERE a.event_id = e.event_id AND a.checked_in = 1) AS checked_in_count,
                   u.full_name AS organizer_name
            FROM events e
            JOIN users u ON u.user_id = e.organizer_id
            WHERE e.event_id = ?
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Active public events that have not ended yet, optionally filtered by a search term and interest
    public function getPublicUpcomingEvents($today, $now, $search = '', $interestId = 0) {
        $sql = "
            SELECT e.event_id, e.name, e.description, e.cover_photo, e.event_date, e.start_time, e.end_time,
                   e.location, e.capacity, e.visibility, e.status, e.registration_open, e.registration_close,
                   (SELECT COUNT(*) FROM event_registrations r
                     WHERE r.event_id = e.event_id AND r.status IN (" . self::SEAT_STATUSES . ")) AS registered_count,
                   (SELECT i.interest_name FROM event_interests ei
                     JOIN interests i ON i.interest_id = ei.interest_id
                     WHERE ei.event_id = e.event_id
                     ORDER BY i.interest_name LIMIT 1) AS primary_interest,
                   u.full_name AS organizer_name
            FROM events e
            JOIN users u ON u.user_id = e.organizer_id
            WHERE e.status = 'ACTIVE' AND e.visibility = 'PUBLIC'
              AND (e.event_date > ? OR (e.event_date = ? AND e.end_time > ?))
        ";
        $types = "sss";
        $params = [$today, $today, $now];

        if ($search !== '') {
            $sql .= " AND (e.name LIKE ? OR e.location LIKE ? OR e.description LIKE ? OR u.full_name LIKE ?)";
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $types .= "ssss";
            array_push($params, $like, $like, $like, $like);
        }
        if ($interestId > 0) {
            $sql .= " AND EXISTS (SELECT 1 FROM event_interests ei WHERE ei.event_id = e.event_id AND ei.interest_id = ?)";
            $types .= "i";
            $params[] = $interestId;
        }
        $sql .= " ORDER BY e.event_date ASC, e.start_time ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Events the user holds a seat for, with their registration status and check-in state
    public function getEventsByAttendee($userId) {
        $stmt = $this->conn->prepare("
            SELECT e.event_id, e.name, e.cover_photo, e.event_date, e.start_time, e.end_time, e.location,
                   e.capacity, e.status, e.registration_open, e.registration_close,
                   r.status AS registration_status,
                   COALESCE(a.checked_in, 0) AS checked_in,
                   (SELECT i.interest_name FROM event_interests ei
                     JOIN interests i ON i.interest_id = ei.interest_id
                     WHERE ei.event_id = e.event_id
                     ORDER BY i.interest_name LIMIT 1) AS primary_interest
            FROM event_registrations r
            JOIN events e ON e.event_id = r.event_id
            LEFT JOIN attendance a ON a.event_id = r.event_id AND a.user_id = r.user_id
            WHERE r.user_id = ? AND r.status IN (" . self::SEAT_STATUSES . ")
            ORDER BY e.event_date ASC, e.start_time ASC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Returns the user's registration status for an event, or null if they have none
    public function getRegistrationStatus($eventId, $userId) {
        $stmt = $this->conn->prepare("SELECT status FROM event_registrations WHERE event_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['status'] : null;
    }

    // Interests attached to at least one active public event, for the explore filter
    public function getPublicEventInterests() {
        $result = $this->conn->query("
            SELECT DISTINCT i.interest_id, i.interest_name
            FROM interests i
            JOIN event_interests ei ON ei.interest_id = i.interest_id
            JOIN events e ON e.event_id = ei.event_id
            WHERE e.status = 'ACTIVE' AND e.visibility = 'PUBLIC' AND i.status = 'ACTIVE'
            ORDER BY i.interest_name
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getEventsByOrganizer($organizerId) {
        $stmt = $this->conn->prepare("
            SELECT e.event_id, e.name, e.event_date, e.start_time, e.end_time, e.location, e.capacity,
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
