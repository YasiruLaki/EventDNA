<?php
require_once __DIR__ . '/../../data/GroupRepository.php';

class GroupController {
    private $repo;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->repo = new GroupRepository($conn);
    }

    public function createGroup($creatorId, $data) {
        $errors = $this->validateGroupData($data);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $groupId = $this->repo->createGroup($creatorId, trim($data['name']), trim($data['description']), $data['interests'] ?? []);
        return ['success' => true, 'group_id' => $groupId];
    }

    public function updateGroup($groupId, $creatorId, $data) {
        $group = $this->repo->getGroupById($groupId);
        if (!$group || (int)$group['creator_id'] !== (int)$creatorId) {
            return ['success' => false, 'errors' => ['Unauthorized or group not found']];
        }

        $errors = $this->validateGroupData($data);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $this->repo->updateGroup($groupId, trim($data['name']), trim($data['description']), $data['interests'] ?? []);
        return ['success' => true];
    }

    private function validateGroupData($data) {
        $errors = [];
        if (empty(trim($data['name']))) $errors[] = "Group name is required.";
        elseif (strlen(trim($data['name'])) > 150) $errors[] = "Group name must be under 150 characters.";
        
        if (empty(trim($data['description']))) $errors[] = "Description is required.";
        
        if (empty($data['interests']) || count($data['interests']) < 1) {
            $errors[] = "At least one interest tag is required.";
        }
        return $errors;
    }

    public function getGroupById($id) { return $this->repo->getGroupById($id); }
    public function getActiveGroups($search = '') { return $this->repo->getActiveGroups($search); }
    public function getGroupsByOrganizer($creatorId) { return $this->repo->getGroupsByOrganizer($creatorId); }
    public function getMyJoinedGroups($userId) { return $this->repo->getMyJoinedGroups($userId); }
    public function archiveGroup($groupId) { $this->repo->archiveGroup($groupId); }
    public function joinGroup($groupId, $userId) { $this->repo->joinGroup($groupId, $userId); }
    public function leaveGroup($groupId, $userId) { $this->repo->leaveGroup($groupId, $userId); }
    public function isMember($groupId, $userId) { return $this->repo->isMember($groupId, $userId); }

    public function getInterests() {
        $res = $this->conn->query("SELECT interest_id, interest_name as name FROM interests ORDER BY interest_name");
        $interests = [];
        while ($row = $res->fetch_assoc()) $interests[] = $row;
        return $interests;
    }
}
?>