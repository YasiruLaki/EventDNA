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
$name = trim($_POST["name"] ?? "");
$description = trim($_POST["description"] ?? "");
$interests = $_POST["interests"] ?? [];

$groupController = new GroupController($conn);
$result = $groupController->create($user_id, $name, $description, $interests);

echo json_encode($result);
?>