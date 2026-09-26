<?php
/**
 * READ — list all active interests, for building the "Interest Tags" checkboxes
 * on the Create Group form (and anywhere else tags are picked from).
 * No role restriction — any authenticated user can see the vocabulary list.
 */

session_start();
require_once __DIR__ . "/../../data/database.php";

header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "code" => "UNAUTHENTICATED", "message" => "Please log in.", "data" => null]);
    exit;
}

$result = $conn->query("SELECT interest_id, interest_name FROM interests WHERE status = 'ACTIVE' ORDER BY interest_name ASC");
$interests = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    "success" => true,
    "code" => "INTERESTS_FETCHED",
    "message" => "",
    "data" => $interests
]);