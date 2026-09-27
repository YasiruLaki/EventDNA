<?php
session_start();

if (!isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
    header("Location: /eventDNA/presentation/auth/login/index.php");
    exit;
}

require_once __DIR__ . '/../../../data/database.php';
require_once __DIR__ . '/avatar.php';

$attendeeId = (int)$_SESSION['user_id'];
$attendeeName = $_SESSION['full_name'] ?? 'Attendee';

// Check Account Status
$stmt = $conn->prepare("SELECT account_status, suspended_until FROM users WHERE user_id = ?");
$stmt->bind_param("i", $attendeeId);
$stmt->execute();
$uRes = $stmt->get_result()->fetch_assoc();

if (!$uRes) {
    header("Location: /eventDNA/presentation/auth/login/index.php");
    exit;
}

if ($uRes['account_status'] === 'DISABLED') {
    die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'><h1>Account Disabled</h1><p>Your account has been disabled. Please contact support.</p></div>");
}

if ($uRes['account_status'] === 'SUSPENDED') {
    if (strtotime($uRes['suspended_until']) <= time()) {
        // Auto-reactivate
        $stmt = $conn->prepare("UPDATE users SET account_status = 'ACTIVE', suspended_at = NULL, suspended_until = NULL, suspension_reason = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $attendeeId);
        $stmt->execute();
    } else {
        $sus_until = date('d F Y, h:i A', strtotime($uRes['suspended_until']));
        die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'><h1>Account Suspended</h1><p>Your account is suspended until $sus_until.</p></div>");
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function csrf_valid() {
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
