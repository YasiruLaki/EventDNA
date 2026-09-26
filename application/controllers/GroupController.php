<?php
require_once __DIR__ . '/../../data/GroupRepository.php';
require_once __DIR__ . '/../../data/UserRepository.php';

class GroupController {
    private $groupRepo;
    private $userRepo;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->groupRepo = new GroupRepository($conn);
        $this->userRepo = new UserRepository($conn);
    }

    private function isOrganizer($userId) {
        $stmt = $this->conn->prepare(
            "SELECT r.role_name FROM user_roles ur
             JOIN roles r ON r.role_id = ur.role_id
             WHERE ur.user_id = ? AND r.role_name = 'ORGANIZER'"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private function isAdmin($userId) {
        $stmt = $this->conn->prepare(
            "SELECT r.role_name FROM user_roles ur
             JOIN roles r ON r.role_id = ur.role_id
             WHERE ur.user_id = ? AND r.role_name = 'ADMINISTRATOR'"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    private function getUserFullName($userId) {
        $stmt = $this->conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? $res['full_name'] : "An organizer";
    }

    public function create($userId, $name, $description, $rawInterests) {
        if (!$this->isOrganizer($userId)) {
            return ["success" => false, "code" => "FORBIDDEN", "message" => "You do not have permission to create groups."];
        }

        if (empty($name)) {
            return ["success" => false, "code" => "VALIDATION_ERROR", "message" => "Group name is required."];
        }
        if (mb_strlen($name) > 200) {
            return ["success" => false, "code" => "VALIDATION_ERROR", "message" => "Group name is too long."];
        }
        if (mb_strlen($description) > 2000) {
            return ["success" => false, "code" => "VALIDATION_ERROR", "message" => "Description is too long."];
        }

        $existing = $this->groupRepo->getGroupByNameAndStatus($name, 'ACTIVE');
        if ($existing) {
            return ["success" => false, "code" => "DUPLICATE_NAME", "message" => "An active group with this name already exists."];
        }

        $interestIds = [];
        if (is_array($rawInterests)) {
            $interestIds = array_values(array_unique(array_map("intval", $rawInterests)));
        }

        if (count($interestIds) > 0) {
            $validIds = $this->groupRepo->getValidInterests($interestIds);
            if (count($validIds) !== count($interestIds)) {
                return ["success" => false, "code" => "INVALID_INTEREST", "message" => "One or more selected interests are invalid."];
            }
        }

        $organizerName = $this->getUserFullName($userId);
        $attendeesToNotify = $this->groupRepo->getOrganizerAttendees($userId);

        try {
            $groupId = $this->groupRepo->createGroup($userId, $name, $description, $interestIds, $organizerName, $attendeesToNotify);
            return [
                "success" => true,
                "code" => "GROUP_CREATED",
                "message" => "Group created successfully. You are now the owner of this group.",
                "data" => ["group_id" => $groupId]
            ];
        } catch (Exception $e) {
            return ["success" => false, "code" => "SERVER_ERROR", "message" => "The group could not be created. Please try again."];
        }
    }

    public function update($userId, $groupId, $name, $description, $rawInterests) {
        if ($groupId <= 0) {
            return ["success" => false, "code" => "VALIDATION_ERROR", "message" => "group_id is required."];
        }
        if (empty($name)) {
            return ["success" => false, "code" => "VALIDATION_ERROR", "message" => "Group name is required."];
        }
        if (mb_strlen($name) > 200) {
            return ["success" => false, "code" => "VALIDATION_ERROR", "message" => "Group name cannot exceed 200 characters."];
        }

        $group = $this->groupRepo->getGroupById($groupId);
        if (!$group) {
            return ["success" => false, "code" => "NOT_FOUND", "message" => "Group not found."];
        }

        if ((int)$group['creator_id'] !== $userId) {
            return ["success" => false, "code" => "FORBIDDEN", "message" => "You do not have permission to update this group."];
        }
        if ($group['status'] !== 'ACTIVE') {
            return ["success" => false, "code" => "GROUP_NOT_ACTIVE", "message" => "Archived groups cannot be updated."];
        }

        $existing = $this->groupRepo->getGroupByNameAndStatus($name, 'ACTIVE', $groupId);
        if ($existing) {
            return ["success" => false, "code" => "DUPLICATE_NAME", "message" => "An active group with this name already exists."];
        }

        $interestIds = [];
        if (is_array($rawInterests)) {
            $interestIds = array_values(array_unique(array_map("intval", $rawInterests)));
        }

        if (count($interestIds) > 0) {
            $validIds = $this->groupRepo->getValidInterests($interestIds);
            if (count($validIds) !== count($interestIds)) {
                return ["success" => false, "code" => "INVALID_INTEREST", "message" => "One or more interest IDs are invalid or inactive."];
            }
            $interestIds = $validIds;
        }

        try {
            $this->groupRepo->updateGroup($groupId, $name, $description, $interestIds);
            return [
                "success" => true,
                "code" => "GROUP_UPDATED",
                "message" => "Group updated successfully.",
                "data" => [
                    "group_id" => $groupId,
                    "name" => $name,
                    "description" => $description,
                    "interests" => $interestIds
                ]
            ];
        } catch (Exception $e) {
            return ["success" => false, "code" => "UPDATE_FAILED", "message" => $e->getMessage()];
        }
    }

    public function archive($userId, $groupId) {
        if ($groupId <= 0) {
            return ["success" => false, "code" => "VALIDATION_ERROR", "message" => "group_id is required."];
        }

        $group = $this->groupRepo->getGroupById($groupId);
        if (!$group) {
            return ["success" => false, "code" => "NOT_FOUND", "message" => "Group not found."];
        }
        if ($group['status'] === 'ARCHIVED') {
            return ["success" => false, "code" => "ALREADY_ARCHIVED", "message" => "Group is already archived."];
        }

        $isCreator = ((int)$group['creator_id'] === $userId);
        $isAdmin = $this->isAdmin($userId);

        if (!$isCreator && !$isAdmin) {
            return ["success" => false, "code" => "FORBIDDEN", "message" => "You cannot archive this group."];
        }

        $this->groupRepo->archiveGroup($groupId);
        return ["success" => true, "code" => "GROUP_ARCHIVED", "message" => "Group archived."];
    }

    public function getList($userId, $mineOnly, $searchQuery, $status) {
        $search = "%" . $searchQuery . "%";
        if ($mineOnly) {
            $groups = $this->groupRepo->getUserGroups($userId, $status, $search);
        } else {
            $groups = $this->groupRepo->getAllGroups($status, $search);
        }
        return [
            "success" => true,
            "code" => "GROUPS_FETCHED",
            "message" => "",
            "data" => $groups
        ];
    }

    public function getDetails($userId, $groupId) {
        if ($groupId <= 0) {
            return ["success" => false, "code" => "VALIDATION_ERROR", "message" => "group_id is required."];
        }

        $group = $this->groupRepo->getGroupById($groupId);
        if (!$group) {
            return ["success" => false, "code" => "NOT_FOUND", "message" => "Group not found."];
        }

        $interests = $this->groupRepo->getGroupInterests($groupId);
        $memberCount = $this->groupRepo->getActiveMemberCount($groupId);
        $membership = $this->groupRepo->getGroupMembership($groupId, $userId);

        $isMember = $membership && $membership['status'] === 'ACTIVE';
        $myRole = $isMember ? $membership['role'] : null;

        return [
            "success" => true,
            "code" => "GROUP_FETCHED",
            "message" => "",
            "data" => [
                "group" => $group,
                "interests" => $interests,
                "member_count" => $memberCount,
                "is_member" => $isMember,
                "my_role" => $myRole
            ]
        ];
    }

    public function join($userId, $groupId) {
        if ($groupId <= 0) {
            return ["success" => false, "code" => "VALIDATION_ERROR", "message" => "group_id is required."];
        }

        $group = $this->groupRepo->getGroupById($groupId);
        if (!$group || $group['status'] !== 'ACTIVE') {
            return ["success" => false, "code" => "NOT_FOUND", "message" => "Group not available."];
        }

        $existing = $this->groupRepo->getGroupMembership($groupId, $userId);
        if ($existing && $existing['status'] === 'ACTIVE') {
            return ["success" => false, "code" => "ALREADY_MEMBER", "message" => "You are already a member."];
        }

        if ($existing) {
            $this->groupRepo->setGroupMembership($groupId, $userId, 'MEMBER', 'ACTIVE', true);
        } else {
            $this->groupRepo->setGroupMembership($groupId, $userId, 'MEMBER', 'ACTIVE', false);
        }

        return ["success" => true, "code" => "GROUP_JOINED", "message" => "Joined group."];
    }
}
?>
