<?php
session_start();
require_once __DIR__ . '/../../../data/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}

$userId = $_SESSION['user_id'];
$eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

if ($eventId > 0) {
    // Check if event exists and registration is open
    $stmt = $conn->prepare("SELECT capacity, (SELECT COUNT(*) FROM event_registrations r WHERE r.event_id = events.event_id AND r.status IN ('PENDING','APPROVED','REGISTERED')) AS registered_count FROM events WHERE event_id = ? AND status != 'CANCELLED'");
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $event = $res->fetch_assoc();
        $spotsLeft = max(0, $event['capacity'] - $event['registered_count']);
        
        if ($spotsLeft > 0) {
            // Check if user is already registered
            $check = $conn->prepare("SELECT registration_id FROM event_registrations WHERE event_id = ? AND user_id = ?");
            $check->bind_param("ii", $eventId, $userId);
            $check->execute();
            if ($check->get_result()->num_rows == 0) {
                // Register
                $insert = $conn->prepare("INSERT INTO event_registrations (event_id, user_id, status) VALUES (?, ?, 'REGISTERED')");
                $insert->bind_param("ii", $eventId, $userId);
                $insert->execute();
            }
            
            // Redirect to success
            header("Location: ../registrationSuccess/index.php?id=" . $eventId);
            exit;
        }
    }
}

// Fallback to explore if error
header("Location: ../ExploreEvents/index.php");
exit;
?>
