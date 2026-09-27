<?php
session_start();

if (!isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 3) {
    header("Location: /eventDNA/presentation/admin/login.php");
    exit;
}

$adminId = (int)$_SESSION['user_id'];
$adminName = $_SESSION['full_name'] ?? 'Admin';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

function csrf_valid() {
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function h($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}
?>
