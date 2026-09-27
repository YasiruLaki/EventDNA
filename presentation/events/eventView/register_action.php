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
    header("Location: ../ExploreEvents/index.php");
    exit;
}

$eventController = new EventController($conn);
$result = $eventController->registerForEvent($_SESSION['user_id'], $eventId, isset($_POST['updates']));

if ($result['success']) {
    if (isset($result['status']) && $result['status'] === 'PENDING') {
        header("Location: index.php?id=" . $eventId . "&request_sent=1");
    } else {
        header("Location: ../registrationSuccess/index.php?id=" . $eventId);
    }
} else {
    header("Location: index.php?id=" . $eventId . "&error=" . urlencode($result['message']));
}
exit;
?>
