<?php
session_start();
require_once __DIR__ . "/../../data/database.php";
require_once __DIR__ . "/../../application/controllers/GroupController.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "code" => "UNAUTHENTICATED", "message" => "Please log in.", "data" => null]);
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$group_id = (int) ($_POST["group_id"] ?? 0);

$groupController = new GroupController($conn);
$result = $groupController->archive($user_id, $group_id);

echo json_encode($result);
?>