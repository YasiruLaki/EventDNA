<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../../auth/login.php"); exit; }
require_once "../../../data/database.php";
require_once "../../../application/controllers/GroupController.php";

$groupId = $_GET['id'] ?? 0;
if ($groupId > 0) {
    $groupController = new GroupController($conn);
    $groupController->leaveGroup($groupId, $_SESSION['user_id']);
}
header("Location: view.php?id=" . $groupId);
