<?php
require_once __DIR__ . '/EventRepository.php';

class RegistrationRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getRegistration($eventId, $userId) {
        $stmt = $this->conn->prepare("SELECT * FROM event_registrations WHERE event_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getRegistrationById($registrationId) {
        $stmt = $this->conn->prepare("SELECT * FROM event_registrations WHERE registration_id = ?");
        $stmt->bind_param("i", $registrationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function countRegistrationsByStatus($eventId) {
        $stmt = $this->conn->prepare("SELECT status, COUNT(*) AS total FROM event_registrations WHERE event_id = ? GROUP BY status");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $counts = ['PENDING' => 0, 'APPROVED' => 0, 'REGISTERED' => 0, 'REJECTED' => 0, 'REMOVED' => 0, 'CANCELLED' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    public function getEventAttendees($eventId) {
        $stmt = $this->conn->prepare("
            SELECT r.registration_id, r.status, r.registered_at, r.approved_at, u.user_id, u.full_name, u.email,
                   COALESCE(a.checked_in, 0) AS checked_in
            FROM event_registrations r
            JOIN users u ON r.user_id = u.user_id
            LEFT JOIN attendance a ON a.event_id = r.event_id AND a.user_id = r.user_id
            WHERE r.event_id = ? AND r.status <> 'CANCELLED'
            ORDER BY FIELD(r.status, 'PENDING', 'REGISTERED', 'APPROVED', 'REJECTED', 'REMOVED'), r.registered_at ASC
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Creates (or re-activates a cancelled) registration while holding a lock on the event row,
    // so two people cannot take the last seat at the same time.
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

            if ($existing && in_array($existing['status'], ['REJECTED', 'REMOVED'])) {
                $this->conn->rollback();
                return "BLOCKED";
            }
            if ($existing && $existing['status'] !== 'CANCELLED') {
                $this->conn->rollback();
                return "DUPLICATE";
            }

            $countStmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM event_registrations WHERE event_id = ? AND status IN (" . EventRepository::SEAT_STATUSES . ")");
            $countStmt->bind_param("i", $eventId);
            $countStmt->execute();
            if ((int) $countStmt->get_result()->fetch_assoc()['total'] >= (int) $event['capacity']) {
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

    // Moves a registration to a new status only if it is still in one of the expected statuses
    public function updateStatus($registrationId, $fromStatuses, $toStatus) {
        $placeholders = implode(',', array_fill(0, count($fromStatuses), '?'));
        $approvedAt = ($toStatus === 'APPROVED') ? ", approved_at = NOW()" : "";

        $stmt = $this->conn->prepare("UPDATE event_registrations SET status = ?" . $approvedAt . " WHERE registration_id = ? AND status IN ($placeholders)");
        $stmt->bind_param("si" . str_repeat("s", count($fromStatuses)), $toStatus, $registrationId, ...$fromStatuses);

        return $stmt->execute() && $stmt->affected_rows === 1;
    }
}
?>
