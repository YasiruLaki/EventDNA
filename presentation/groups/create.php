<?php
/**
 * CREATE a group  —  POST /groups/create.php
 *
 * Follows: EventDNA Organizer Create Group Implementation Plan, section 7 & 10.
 *
 * Steps (must happen in this order, all inside one transaction):
 *   1. Check session (authenticated?)
 *   2. Check role == ORGANIZER
 *   3. Validate name / description / interest_ids
 *   4. INSERT groups
 *   5. INSERT group_members (creator = OWNER)
 *   6. INSERT group_interests
 *   7. INSERT notifications (to the organizer's own event attendees)
 *   8. COMMIT (or ROLLBACK on any failure)
 *
 * Expects POST:
 *   name         (required)
 *   description  (optional but validated/sanitized)
 *   interests[]  (optional, array of interest_id — every ID must exist & be ACTIVE)
 */

session_start();
require_once __DIR__ . "/../../data/database.php";

header("Content-Type: application/json");

// ---------- 1. Authenticated? ----------
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "code" => "UNAUTHENTICATED", "message" => "Please log in.", "data" => null]);
    exit;
}

$user_id = (int) $_SESSION["user_id"];

// ---------- 2. Role == ORGANIZER? ----------
// Per the plan: "Do not rely only on hiding the button using JavaScript." This check is the real gate.
$roleCheck = $conn->prepare(
    "SELECT r.role_name FROM user_roles ur
     JOIN roles r ON r.role_id = ur.role_id
     WHERE ur.user_id = ? AND r.role_name = 'ORGANIZER'"
);
$roleCheck->bind_param("i", $user_id);
$roleCheck->execute();

if ($roleCheck->get_result()->num_rows === 0) {
    echo json_encode(["success" => false, "code" => "FORBIDDEN", "message" => "You do not have permission to create groups.", "data" => null]);
    exit;
}

// ---------- 3. Validate input ----------
$name        = trim($_POST["name"] ?? "");
$description = trim($_POST["description"] ?? "");
$rawInterests = $_POST["interests"] ?? []; // array of interest_id strings

if ($name === "") {
    echo json_encode(["success" => false, "code" => "VALIDATION_ERROR", "message" => "Group name is required.", "data" => null]);
    exit;
}
if (mb_strlen($name) > 200) {
    echo json_encode(["success" => false, "code" => "VALIDATION_ERROR", "message" => "Group name is too long.", "data" => null]);
    exit;
}
if (mb_strlen($description) > 2000) {
    echo json_encode(["success" => false, "code" => "VALIDATION_ERROR", "message" => "Description is too long.", "data" => null]);
    exit;
}

// De-duplicate and cast every submitted interest ID to int
$interestIds = [];
if (is_array($rawInterests)) {
    $interestIds = array_values(array_unique(array_map("intval", $rawInterests)));
}

// If any interest was submitted, every single one must exist and be ACTIVE — otherwise reject the whole request
if (count($interestIds) > 0) {
    $placeholders = implode(",", array_fill(0, count($interestIds), "?"));
    $types = str_repeat("i", count($interestIds));

    $checkStmt = $conn->prepare(
        "SELECT interest_id FROM interests WHERE status = 'ACTIVE' AND interest_id IN ($placeholders)"
    );
    $checkStmt->bind_param($types, ...$interestIds);
    $checkStmt->execute();
    $validRows = $checkStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (count($validRows) !== count($interestIds)) {
        echo json_encode(["success" => false, "code" => "INVALID_INTEREST", "message" => "One or more selected interests are invalid.", "data" => null]);
        exit;
    }
}

// ---------- 4–7. Insert group / member / interests / notifications, all in one transaction ----------
$conn->begin_transaction();

try {
    // 4. INSERT groups — creator_id comes ONLY from the session, never from the request body
    $stmt = $conn->prepare(
        "INSERT INTO groups (creator_id, name, description, status) VALUES (?, ?, ?, 'ACTIVE')"
    );
    $stmt->bind_param("iss", $user_id, $name, $description);
    $stmt->execute();
    $group_id = $conn->insert_id;

    // 5. INSERT group_members — organizer becomes first member, role = OWNER
    $memberStmt = $conn->prepare(
        "INSERT INTO group_members (group_id, user_id, role, status) VALUES (?, ?, 'OWNER', 'ACTIVE')"
    );
    $memberStmt->bind_param("ii", $group_id, $user_id);
    $memberStmt->execute();

    // 6. INSERT group_interests
    if (count($interestIds) > 0) {
        $interestStmt = $conn->prepare(
            "INSERT INTO group_interests (group_id, interest_id) VALUES (?, ?)"
        );
        foreach ($interestIds as $interest_id) {
            $interestStmt->bind_param("ii", $group_id, $interest_id);
            $interestStmt->execute();
        }
    }

    // 7. INSERT notifications — notify this organizer's own event attendees (spec section 16)
    // Look up the organizer's name for the notification message
    $nameStmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
    $nameStmt->bind_param("i", $user_id);
    $nameStmt->execute();
    $organizerName = $nameStmt->get_result()->fetch_assoc()["full_name"] ?? "An organizer";

    // Find everyone registered/approved for any event this organizer runs (excluding the organizer themself)
    $attendeeStmt = $conn->prepare(
        "SELECT DISTINCT er.user_id
         FROM event_registrations er
         JOIN events e ON e.event_id = er.event_id
         WHERE e.organizer_id = ?
           AND er.status IN ('REGISTERED','APPROVED')
           AND er.user_id != ?"
    );
    $attendeeStmt->bind_param("ii", $user_id, $user_id);
    $attendeeStmt->execute();
    $attendees = $attendeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (count($attendees) > 0) {
        $notifyStmt = $conn->prepare(
            "INSERT INTO notifications (user_id, type, title, message, reference_type, reference_id, is_read)
             VALUES (?, 'NEW_GROUP', 'New Community Group', ?, 'group', ?, 0)"
        );
        $message = "$organizerName created a new group: \"$name\". Explore the group to join.";
        foreach ($attendees as $attendee) {
            $attendee_id = (int) $attendee["user_id"];
            $notifyStmt->bind_param("isi", $attendee_id, $message, $group_id);
            $notifyStmt->execute();
        }
    }

    // 8. COMMIT
    $conn->commit();

    echo json_encode([
        "success" => true,
        "code" => "GROUP_CREATED",
        "message" => "Group created successfully. You are now the owner of this group.",
        "data" => ["group_id" => $group_id]
    ]);

} catch (Exception $e) {
    // ROLLBACK — prevents a group existing without an owner, without its tags, or with partial notifications
    $conn->rollback();
    echo json_encode(["success" => false, "code" => "SERVER_ERROR", "message" => "The group could not be created. Please try again.", "data" => null]);
}