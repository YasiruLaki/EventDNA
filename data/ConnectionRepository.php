<?php
class ConnectionRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getConnections($userId) {
        $stmt = $this->conn->prepare("
            SELECT c.*, u.user_id as profile_id, u.name, p.headline as role, p.company, p.profile_picture 
            FROM connections c
            JOIN users u ON (c.user1_id = u.user_id OR c.user2_id = u.user_id) AND u.user_id != ?
            LEFT JOIN profiles p ON u.user_id = p.user_id
            WHERE (c.user1_id = ? OR c.user2_id = ?) AND c.status = 'ACCEPTED'
            ORDER BY c.responded_at DESC
        ");
        $stmt->bind_param("iii", $userId, $userId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getReceivedRequests($userId) {
        $stmt = $this->conn->prepare("
            SELECT c.*, u.user_id as profile_id, u.name, p.headline as role, p.company, p.profile_picture 
            FROM connections c
            JOIN users u ON c.user1_id = u.user_id
            LEFT JOIN profiles p ON u.user_id = p.user_id
            WHERE c.user2_id = ? AND c.status = 'PENDING'
            ORDER BY c.requested_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getSentRequests($userId) {
        $stmt = $this->conn->prepare("
            SELECT c.*, u.user_id as profile_id, u.name, p.headline as role, p.company, p.profile_picture 
            FROM connections c
            JOIN users u ON c.user2_id = u.user_id
            LEFT JOIN profiles p ON u.user_id = p.user_id
            WHERE c.user1_id = ? AND c.status = 'PENDING'
            ORDER BY c.requested_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getStats($userId) {
        $stats = ['total' => 0, 'pending' => 0];
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM connections WHERE (user1_id = ? OR user2_id = ?) AND status = 'ACCEPTED'");
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $stmt->bind_result($stats['total']);
        $stmt->fetch();
        $stmt->close();

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM connections WHERE user2_id = ? AND status = 'PENDING'");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($stats['pending']);
        $stmt->fetch();
        $stmt->close();
        return $stats;
    }
}
?>
