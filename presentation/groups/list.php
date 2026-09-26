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
$search = $_GET["search"] ?? "";
$mineOnly = isset($_GET["mine"]) && $_GET["mine"] == "1";
$status = isset($_GET["status"]) ? $_GET["status"] : 'ACTIVE';

$groupController = new GroupController($conn);
$result = $groupController->getList($user_id, $mineOnly, $search, $status);

echo json_encode($result);
?>