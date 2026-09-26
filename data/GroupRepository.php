<?php
class GroupRepository {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createGroup($creatorId, $name, $description, $interests) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("INSERT INTO groups (creator_id, name, description, status) VALUES (?, ?, ?, 'ACTIVE')");
            $stmt->bind_param("iss", $creatorId, $name, $description);
            $stmt->execute();
            $groupId = $this->conn->insert_id;
            $stmt->close();

            $stmt = $this->conn->prepare("INSERT INTO group_members (group_id, user_id, role, status) VALUES (?, ?, 'OWNER', 'ACTIVE')");
            $stmt->bind_param("ii", $groupId, $creatorId);
            $stmt->execute();
            $stmt->close();

            if (!empty($interests)) {
                $stmt = $this->conn->prepare("INSERT INTO group_interests (group_id, interest_id) VALUES (?, ?)");
                foreach ($interests as $interestId) {
                    $stmt->bind_param("ii", $groupId, $interestId);
                    $stmt->execute();
                }
                $stmt->close();
            }

            $this->conn->commit();
            return $groupId;
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getGroupById($groupId) {
        $stmt = $this->conn->prepare("
            SELECT g.*, 
                   (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.group_id AND gm.status = 'ACTIVE') as member_count
            FROM groups g 
            WHERE g.group_id = ? AND g.status = 'ACTIVE'
        ");
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $group = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($group) {
            $stmt = $this->conn->prepare("
                SELECT i.interest_id, i.interest_name as name 
                FROM group_interests gi 
                JOIN interests i ON gi.interest_id = i.interest_id 
                WHERE gi.group_id = ?
            ");
            $stmt->bind_param("i", $groupId);
            $stmt->execute();
            $result = $stmt->get_result();
            $interests = [];
            while ($row = $result->fetch_assoc()) {
                $interests[] = $row;
            }
            $stmt->close();
            $group['interests'] = $interests;
        }

        return $group;
    }

    public function updateGroup($groupId, $name, $description, $interests) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("UPDATE groups SET name = ?, description = ? WHERE group_id = ?");
            $stmt->bind_param("ssi", $name, $description, $groupId);
            $stmt->execute();
            $stmt->close();

            $stmt = $this->conn->prepare("DELETE FROM group_interests WHERE group_id = ?");
            $stmt->bind_param("i", $groupId);
            $stmt->execute();
            $stmt->close();

            if (!empty($interests)) {
                $stmt = $this->conn->prepare("INSERT INTO group_interests (group_id, interest_id) VALUES (?, ?)");
                foreach ($interests as $interestId) {
                    $stmt->bind_param("ii", $groupId, $interestId);
                    $stmt->execute();
                }
                $stmt->close();
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
        $stmt->execute();
        $stmt->close();
    }

    public function getActiveGroups($search = '') {
        $query = "
            SELECT g.group_id, g.name, g.description, g.created_at,
                   COUNT(gm.user_id) as member_count
            FROM groups g
            LEFT JOIN group_members gm ON g.group_id = gm.group_id AND gm.status = 'ACTIVE'
            WHERE g.status = 'ACTIVE'
        ";
        if ($search) {
            $query .= " AND (g.name LIKE ? OR g.description LIKE ?)";
        }
        $query .= " GROUP BY g.group_id ORDER BY g.created_at DESC";

        $stmt = $this->conn->prepare($query);
        if ($search) {
            $term = "%" . $search . "%";
            $stmt->bind_param("ss", $term, $term);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row;
        }
        $stmt->close();

        // Fetch interests for all these groups
        if (!empty($groups)) {
            $groupIds = array_column($groups, 'group_id');
            $placeholders = str_repeat('?,', count($groupIds) - 1) . '?';
            $stmt = $this->conn->prepare("
                SELECT gi.group_id, i.interest_id, i.interest_name as name 
                FROM group_interests gi 
                JOIN interests i ON gi.interest_id = i.interest_id 
                WHERE gi.group_id IN ($placeholders)
            ");
            $stmt->bind_param(str_repeat('i', count($groupIds)), ...$groupIds);
            $stmt->execute();
            $res = $stmt->get_result();
            $allInterests = [];
            while ($row = $res->fetch_assoc()) {
                $allInterests[$row['group_id']][] = $row;
            }
            $stmt->close();

            foreach ($groups as &$g) {
                $g['interests'] = $allInterests[$g['group_id']] ?? [];
            }
        }
        return $groups;
    }

    public function getGroupsByOrganizer($creatorId) {
        $stmt = $this->conn->prepare("
            SELECT g.group_id, g.name, g.description, g.created_at, g.status,
                   (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id = g.group_id AND gm.status = 'ACTIVE') as member_count
            FROM groups g
            WHERE g.creator_id = ? AND g.status = 'ACTIVE'
            ORDER BY g.created_at DESC
        ");
        $stmt->bind_param("i", $creatorId);
        $stmt->execute();
        $result = $stmt->get_result();
        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row;
        }
        $stmt->close();
        return $groups;
    }

    public function getMyJoinedGroups($userId) {
        $stmt = $this->conn->prepare("
            SELECT g.group_id, g.name, g.description, g.created_at, gm.role
            FROM groups g
            JOIN group_members gm ON g.group_id = gm.group_id
            WHERE gm.user_id = ? AND gm.status = 'ACTIVE' AND g.status = 'ACTIVE'
            ORDER BY gm.joined_at DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row;
        }
        $stmt->close();
        return $groups;
    }

    public function joinGroup($groupId, $userId) {
        $stmt = $this->conn->prepare("INSERT INTO group_members (group_id, user_id, role, status) VALUES (?, ?, 'MEMBER', 'ACTIVE') ON DUPLICATE KEY UPDATE status = 'ACTIVE'");
        $stmt->bind_param("ii", $groupId, $userId);
        $stmt->execute();
        $stmt->close();
    }

    public function leaveGroup($groupId, $userId) {
        $stmt = $this->conn->prepare("UPDATE group_members SET status = 'REMOVED' WHERE group_id = ? AND user_id = ? AND role != 'OWNER'");
        $stmt->bind_param("ii", $groupId, $userId);
        $stmt->execute();
        $stmt->close();
    }

    public function isMember($groupId, $userId) {
        $stmt = $this->conn->prepare("SELECT role, status FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $groupId, $userId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res && $res['status'] === 'ACTIVE' ? $res : false;
    }
}
?>