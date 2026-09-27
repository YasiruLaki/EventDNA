<?php
require_once __DIR__ . '/EventRepository.php';

class NotificationRepository {
    private $conn;

    const EVENT_CANCELLED = 'EVENT_CANCELLED';

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Sends the same notification to everyone holding a seat at the event
    public function notifyEventRegistrants($eventId, $type, $title, $message) {
        $stmt = $this->conn->prepare("
            INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id)
            SELECT user_id, ?, ?, ?, 'event', event_id
            FROM event_registrations
            WHERE event_id = ? AND status IN (" . EventRepository::SEAT_STATUSES . ")
        ");
        $stmt->bind_param("sssi", $type, $title, $message, $eventId);
        return $stmt->execute();
    }

    public function getForUser($userId, $limit = 50) {
        $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, notification_id DESC LIMIT ?");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countUnread($userId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS n FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_assoc()['n'];
    }

    public function getLatestUnread($userId, $type) {
        $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND type = ? AND is_read = 0 ORDER BY created_at DESC, notification_id DESC LIMIT 1");
        $stmt->bind_param("is", $userId, $type);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function markAllRead($userId) {
        $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
}
?>
