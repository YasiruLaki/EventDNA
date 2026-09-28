<?php
require_once __DIR__ . '/../../includes/guard.php';
require_once __DIR__ . '/../../../attendee/includes/avatar.php';
require_once __DIR__ . '/../../../../data/NotificationRepository.php';

$notificationRepo = new NotificationRepository($conn);
$notifications = $notificationRepo->getForUser($attendeeId);
// Opening the page counts as reading them; items still show as unread on this visit so the user can spot what's new
$notificationRepo->markAllRead($attendeeId);

$today = date('Y-m-d');
$groups = ['Today' => [], 'Earlier' => []];
foreach ($notifications as $n) {
    $groups[date('Y-m-d', strtotime($n['created_at'])) === $today ? 'Today' : 'Earlier'][] = $n;
}

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) { $m = (int)floor($diff / 60); return $m . ' minute' . ($m === 1 ? '' : 's') . ' ago'; }
    if ($diff < 86400) { $h = (int)floor($diff / 3600); return $h . ' hour' . ($h === 1 ? '' : 's') . ' ago'; }
    if ($diff < 172800) return 'Yesterday';
    if ($diff < 604800) return (int)floor($diff / 86400) . ' days ago';
    return date('M j, Y', strtotime($datetime));
}

function notif_link($n) {
    if ($n['reference_type'] === 'event' && $n['reference_id']) {
        return '../../../events/eventView/index.php?id=' . (int)$n['reference_id'];
    }
    return null;
}

$icons = [
    'danger' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><line x1="12" y1="8" x2="12" y2="12.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
    'info'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notifications — EventDNA</title>
<link rel="stylesheet" href="../../dashboard/styles.css" />
<link rel="stylesheet" href="./styles.css">
</head>
<body>

<nav class="top-nav">
  <div class="nav-container">
    <div class="nav-left">
      <a href="../../dashboard/index.php" class="nav-logo">
        <img src="../../../images/logo.png" alt="EventDNA" class="nav-logo-img">
      </a>
      <div class="nav-links">
        <a href="../../../events/ExploreEvents/index.php" class="nav-link">Find Events</a>
        <a href="../../../events/myEvents/index.php" class="nav-link">My Events</a>
        <a href="../index.php" class="nav-link">Communities</a>
        <a href="../../myConnections/index.php" class="nav-link">Connections</a>
      </div>
    </div>
    <div class="nav-right">
      <?= nav_notifications_html() ?>
      <div class="nav-profile-menu">
        <button class="nav-profile-btn">
          <?= nav_avatar_html($attendeeName) ?>
          <span class="nav-profile-name"><?= h(explode(' ', trim($attendeeName))[0]) ?></span>
          <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="nav-dropdown">
          <a href="../../dashboard/index.php" class="dropdown-item">Dashboard</a>
          <a href="../../settings/index.php" class="dropdown-item">Settings</a>
          <a href="../../../auth/logout/index.php" class="dropdown-item text-danger">Log Out</a>
        </div>
      </div>
    </div>
  </div>
</nav>

<main class="dashboard-shell">
  <div class="container" style="max-width:800px; margin:0 auto;">
    <div class="hero-row" style="align-items:center;">
      <div class="hero-title">
        <h1>Notifications</h1>
        <p>Connection requests, event updates, and community activity land here.</p>
      </div>
    </div>

    <div style="display:flex; flex-direction:column; gap:2rem;">

    <?php if (empty($notifications)): ?>
    <div class="card notif-empty">
      <h3>You're all caught up</h3>
      <p>Event updates and other activity will show up here.</p>
    </div>
    <?php endif; ?>

    <?php foreach ($groups as $label => $items): if (empty($items)) continue; ?>
    <div>
      <div class="section-title-row">
        <h2><?= $label ?></h2>
      </div>

      <div class="card notif-list">
        <?php foreach ($items as $n):
            $tone = $n['type'] === NotificationRepository::EVENT_CANCELLED ? 'danger' : 'info';
            $link = notif_link($n);
        ?>
        <div class="notif-item<?= $n['is_read'] ? '' : ' unread' ?>">
          <div class="notif-icon <?= $tone ?>"><?= $icons[$tone] ?></div>
          <div class="notif-body">
            <p class="notif-text"><strong><?= h($n['title']) ?></strong> <?= h($n['message']) ?></p>
            <p class="notif-meta">
              <?= time_ago($n['created_at']) ?>
              <?php if ($link): ?> · <a href="<?= h($link) ?>" class="notif-action">View event</a><?php endif; ?>
            </p>
          </div>
          <?php if (!$n['is_read']): ?><span class="notif-unread-dot"></span><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>

    </div>
  </div>
</main>

</body>
</html>
