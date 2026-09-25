<?php
/**
 * READ (list) groups — used for "Discover Groups" / "My Groups" / search
 *
 * Expects GET:
 *   search   (optional) - filter by group name
 *   mine     (optional) - "1" to show only groups the current user has joined
 */

session_start();
require_once __DIR__ . "/../../data/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "code" => "UNAUTHENTICATED", "message" => "Please log in.", "data" => null]);
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$search  = "%" . ($_GET["search"] ?? "") . "%";
$mineOnly = isset($_GET["mine"]) && $_GET["mine"] == "1";

if ($mineOnly) {
    // Only groups this user is an ACTIVE member of
    $sql = "SELECT g.group_id, g.name, g.description, g.creator_id, g.created_at,
                   (SELECT COUNT(*) FROM group_members gm2 WHERE gm2.group_id = g.group_id AND gm2.status = 'ACTIVE') AS member_count
            FROM groups g
            JOIN group_members gm ON gm.group_id = g.group_id
            WHERE g.status = 'ACTIVE' AND gm.user_id = ? AND gm.status = 'ACTIVE' AND g.name LIKE ?
            ORDER BY g.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $search);
} else {
    // All active/discoverable groups
    $sql = "SELECT g.group_id, g.name, g.description, g.creator_id, g.created_at,
                   (SELECT COUNT(*) FROM group_members gm2 WHERE gm2.group_id = g.group_id AND gm2.status = 'ACTIVE') AS member_count
            FROM groups g
            WHERE g.status = 'ACTIVE' AND g.name LIKE ?
            ORDER BY g.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $search);
}

$stmt->execute();
$result = $stmt->get_result();

$groups = [];
while ($row = $result->fetch_assoc()) {
    $groups[] = $row;
}

echo json_encode([
    "success" => true,
    "code" => "GROUPS_FETCHED",
    "message" => "",
    "data" => $groups
]);