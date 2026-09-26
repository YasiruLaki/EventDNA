<?php
require_once __DIR__ . '/database.php';

// qr_tokens stores only the SHA-256 hash of each token. The raw token exists only inside the QR link.
class QrTokenRepository {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Locks the event row so two regenerate clicks cannot leave two active QR codes
    public function lockEvent($eventId) {
        $stmt = $this->conn->prepare("
            SELECT event_id, organizer_id, name, event_date, start_time, end_time, status
            FROM events
            WHERE event_id = ?
            FOR UPDATE
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function revokeActiveEventTokens($eventId) {
        $stmt = $this->conn->prepare("
            UPDATE qr_tokens SET status = 'REVOKED'
            WHERE event_id = ? AND type = 'EVENT_CHECKIN' AND status = 'ACTIVE'
        ");
        $stmt->bind_param("i", $eventId);
        return $stmt->execute();
    }

    public function createEventToken($eventId, $tokenHash, $expiresAt) {
        $stmt = $this->conn->prepare("
            INSERT INTO qr_tokens (event_id, token_hash, type, expires_at, status)
            VALUES (?, ?, 'EVENT_CHECKIN', ?, 'ACTIVE')
        ");
        $stmt->bind_param("iss", $eventId, $tokenHash, $expiresAt);
        return $stmt->execute();
    }

    // The event's current check-in QR, or null if none has been generated (or it was revoked)
    public function getActiveEventToken($eventId) {
        $stmt = $this->conn->prepare("
            SELECT qr_id, event_id, token_hash, expires_at, created_at
            FROM qr_tokens
            WHERE event_id = ? AND type = 'EVENT_CHECKIN' AND status = 'ACTIVE'
            ORDER BY qr_id DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // For check-in: looks up an active, unexpired event QR by the hash of the scanned token
    public function findValidEventToken($tokenHash, $now) {
        $stmt = $this->conn->prepare("
            SELECT qr_id, event_id, expires_at, created_at
            FROM qr_tokens
            WHERE token_hash = ? AND type = 'EVENT_CHECKIN' AND status = 'ACTIVE'
              AND (expires_at IS NULL OR expires_at > ?)
        ");
        $stmt->bind_param("ss", $tokenHash, $now);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
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
