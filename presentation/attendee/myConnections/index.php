<?php
session_start();
require_once __DIR__ . '/../../attendee/includes/avatar.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}
$attendeeName = $_SESSION['full_name'] ?? 'Attendee';
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
          <button class="btn-secondary w-full" style="justify-content: center;">Remove</button>
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

</body>
</html>