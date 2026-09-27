<?php
require_once __DIR__ . '/../../../data/database.php';

// Top-nav avatar for the signed-in attendee: their profile photo, or their initial if they have none
function nav_avatar_html($name) {
    global $conn;

    $photo = null;
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT profile_photo FROM profiles WHERE user_id = ?");
        $userId = (int)$_SESSION['user_id'];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $photo = $row['profile_photo'] ?? null;
    }

    $style = 'width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;';
    if ($photo) {
        // Photos are stored relative to the project root, so climb up from the current page's folder
        $root = str_replace('\\', '/', realpath(__DIR__ . '/../../..'));
        $dir = str_replace('\\', '/', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
        $prefix = str_repeat('../', substr_count(substr($dir, strlen($root)), '/'));
        return '<img src="' . htmlspecialchars($prefix . $photo) . '" alt="Profile" class="nav-avatar" style="' . $style . ' object-fit: cover;">';
    }

    return '<div style="' . $style . ' background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">'
        . htmlspecialchars(mb_substr($name, 0, 1)) . '</div>';
}
?>
