<?php
require_once __DIR__ . '/../../../data/database.php';
require_once __DIR__ . '/../../../application/controllers/EventController.php';

require_once __DIR__ . '/../../attendee/includes/guard.php';

$eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $eventId <= 0) {
    header("Location: ../myEvents/index.php");
    exit;
}

$eventController = new EventController($conn);
$result = $eventController->updateRegistrationUpdates($_SESSION['user_id'], $eventId, isset($_POST['updates']));

$query = $result['success'] ? "success=" . urlencode("Your update preferences have been saved.") : "error=" . urlencode($result['message']);
header("Location: index.php?id=" . $eventId . "&" . $query);
exit;
?>
