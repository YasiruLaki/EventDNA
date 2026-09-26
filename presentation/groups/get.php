<?php
/**
 * READ a single group's details (About tab on Group Details screen)
 *
 * Expects GET:
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
$group_id = (int) ($_GET["group_id"] ?? 0);

if ($group_id === 0) {
    echo json_encode(["success" => false, "code" => "VALIDATION_ERROR", "message" => "group_id is required.", "data" => null]);
    exit;
}

// 1. Fetch the group itself
$stmt = $conn->prepare(
    "SELECT group_id, creator_id, name, description, created_at, status FROM groups WHERE group_id = ?"
);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();

if (!$group) {
    echo json_encode(["success" => false, "code" => "NOT_FOUND", "message" => "Group not found.", "data" => null]);
    exit;
}

// 2. Interest tags
$interestStmt = $conn->prepare(
    "SELECT i.interest_id, i.interest_name FROM group_interests gi
     JOIN interests i ON i.interest_id = gi.interest_id
     WHERE gi.group_id = ?"
);
$interestStmt->bind_param("i", $group_id);
$interestStmt->execute();
$interests = $interestStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Member count
$countStmt = $conn->prepare(
    "SELECT COUNT(*) AS member_count FROM group_members WHERE group_id = ? AND status = 'ACTIVE'"
);
$countStmt->bind_param("i", $group_id);
$countStmt->execute();
$memberCount = $countStmt->get_result()->fetch_assoc()["member_count"];

// 4. Is the current user a member? (needed to gate Discussion/Resources tabs)
$memberStmt = $conn->prepare(
    "SELECT role, status FROM group_members WHERE group_id = ? AND user_id = ?"
);
$memberStmt->bind_param("ii", $group_id, $user_id);
$memberStmt->execute();
$membership = $memberStmt->get_result()->fetch_assoc();

$isMember  = $membership && $membership["status"] === "ACTIVE";
$myRole    = $isMember ? $membership["role"] : null;

echo json_encode([
    "success" => true,
    "code" => "GROUP_FETCHED",
    "message" => "",
    "data" => [
        "group" => $group,
        "interests" => $interests,
        "member_count" => (int) $memberCount,
        "is_member" => $isMember,
        "my_role" => $myRole
    ]
]);