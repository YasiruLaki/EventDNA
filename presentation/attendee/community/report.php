<?php
require_once __DIR__ . '/../includes/guard.php';
require_once "../../../data/database.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$reporterId = (int)$_SESSION['user_id'];
$type = $input['type'] ?? '';
$contentId = (int)($input['content_id'] ?? 0);
$reason = trim($input['reason'] ?? '');

if (!in_array($type, ['POST', 'COMMENT']) || $contentId <= 0 || empty($reason)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO reported_content (reporter_id, content_type, content_id, reason) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isis", $reporterId, $type, $contentId, $reason);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
