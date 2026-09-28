<?php
require_once __DIR__ . '/../includes/guard.php';
require_once __DIR__ . '/../../../data/database.php';
require_once __DIR__ . '/../../../data/ConnectionRepository.php';

$connRepo = new ConnectionRepository($conn);
$userId = (int)$_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'connections';
$search = $_GET['q'] ?? '';

$stats = $connRepo->getStats($userId);

if ($tab === 'received') {
    $people = $connRepo->getReceivedRequests($userId);
} elseif ($tab === 'sent') {
    $people = $connRepo->getSentRequests($userId);
} else {
    $people = $connRepo->getConnections($userId);
}

// Filter by search if needed
if (!empty($search)) {
    $people = array_filter($people, function($p) use ($search) {
        return stripos($p['name'], $search) !== false || stripos($p['role'] ?? '', $search) !== false;
    });
}

function getAvatar($path, $name) {
    if (!$path) return 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80';
    return preg_match('#^https?://#i', $path) ? $path : '../../../' . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Connections — EventDNA</title>
<link rel="stylesheet" href="../../../globals.css" />
<link rel="stylesheet" href="../dashboard/styles.css" />
<link rel="stylesheet" href="styles.css">
</head>
<body>

  <nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="../dashboard/index.php" class="nav-logo">
          <img src="../../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="../../events/ExploreEvents/index.php" class="nav-link">Find Events</a>
          <a href="../../events/myEvents/index.php" class="nav-link">My Events</a>
          <a href="../community/index.php" class="nav-link">Communities</a>
          <a href="#" class="nav-link active">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <?= nav_notifications_html() ?>
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <?= nav_avatar_html($attendeeName) ?>
            <span class="nav-profile-name"><?= htmlspecialchars(explode(' ', trim($attendeeName))[0]) ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="nav-dropdown">
            <a href="../settings/index.php" class="dropdown-item">Profile</a>
            <a href="../../auth/logout/index.php" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

<div class="dashboard-shell">
  <main class="dashboard-content">
    
    <!-- Hero Row -->
    <div class="hero-row" style="margin-bottom: 2rem;">
      <div class="hero-copy">
        <h1 style="font-size: 2.2rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0;">My Connections</h1>
        <p class="supporting-copy">Your professional network built through EventDNA.</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr)); max-width: 600px;">
      <div class="stat-card">
        <div class="stat-num"><?= $stats['total'] ?></div>
        <div class="stat-label">Total Connections</div>
      </div>
      <div class="stat-card accent">
        <div class="stat-num"><?= $stats['pending'] ?></div>
        <div class="stat-label">Pending Requests</div>
      </div>
    </div>

    <!-- Tabs & Toolbar -->
    <div class="connections-control-bar">
      <div class="tabs">
        <a href="?tab=connections&q=<?= urlencode($search) ?>" class="tab <?= $tab === 'connections' ? 'active' : '' ?>" style="text-decoration: none;">Connections</a>
        <a href="?tab=received&q=<?= urlencode($search) ?>" class="tab <?= $tab === 'received' ? 'active' : '' ?>" style="text-decoration: none;">Received</a>
        <a href="?tab=sent&q=<?= urlencode($search) ?>" class="tab <?= $tab === 'sent' ? 'active' : '' ?>" style="text-decoration: none;">Sent</a>
      </div>

      <div class="toolbar">
        <form method="get" class="search-input">
          <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><line x1="21" y1="21" x2="16.5" y2="16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          <input type="text" name="q" placeholder="Search people..." value="<?= htmlspecialchars($search) ?>">
        </form>
      </div>
    </div>

    <!-- Connection cards -->
    <div class="connections-grid">
      <?php if (empty($people)): ?>
        <p style="color: var(--text-secondary); grid-column: 1 / -1; padding: 2rem 0; text-align: center;">No connections found in this tab.</p>
      <?php else: ?>
        <?php foreach ($people as $person): ?>
          <div class="person-card">
            <div class="person-top">
              <div class="avatar <?= $tab === 'connections' ? 'online' : '' ?>"><img src="<?= getAvatar($person['profile_picture'] ?? '', $person['name']) ?>" alt="<?= htmlspecialchars($person['name']) ?>"></div>
              <div>
                <div class="person-name"><?= htmlspecialchars($person['name']) ?></div>
                <div class="person-role"><?= htmlspecialchars($person['role'] ?? 'Member') ?><br><?= htmlspecialchars($person['company'] ?? 'EventDNA') ?></div>
              </div>
            </div>

            <div class="person-stats">
              <div class="stat-block" style="flex-direction: row; gap: 0.5rem; align-items: center;">
                <?php if ($tab === 'connections'): ?>
                  <span class="stat-tiny-label">Connected since</span>
                  <span class="stat-tiny-value"><?= date('M d, Y', strtotime($person['responded_at'] ?? $person['requested_at'])) ?></span>
                <?php elseif ($tab === 'received'): ?>
                  <span class="stat-tiny-value" style="font-weight: 600;">Wants to connect with you</span>
                <?php else: ?>
                  <span class="stat-tiny-label">Request sent &middot; <?= date('M d, Y', strtotime($person['requested_at'])) ?></span>
                <?php endif; ?>
              </div>
            </div>

            <div class="person-actions">
              <?php if ($tab === 'received'): ?>
                <form action="action.php" method="post" style="display:flex; width: 100%; gap: 0.5rem; margin-bottom: 0.75rem;">
                  <input type="hidden" name="connection_id" value="<?= $person['connection_id'] ?>">
                  <button type="submit" name="action" value="accept" class="btn-primary" style="flex:1; justify-content: center;">Accept</button>
                  <button type="submit" name="action" value="reject" class="btn-secondary" style="flex:1; justify-content: center;">Reject</button>
                </form>
              <?php else: ?>
                <a href="../NetworkHub/view_profile.php?id=<?= $person['profile_id'] ?>" class="btn-primary w-full" style="text-align: center; justify-content: center;">View Profile</a>
                <?php if ($tab === 'connections'): ?>
                  <form action="action.php" method="post" style="width:100%;">
                    <input type="hidden" name="connection_id" value="<?= $person['connection_id'] ?>">
                    <button type="submit" name="action" value="remove" class="btn-secondary w-full" style="justify-content: center;" onclick="return initCustomConfirm(this, 'Are you sure you want to remove this connection?', event);">Remove</button>
                  </form>
                <?php else: ?>
                  <button class="btn-secondary w-full" disabled style="justify-content: center; opacity: 0.6; cursor: default;">Pending</button>
                <?php endif; ?>
              <?php endif; ?>
            </div>
            
            <?php if ($tab === 'received'): ?>
              <a href="../NetworkHub/view_profile.php?id=<?= $person['profile_id'] ?>" class="btn-secondary w-full" style="text-align: center; justify-content: center; background: transparent; border: 1px solid var(--divider-color);">View Profile</a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- Custom Confirm Modal Setup -->
<style>
.custom-confirm-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);
    display: none; align-items: center; justify-content: center; z-index: 999999;
}
.custom-confirm-modal {
    background: #fff; padding: 2rem; border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    max-width: 400px; width: 90%; text-align: center;
    animation: confirmPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes confirmPop { from { transform: scale(0.95) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
.custom-confirm-modal h3 { margin: 0 0 1rem; color: #0f172a; font-size: 1.25rem; font-weight: 800; font-family: 'Inter', sans-serif; }
.custom-confirm-modal p { color: #64748b; margin-bottom: 2rem; font-size: 0.95rem; line-height: 1.5; font-family: 'Inter', sans-serif; }
.custom-confirm-actions { display: flex; gap: 1rem; justify-content: center; }
.custom-confirm-btn {
    padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; font-family: 'Inter', sans-serif; transition: all 0.2s;
}
.custom-confirm-cancel { background: #f1f5f9; color: #475569; }
.custom-confirm-cancel:hover { background: #e2e8f0; }
.custom-confirm-danger { background: #ef4444; color: #fff; }
.custom-confirm-danger:hover { background: #dc2626; transform: translateY(-1px); }
</style>
<div class="custom-confirm-overlay" id="customConfirmOverlay">
    <div class="custom-confirm-modal">
        <h3 id="customConfirmTitle">Are you sure?</h3>
        <p id="customConfirmMessage">This action cannot be undone.</p>
        <div class="custom-confirm-actions">
            <button class="custom-confirm-btn custom-confirm-cancel" id="customConfirmCancel">Cancel</button>
            <button class="custom-confirm-btn custom-confirm-danger" id="customConfirmOk">Yes, I'm sure</button>
        </div>
    </div>
</div>
<script>
let pendingConfirmAction = null;
function initCustomConfirm(el, message, e) {
    if (e) e.preventDefault();
    document.getElementById('customConfirmMessage').innerText = message;
    
    // Auto title based on message
    let title = "Are you sure?";
    if(message.toLowerCase().includes('delete')) title = "Confirm Deletion";
    else if(message.toLowerCase().includes('remove')) title = "Confirm Removal";
    document.getElementById('customConfirmTitle').innerText = title;

    document.getElementById('customConfirmOverlay').style.display = 'flex';
    
    pendingConfirmAction = () => {
        if (el.tagName === 'FORM') {
            // Remove the onsubmit handler so it doesn't trigger again, then submit
            el.onsubmit = null;
            el.submit();
        } else if (el.tagName === 'BUTTON' || el.tagName === 'A') {
            if(el.form) {
                el.form.onsubmit = null;
                el.form.submit();
            } else {
                // If there's an href, navigate
                if(el.href && el.href !== '#' && !el.href.startsWith('javascript:')) {
                    window.location.href = el.href;
                }
            }
        }
    };
    return false;
}
document.getElementById('customConfirmCancel').addEventListener('click', () => {
    document.getElementById('customConfirmOverlay').style.display = 'none';
    pendingConfirmAction = null;
});
document.getElementById('customConfirmOk').addEventListener('click', () => {
    document.getElementById('customConfirmOverlay').style.display = 'none';
    if(pendingConfirmAction) pendingConfirmAction();
});
</script>
</body>
</html>