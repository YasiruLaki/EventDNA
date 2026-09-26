<?php
// Shared setup for organizer pages: session, role check, CSRF token and output escaping.
session_start();

if (!isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 2) {
    header("Location: ../auth/login/index.php");
    exit;
}

$organizerId = (int)$_SESSION['user_id'];
$organizerName = $_SESSION['full_name'] ?? 'Organizer';

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

function format_time_range($start, $end) {
    return date('g:i A', strtotime($start)) . ' – ' . date('g:i A', strtotime($end));
}

// Badge colours used by the dashboard and event pages
function status_badge_style($status) {
    $styles = [
        'Live' => 'background: rgba(220, 38, 38, 0.1); color: var(--danger);',
        'Upcoming' => 'background: var(--primary-tint); color: var(--primary);',
        'Completed' => 'background: rgba(22, 163, 74, 0.1); color: var(--success);',
        'Cancelled' => 'background: rgba(148, 163, 184, 0.15); color: var(--text-secondary);',
    ];
    return $styles[$status] ?? $styles['Cancelled'];
}
?>
