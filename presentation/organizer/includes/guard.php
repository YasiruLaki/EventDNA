<?php
// Shared setup for organizer pages: session, role check, CSRF token and output escaping.
session_start();

if (!isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 2) {
    header("Location: ../auth/login/index.php");
    exit;
}

$organizerId = (int)$_SESSION['user_id'];
$organizerName = $_SESSION['full_name'] ?? 'Organizer';

require_once __DIR__ . '/../../../data/database.php';

// Check Account Status
$stmt = $conn->prepare("SELECT account_status, suspended_until FROM users WHERE user_id = ?");
$stmt->bind_param("i", $organizerId);
$stmt->execute();
$uRes = $stmt->get_result()->fetch_assoc();

if (!$uRes) {
    header("Location: ../auth/login/index.php");
    exit;
}

if ($uRes['account_status'] === 'DISABLED') {
    die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'><h1>Account Disabled</h1><p>Your account has been disabled. Please contact support.</p></div>");
}

if ($uRes['account_status'] === 'SUSPENDED') {
    if (strtotime($uRes['suspended_until']) <= time()) {
        // Auto-reactivate
        $stmt = $conn->prepare("UPDATE users SET account_status = 'ACTIVE', suspended_at = NULL, suspended_until = NULL, suspension_reason = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $organizerId);
        $stmt->execute();
    } else {
        $sus_until = date('d F Y, h:i A', strtotime($uRes['suspended_until']));
        die("<div style='text-align:center; padding: 50px; font-family: sans-serif;'><h1>Account Suspended</h1><p>Your account is suspended until $sus_until.</p></div>");
    }
}

$stmt = $conn->prepare("SELECT profile_completed, profile_photo FROM profiles WHERE user_id = ?");
$stmt->bind_param("i", $organizerId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$organizerPhoto = $res['profile_photo'] ?? null;

if (!$res || !$res['profile_completed']) {
    if (strpos($_SERVER['REQUEST_URI'], 'organizer/onboarding/index.php') === false) {
        // Redirect to onboarding if profile is incomplete
        header("Location: /eventDNA/presentation/organizer/onboarding/index.php");
        exit;
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
