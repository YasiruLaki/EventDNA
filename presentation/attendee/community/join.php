<?php
require_once __DIR__ . '/../includes/guard.php';
require_once "../../../data/database.php";
require_once "../../../application/controllers/GroupController.php";

$groupId = $_GET['id'] ?? 0;
if ($groupId > 0) {
    $groupController = new GroupController($conn);
    $groupController->joinGroup($groupId, $_SESSION['user_id']);
}
header("Location: view.php?id=" . $groupId);
