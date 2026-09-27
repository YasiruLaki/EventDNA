<?php

require_once __DIR__ . '/../includes/guard.php';

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
          <a href="../community/community-hub/index.php" class="nav-link">Communities</a>
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
        <div class="stat-num">46</div>
        <div class="stat-label">Total Connections</div>
      </div>
      <div class="stat-card accent">
        <div class="stat-num">4</div>
        <div class="stat-label">Pending Requests</div>
      </div>
    </div>

    <!-- Tabs & Toolbar -->
    <div class="connections-control-bar">
      <div class="tabs">
        <span class="tab active">Connections</span>
        <span class="tab">Received</span>
        <span class="tab">Sent</span>
      </div>

      <div class="toolbar">
        <div class="search-input">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><line x1="21" y1="21" x2="16.5" y2="16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          <input type="text" placeholder="Search people...">
        </div>
      </div>
    </div>

    <!-- Connection cards -->
    <div class="connections-grid">

      <!-- Established Connection -->
      <div class="person-card">
        <div class="person-top">
          <div class="avatar avatar-2 online">SJ</div>
          <div>
            <div class="person-name">Sarah Jenkins</div>
            <div class="person-role">Lead Researcher<br>BioGen</div>
          </div>
        </div>

        <div class="person-stats">
          <div class="stat-block" style="flex-direction: row; gap: 0.5rem; align-items: center;">
            <span class="stat-tiny-label">Connected since</span>
            <span class="stat-tiny-value">Nov 05, 2026</span>
          </div>
        </div>

        <div class="person-actions">
          <a href="#" class="btn-primary w-full" style="text-align: center; justify-content: center;">View Profile</a>
          <button class="btn-secondary w-full" style="justify-content: center;" onclick="return initCustomConfirm(this, 'Are you sure you want to remove this item?', event);">Remove</button>
        </div>
      </div>

      <!-- Received Request -->
      <div class="person-card">
        <div class="person-top">
          <div class="avatar avatar-3">JD</div>
          <div>
            <div class="person-name">James Doe</div>
            <div class="person-role">Software Engineer<br>CloudSync</div>
          </div>
        </div>

        <div class="person-stats">
          <div class="stat-block" style="flex-direction: row; gap: 0.5rem; align-items: center;">
            <span class="stat-tiny-value" style="font-weight: 600;">Wants to connect with you</span>
          </div>
        </div>

        <div class="person-actions" style="margin-bottom: 0.75rem;">
          <button class="btn-primary w-full" style="justify-content: center;">Accept</button>
          <button class="btn-secondary w-full" style="justify-content: center;">Reject</button>
        </div>
        <a href="#" class="btn-secondary w-full" style="text-align: center; justify-content: center; background: transparent; border: 1px solid var(--divider-color);">View Profile</a>
      </div>

      <!-- Sent Request -->
      <div class="person-card">
        <div class="person-top">
          <div class="avatar avatar-1">ER</div>
          <div>
            <div class="person-name">Elena Rostova</div>
            <div class="person-role">Product Manager<br>TechFlow</div>
          </div>
        </div>

        <div class="person-stats">
          <div class="stat-block" style="flex-direction: row; gap: 0.5rem; align-items: center;">
            <span class="stat-tiny-label">Request sent &middot; 2 days ago</span>
          </div>
        </div>

        <div class="person-actions">
          <a href="#" class="btn-primary w-full" style="text-align: center; justify-content: center;">View Profile</a>
          <button class="btn-secondary w-full" disabled style="justify-content: center; opacity: 0.6; cursor: default;">Pending</button>
        </div>
      </div>

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