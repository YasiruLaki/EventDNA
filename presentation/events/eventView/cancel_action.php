<?php
session_start();
require_once __DIR__ . '/../../../data/database.php';
require_once __DIR__ . '/../../../application/controllers/EventController.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}

$eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $eventId <= 0) {
    header("Location: ../myEvents/index.php");
    exit;
}

$eventController = new EventController($conn);
$result = $eventController->cancelRegistration($_SESSION['user_id'], $eventId);

$query = $result['success'] ? "success=" . urlencode("Your registration has been cancelled.") : "error=" . urlencode($result['message']);
header("Location: index.php?id=" . $eventId . "&" . $query);
exit;
?>
