<?php
/**
 * DELETE (archive) a group.
 * We never hard-delete a group — we set status = ARCHIVED so historical
 * posts/resources/members stay intact for community history.
 * Only the group's creator (OWNER) or an ADMINISTRATOR may archive it.
 *
 * Expects POST:
 *   group_id (required)
 */

session_start();
require_once __DIR__ . "/../../data/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "code" => "UNAUTHENTICATED", "message" => "Please log in.", "data" => null]);
    exit;
}

$user_id  = (int) $_SESSION["user_id"];
$group_id = (int) ($_POST["group_id"] ?? 0);

if ($group_id === 0) {
    echo json_encode(["success" => false, "code" => "VALIDATION_ERROR", "message" => "group_id is required.", "data" => null]);
    exit;
}

$stmt = $conn->prepare("SELECT creator_id, status FROM groups WHERE group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();

if (!$group) {
    echo json_encode(["success" => false, "code" => "NOT_FOUND", "message" => "Group not found.", "data" => null]);
    exit;
}

if ($group["status"] === "ARCHIVED") {
    echo json_encode(["success" => false, "code" => "ALREADY_ARCHIVED", "message" => "Group is already archived.", "data" => null]);
    exit;
}

$isCreator = ((int) $group["creator_id"] === $user_id);

$adminCheck = $conn->prepare(
    "SELECT r.role_name FROM user_roles ur
     JOIN roles r ON r.role_id = ur.role_id
     WHERE ur.user_id = ? AND r.role_name = 'ADMINISTRATOR'"
);
$adminCheck->bind_param("i", $user_id);
$adminCheck->execute();
$isAdmin = $adminCheck->get_result()->num_rows > 0;

if (!$isCreator && !$isAdmin) {
    echo json_encode(["success" => false, "code" => "FORBIDDEN", "message" => "You cannot archive this group.", "data" => null]);
    exit;
}

$archiveStmt = $conn->prepare("UPDATE groups SET status = 'ARCHIVED' WHERE group_id = ?");
$archiveStmt->bind_param("i", $group_id);
$archiveStmt->execute();

echo json_encode(["success" => true, "code" => "GROUP_ARCHIVED", "message" => "Group archived.", "data" => null]);