<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../data/database.php";
require_once "../../application/controllers/GroupController.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = (int)($_POST['group_id'] ?? 0);
    $organizerId = (int)$_SESSION['user_id'];
    
    $groupController = new GroupController($conn);
    $group = $groupController->getGroupById($groupId);
    
    if ($group && (int)$group['creator_id'] === $organizerId) {
        $groupController->archiveGroup($groupId);
    }
}
header("Location: manage-groups.php");
exit;
