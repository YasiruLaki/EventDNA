
<?php

/**
 * UPDATE a group
 *
 * Expects POST:
 *   group_id       (required)
 *   name           (required)
 *   description    (optional)
 *   interests[]    (optional - interest IDs)
 *
 * The logged-in user must be the creator of the group.
 */

session_start();

require_once __DIR__ . "/../../data/database.php";

header("Content-Type: application/json");

// --------------------------------------------------
// 1. Check login
// --------------------------------------------------

if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "code" => "UNAUTHENTICATED",
        "message" => "Please log in.",
        "data" => null
    ]);
    exit;
}

$user_id = (int) $_SESSION["user_id"];

// --------------------------------------------------
// 2. Only allow POST
// --------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "code" => "METHOD_NOT_ALLOWED",
        "message" => "Only POST requests are allowed.",
        "data" => null
    ]);
    exit;
}

// --------------------------------------------------
// 3. Get submitted data
// --------------------------------------------------

$group_id   = (int) ($_POST["group_id"] ?? 0);
$name       = trim($_POST["name"] ?? "");
$description = trim($_POST["description"] ?? "");

// --------------------------------------------------
// 4. Validate group ID
// --------------------------------------------------

if ($group_id <= 0) {
    echo json_encode([
        "success" => false,
        "code" => "VALIDATION_ERROR",
        "message" => "group_id is required.",
        "data" => null
    ]);
    exit;
}

// --------------------------------------------------
// 5. Validate group name
// --------------------------------------------------

if ($name === "") {
    echo json_encode([
        "success" => false,
        "code" => "VALIDATION_ERROR",
        "message" => "Group name is required.",
        "data" => null
    ]);
    exit;
}

if (strlen($name) > 200) {
    echo json_encode([
        "success" => false,
        "code" => "VALIDATION_ERROR",
        "message" => "Group name cannot exceed 200 characters.",
        "data" => null
    ]);
    exit;
}

// --------------------------------------------------
// 6. Get the group
// --------------------------------------------------

$stmt = $conn->prepare(
    "SELECT group_id, creator_id, name, description, status
     FROM groups
     WHERE group_id = ?"
);

$stmt->bind_param("i", $group_id);
$stmt->execute();

$group = $stmt->get_result()->fetch_assoc();

if (!$group) {
    echo json_encode([
        "success" => false,
        "code" => "NOT_FOUND",
        "message" => "Group not found.",
        "data" => null
    ]);
    exit;
}

// --------------------------------------------------
// 7. Check ownership
// --------------------------------------------------

if ((int) $group["creator_id"] !== $user_id) {
    echo json_encode([
        "success" => false,
        "code" => "FORBIDDEN",
        "message" => "You do not have permission to update this group.",
        "data" => null
    ]);
    exit;
}

// --------------------------------------------------
// 8. Check group status
// --------------------------------------------------

if ($group["status"] !== "ACTIVE") {
    echo json_encode([
        "success" => false,
        "code" => "GROUP_NOT_ACTIVE",
        "message" => "Archived groups cannot be updated.",
        "data" => null
    ]);
    exit;
}

// --------------------------------------------------
// 9. Get interests
// --------------------------------------------------

$interests = $_POST["interests"] ?? [];

// If a single value was sent instead of interests[]
// convert it into an array.
if (!is_array($interests)) {
    $interests = [$interests];
}

// Convert to unique positive integers
$cleanInterests = [];

foreach ($interests as $interest_id) {
    $interest_id = (int) $interest_id;

    if ($interest_id > 0) {
        $cleanInterests[] = $interest_id;
    }
}

$cleanInterests = array_values(array_unique($cleanInterests));

// --------------------------------------------------
// 10. Validate interest IDs
// --------------------------------------------------

if (!empty($cleanInterests)) {

    $placeholders = implode(",", array_fill(0, count($cleanInterests), "?"));

    $types = str_repeat("i", count($cleanInterests));

    $sql = "SELECT interest_id
            FROM interests
            WHERE interest_id IN ($placeholders)
            AND status = 'ACTIVE'";

    $interestCheck = $conn->prepare($sql);

    $interestCheck->bind_param($types, ...$cleanInterests);

    $interestCheck->execute();

    $result = $interestCheck->get_result();

    $validInterestIds = [];

    while ($row = $result->fetch_assoc()) {
        $validInterestIds[] = (int) $row["interest_id"];
    }

    // If the number does not match, one or more IDs are invalid.
    if (count($validInterestIds) !== count($cleanInterests)) {
        echo json_encode([
            "success" => false,
            "code" => "INVALID_INTEREST",
            "message" => "One or more interest IDs are invalid or inactive.",
            "data" => null
        ]);
        exit;
    }

    $cleanInterests = $validInterestIds;
}

// --------------------------------------------------
// 11. Start database transaction
// --------------------------------------------------

$conn->begin_transaction();

try {

    // --------------------------------------------------
    // 12. Update group information
    // --------------------------------------------------

    $updateStmt = $conn->prepare(
        "UPDATE groups
         SET name = ?, description = ?
         WHERE group_id = ?"
    );

    $updateStmt->bind_param(
        "ssi",
        $name,
        $description,
        $group_id
    );

    if (!$updateStmt->execute()) {
        throw new Exception("Failed to update group.");
    }

    // --------------------------------------------------
    // 13. Replace interest tags
    // --------------------------------------------------

    // Delete existing interests first
    $deleteInterestStmt = $conn->prepare(
        "DELETE FROM group_interests
         WHERE group_id = ?"
    );

    $deleteInterestStmt->bind_param("i", $group_id);

    if (!$deleteInterestStmt->execute()) {
        throw new Exception("Failed to update group interests.");
    }

    // Add new interests
    if (!empty($cleanInterests)) {

        $insertInterestStmt = $conn->prepare(
            "INSERT INTO group_interests (group_id, interest_id)
             VALUES (?, ?)"
        );

        foreach ($cleanInterests as $interest_id) {

            $insertInterestStmt->bind_param(
                "ii",
                $group_id,
                $interest_id
            );

            if (!$insertInterestStmt->execute()) {
                throw new Exception("Failed to add group interest.");
            }
        }
    }

    // --------------------------------------------------
    // 14. Commit transaction
    // --------------------------------------------------

    $conn->commit();

    // --------------------------------------------------
    // 15. Return updated group
    // --------------------------------------------------

    echo json_encode([
        "success" => true,
        "code" => "GROUP_UPDATED",
        "message" => "Group updated successfully.",
        "data" => [
            "group_id" => $group_id,
            "name" => $name,
            "description" => $description,
            "interests" => $cleanInterests
        ]
    ]);

} catch (Exception $e) {

    // Undo all database changes if something failed
    $conn->rollback();

    echo json_encode([
        "success" => false,
        "code" => "UPDATE_FAILED",
        "message" => $e->getMessage(),
        "data" => null
    ]);
}
?>


