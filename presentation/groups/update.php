
<?php
session_start();
require_once __DIR__ . "/../../data/database.php";
require_once __DIR__ . "/../../application/controllers/GroupController.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "code" => "UNAUTHENTICATED", "message" => "Please log in.", "data" => null]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "code" => "METHOD_NOT_ALLOWED", "message" => "Only POST requests are allowed.", "data" => null]);
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$group_id = (int) ($_POST["group_id"] ?? 0);
$name = trim($_POST["name"] ?? "");
$description = trim($_POST["description"] ?? "");
$interests = $_POST["interests"] ?? [];

$groupController = new GroupController($conn);
$result = $groupController->update($user_id, $group_id, $name, $description, $interests);

echo json_encode($result);
?>


