<?php

class SettingsRepository {
    private $conn;

    const NOTIFICATION_COLUMNS = [
        'notify_in_app', 'notify_email', 'notify_event_updates',
        'notify_connection_requests', 'notify_connection_updates', 'notify_community_activity',
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Returns the user's saved preferences, or the table defaults if they haven't saved any.
    public function getSettings($userId) {
        $stmt = $this->conn->prepare("SELECT " . implode(', ', self::NOTIFICATION_COLUMNS) . ", profile_visibility FROM user_settings WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            return $row;
        }

        $defaults = array_fill_keys(self::NOTIFICATION_COLUMNS, 1);
        $defaults['profile_visibility'] = 'MEMBERS';
        return $defaults;
    }

    // $flags: [column => 0|1] for every column in NOTIFICATION_COLUMNS
    public function saveNotifications($userId, $flags) {
        $columns = self::NOTIFICATION_COLUMNS;
        $updates = implode(', ', array_map(fn($col) => "$col = VALUES($col)", $columns));
        $stmt = $this->conn->prepare("INSERT INTO user_settings (user_id, " . implode(', ', $columns) . ") VALUES (?" . str_repeat(', ?', count($columns)) . ") ON DUPLICATE KEY UPDATE $updates");

        $values = array_map(fn($col) => (int)$flags[$col], $columns);
        $stmt->bind_param("i" . str_repeat("i", count($columns)), $userId, ...$values);
        return $stmt->execute();
    }

    public function saveProfileVisibility($userId, $visibility) {
        $stmt = $this->conn->prepare("INSERT INTO user_settings (user_id, profile_visibility) VALUES (?, ?) ON DUPLICATE KEY UPDATE profile_visibility = VALUES(profile_visibility)");
        $stmt->bind_param("is", $userId, $visibility);
        return $stmt->execute();
    }
}
?>
