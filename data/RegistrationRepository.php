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

    public function getEventOwner($eventId) {
        $stmt = $this->conn->prepare("SELECT event_id, organizer_id FROM events WHERE event_id = ?");
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

    // Organizer view: every registration for the event with the attendee's check-in state from the attendance table.
    // $filter is one of the keys in attendeeFilterSql(); $search matches name or email.
    public function getEventRegistrations($eventId, $search = '', $filter = 'all') {
        $sql = "
            SELECT r.registration_id, r.user_id, r.status, r.registered_at, r.approved_at,
                   u.full_name, u.email,
                   COALESCE(a.checked_in, 0) AS checked_in, a.checked_in_at
            FROM event_registrations r
            JOIN users u ON u.user_id = r.user_id
            LEFT JOIN attendance a ON a.event_id = r.event_id AND a.user_id = r.user_id
            WHERE r.event_id = ?" . $this->attendeeFilterSql($filter);
        $types = "i";
        $params = [$eventId];

        $search = trim($search);
        if ($search !== '') {
            $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
            $like = '%' . addcslashes($search, '\\%_') . '%';
            $types .= "ss";
            $params[] = $like;
            $params[] = $like;
        }
        $sql .= " ORDER BY FIELD(r.status, 'PENDING', 'APPROVED', 'REGISTERED', 'REJECTED', 'REMOVED', 'CANCELLED'),
                           u.full_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Number of registrations in each filter, for the filter labels
    public function countEventRegistrationsByFilter($eventId) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS all_count,
                   SUM(r.status = 'PENDING') AS pending,
                   SUM(r.status IN ('APPROVED','REGISTERED')) AS registered,
                   SUM(COALESCE(a.checked_in, 0) = 1) AS checked_in,
                   SUM(r.status = 'REJECTED') AS rejected,
                   SUM(r.status = 'REMOVED') AS removed,
                   SUM(r.status = 'CANCELLED') AS cancelled
            FROM event_registrations r
            LEFT JOIN attendance a ON a.event_id = r.event_id AND a.user_id = r.user_id
            WHERE r.event_id = ?
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $row['all'] = $row['all_count'];
        unset($row['all_count']);
        return array_map('intval', $row);
    }

    private function attendeeFilterSql($filter) {
        $filters = [
            'pending' => " AND r.status = 'PENDING'",
            'registered' => " AND r.status IN ('APPROVED','REGISTERED')",
            'checked_in' => " AND a.checked_in = 1",
            'rejected' => " AND r.status = 'REJECTED'",
            'removed' => " AND r.status = 'REMOVED'",
            'cancelled' => " AND r.status = 'CANCELLED'",
        ];
        return $filters[$filter] ?? '';
    }

    // Locks one registration, but only if it belongs to the given event
    public function getRegistrationForEvent($registrationId, $eventId) {
        $stmt = $this->conn->prepare("
            SELECT registration_id, event_id, user_id, status
            FROM event_registrations
            WHERE registration_id = ? AND event_id = ?
            FOR UPDATE
        ");
        $stmt->bind_param("ii", $registrationId, $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateRegistrationStatus($registrationId, $status) {
        $stmt = $this->conn->prepare("
            UPDATE event_registrations
            SET status = ?, approved_at = IF(? = 'APPROVED', CURRENT_TIMESTAMP, approved_at)
            WHERE registration_id = ?
        ");
        $stmt->bind_param("ssi", $status, $status, $registrationId);
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
