<?php
// Shared setup for attendee event pages: session, role check, CSRF token and output helpers.
session_start();

if (!isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
    header("Location: ../../auth/login/index.php");
    exit;
}

$attendeeId = (int)$_SESSION['user_id'];
$attendeeName = $_SESSION['full_name'] ?? 'Attendee';

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

function format_event_date($date) {
    return date('M j, Y', strtotime($date));
}

function format_time($time) {
    return date('g:i A', strtotime($time));
}

function format_time_range($start, $end) {
    return format_time($start) . ' – ' . format_time($end);
}

// Cover photos are stored relative to the project root; pages live two folders below presentation/
function cover_url($path) {
    return $path ? '../../../' . $path : null;
}

function seats_label($remaining) {
    return $remaining === 1 ? '1 spot left' : $remaining . ' spots left';
}
?>
