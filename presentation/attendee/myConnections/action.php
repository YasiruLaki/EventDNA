<?php
require_once __DIR__ . '/../includes/guard.php';
require_once __DIR__ . '/../../../data/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$connectionId = (int)($_POST['connection_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($connectionId > 0 && in_array($action, ['accept', 'reject', 'remove'])) {
    if ($action === 'accept') {
        $stmt = $conn->prepare("UPDATE connections SET status = 'ACCEPTED', responded_at = CURRENT_TIMESTAMP WHERE connection_id = ? AND user2_id = ? AND status = 'PENDING'");
        $stmt->bind_param("ii", $connectionId, $userId);
        $stmt->execute();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE connections SET status = 'REJECTED', responded_at = CURRENT_TIMESTAMP WHERE connection_id = ? AND user2_id = ? AND status = 'PENDING'");
        $stmt->bind_param("ii", $connectionId, $userId);
        $stmt->execute();
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM connections WHERE connection_id = ? AND (user1_id = ? OR user2_id = ?) AND status = 'ACCEPTED'");
        $stmt->bind_param("iii", $connectionId, $userId, $userId);
        $stmt->execute();
    }
}

// Redirect back to the correct tab
$tab = 'connections';
if ($action === 'accept' || $action === 'reject') $tab = 'received';

header("Location: index.php?tab=" . $tab);
exit;
?>
