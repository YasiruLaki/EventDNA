<?php
require_once __DIR__ . '/database.php';

class GroupRepository {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getGroupByNameAndStatus($name, $status, $excludeGroupId = null) {
        if ($excludeGroupId) {
            $stmt = $this->conn->prepare("SELECT group_id FROM groups WHERE name = ? AND status = ? AND group_id != ?");
            $stmt->bind_param("ssi", $name, $status, $excludeGroupId);
        } else {
            $stmt = $this->conn->prepare("SELECT group_id FROM groups WHERE name = ? AND status = ?");
            $stmt->bind_param("ss", $name, $status);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function getValidInterests($interestIds) {
        if (empty($interestIds)) return [];
        $placeholders = implode(",", array_fill(0, count($interestIds), "?"));
        $types = str_repeat("i", count($interestIds));
        
        $stmt = $this->conn->prepare("SELECT interest_id FROM interests WHERE status = 'ACTIVE' AND interest_id IN ($placeholders)");
        $stmt->bind_param($types, ...$interestIds);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $validIds = [];
        foreach ($rows as $row) {
            $validIds[] = (int) $row['interest_id'];
        }
        return $validIds;
    }

    public function getOrganizerAttendees($organizerId) {
        $stmt = $this->conn->prepare(
            "SELECT DISTINCT er.user_id
             FROM event_registrations er
             JOIN events e ON e.event_id = er.event_id
             WHERE e.organizer_id = ?
               AND er.status IN ('REGISTERED','APPROVED')
               AND er.user_id != ?"
        );
        $stmt->bind_param("ii", $organizerId, $organizerId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createGroup($creatorId, $name, $description, $interestIds, $organizerName, $attendeesToNotify) {
        $this->conn->begin_transaction();
        try {
            $stmt = $this->conn->prepare("INSERT INTO groups (creator_id, name, description, status) VALUES (?, ?, ?, 'ACTIVE')");
            $stmt->bind_param("iss", $creatorId, $name, $description);
            $stmt->execute();
            $groupId = $this->conn->insert_id;

            $memberStmt = $this->conn->prepare("INSERT INTO group_members (group_id, user_id, role, status) VALUES (?, ?, 'OWNER', 'ACTIVE')");
            $memberStmt->bind_param("ii", $groupId, $creatorId);
            $memberStmt->execute();

            if (!empty($interestIds)) {
                $interestStmt = $this->conn->prepare("INSERT INTO group_interests (group_id, interest_id) VALUES (?, ?)");
                foreach ($interestIds as $interest_id) {
                    $interestStmt->bind_param("ii", $groupId, $interest_id);
                    $interestStmt->execute();
                }
            }

            if (!empty($attendeesToNotify)) {
                $notifyStmt = $this->conn->prepare(
                    "INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id, is_read)
                     VALUES (?, 'NEW_GROUP', 'New Community Group', ?, 'group', ?, 0)"
                );
                $message = "$organizerName created a new group: \"$name\". Explore the group to join.";
                foreach ($attendeesToNotify as $attendee) {
                    $attendee_id = (int) $attendee["user_id"];
                    $notifyStmt->bind_param("isi", $attendee_id, $message, $groupId);
                    $notifyStmt->execute();
                }
            }

            $this->conn->commit();
            return $groupId;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getGroupById($groupId) {
        $stmt = $this->conn->prepare("SELECT group_id, creator_id, name, description, status, created_at FROM groups WHERE group_id = ?");
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    public function updateGroup($groupId, $name, $description, $interestIds) {
        $this->conn->begin_transaction();
        try {
            $updateStmt = $this->conn->prepare("UPDATE groups SET name = ?, description = ? WHERE group_id = ?");
            $updateStmt->bind_param("ssi", $name, $description, $groupId);
            $updateStmt->execute();

            $deleteInterestStmt = $this->conn->prepare("DELETE FROM group_interests WHERE group_id = ?");
            $deleteInterestStmt->bind_param("i", $groupId);
            $deleteInterestStmt->execute();

            if (!empty($interestIds)) {
                $insertInterestStmt = $this->conn->prepare("INSERT INTO group_interests (group_id, interest_id) VALUES (?, ?)");
                foreach ($interestIds as $interest_id) {
                    $insertInterestStmt->bind_param("ii", $groupId, $interest_id);
                    $insertInterestStmt->execute();
                }
            }
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function archiveGroup($groupId) {
        $stmt = $this->conn->prepare("UPDATE groups SET status = 'ARCHIVED' WHERE group_id = ?");
        $stmt->bind_param("i", $groupId);
        return $stmt->execute();
    }

    public function getUserGroups($userId, $status, $search) {
        $sql = "SELECT g.group_id, g.name, g.description, g.creator_id, g.created_at,
                       (SELECT COUNT(*) FROM group_members gm2 WHERE gm2.group_id = g.group_id AND gm2.status = 'ACTIVE') AS member_count
                FROM groups g
                JOIN group_members gm ON gm.group_id = g.group_id
                WHERE g.status = ? AND gm.user_id = ? AND gm.status = 'ACTIVE' AND g.name LIKE ?
                ORDER BY g.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sis", $status, $userId, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllGroups($status, $search) {
        $sql = "SELECT g.group_id, g.name, g.description, g.creator_id, g.created_at,
                       (SELECT COUNT(*) FROM group_members gm2 WHERE gm2.group_id = g.group_id AND gm2.status = 'ACTIVE') AS member_count
                FROM groups g
                WHERE g.status = ? AND g.name LIKE ?
                ORDER BY g.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $status, $search);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getGroupInterests($groupId) {
        $stmt = $this->conn->prepare(
            "SELECT i.interest_id, i.interest_name FROM group_interests gi
             JOIN interests i ON i.interest_id = gi.interest_id
             WHERE gi.group_id = ?"
        );
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getActiveMemberCount($groupId) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS member_count FROM group_members WHERE group_id = ? AND status = 'ACTIVE'");
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()["member_count"];
    }

    public function getGroupMembership($groupId, $userId) {
        $stmt = $this->conn->prepare("SELECT role, status FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $groupId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function setGroupMembership($groupId, $userId, $role, $status, $isUpdate) {
        if ($isUpdate) {
            $stmt = $this->conn->prepare("UPDATE group_members SET status = ? WHERE group_id = ? AND user_id = ?");
            $stmt->bind_param("sii", $status, $groupId, $userId);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO group_members (group_id, user_id, role, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $groupId, $userId, $role, $status);
        }
        return $stmt->execute();
    }
}
?>
