<?php
/**
 * JOIN a group (bonus helper — not one of the 4 CRUD ops, but needed
 * so a group actually has members beyond its creator)
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

// Group must exist and be active
$stmt = $conn->prepare("SELECT status FROM groups WHERE group_id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();

if (!$group || $group["status"] !== "ACTIVE") {
    echo json_encode(["success" => false, "code" => "NOT_FOUND", "message" => "Group not available.", "data" => null]);
    exit;
}

// Check for an existing membership row (active or previously removed)
$memberStmt = $conn->prepare("SELECT status FROM group_members WHERE group_id = ? AND user_id = ?");
$memberStmt->bind_param("ii", $group_id, $user_id);
$memberStmt->execute();
$existing = $memberStmt->get_result()->fetch_assoc();

if ($existing && $existing["status"] === "ACTIVE") {
    echo json_encode(["success" => false, "code" => "ALREADY_MEMBER", "message" => "You are already a member.", "data" => null]);
    exit;
}

if ($existing) {
    // Rejoin: had left before, flip status back to ACTIVE
    $rejoin = $conn->prepare("UPDATE group_members SET status = 'ACTIVE' WHERE group_id = ? AND user_id = ?");
    $rejoin->bind_param("ii", $group_id, $user_id);
    $rejoin->execute();
} else {
    // First time joining
    $insert = $conn->prepare("INSERT INTO group_members (group_id, user_id, role, status) VALUES (?, ?, 'MEMBER', 'ACTIVE')");
    $insert->bind_param("ii", $group_id, $user_id);
    $insert->execute();
}

echo json_encode(["success" => true, "code" => "GROUP_JOINED", "message" => "Joined group.", "data" => null]);