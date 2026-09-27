<?php
require_once __DIR__ . '/../../../data/database.php';
require_once __DIR__ . '/../../../data/NotificationRepository.php';

// Keep the session name in sync with the database: sign-up never sets it, and it goes stale if the name is edited elsewhere
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
    $sessionUserId = (int)$_SESSION['user_id'];
    $stmt->bind_param("i", $sessionUserId);
    $stmt->execute();
    $sessionUser = $stmt->get_result()->fetch_assoc();
    if ($sessionUser && trim($sessionUser['full_name']) !== '') {
        $_SESSION['full_name'] = $sessionUser['full_name'];
    }
}

// Stored paths and site links are relative to the project root, so climb up from the current page's folder
function nav_root_prefix() {
    $root = str_replace('\\', '/', realpath(__DIR__ . '/../../..'));
    $dir = str_replace('\\', '/', realpath(dirname($_SERVER['SCRIPT_FILENAME'])));
    return str_repeat('../', substr_count(substr($dir, strlen($root)), '/'));
}

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
        return '<img src="' . htmlspecialchars(nav_root_prefix() . $photo) . '" alt="Profile" class="nav-avatar" style="' . $style . ' object-fit: cover;">';
    }

    return '<div style="' . $style . ' background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">'
        . htmlspecialchars(mb_substr($name, 0, 1)) . '</div>';
}

// Top-nav bell linking to the notifications page, with the unread count
function nav_notifications_html() {
    global $conn;

    $unread = isset($_SESSION['user_id']) ? (new NotificationRepository($conn))->countUnread((int)$_SESSION['user_id']) : 0;
    $href = nav_root_prefix() . 'presentation/attendee/community/notifications/index.php';
    $label = $unread ? "Notifications ($unread unread)" : 'Notifications';

    $html = '<a href="' . htmlspecialchars($href) . '" class="nav-bell" aria-label="' . $label . '" title="Notifications"'
        . ' style="position: relative; display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; color: var(--text-secondary, #475569); text-decoration: none; margin-right: 0.5rem;">'
        . '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>';
    if ($unread) {
        $html .= '<span style="position: absolute; top: 2px; right: 0; min-width: 18px; height: 18px; padding: 0 5px; box-sizing: border-box; border-radius: 999px; background: #dc2626; color: #fff; font-size: 0.68rem; font-weight: 700; line-height: 14px; text-align: center; border: 2px solid #fff;">'
            . ($unread > 9 ? '9+' : $unread) . '</span>';
    }
    return $html . '</a>';
}
?>
