<?php
// Top navigation for attendee event pages. Set $activeNav to 'explore' or 'mine' before including.
$activeNav = $activeNav ?? '';
?>
  <nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="../../attendee/dashboard/index.php" class="nav-logo">
          <img src="../../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="../ExploreEvents/index.php" class="nav-link<?= $activeNav === 'explore' ? ' active' : '' ?>">Find Events</a>
          <a href="../myEvents/index.php" class="nav-link<?= $activeNav === 'mine' ? ' active' : '' ?>">My Events</a>
          <a href="../../attendee/community/community-hub/index.html" class="nav-link">Communities</a>
          <a href="../../attendee/myConnections/index.html" class="nav-link">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <span class="nav-avatar" style="display: inline-flex; align-items: center; justify-content: center; background: var(--primary); color: #fff; font-weight: 700; font-size: 0.85rem;"><?= h(mb_strtoupper(mb_substr($attendeeName, 0, 1))) ?></span>
            <span class="nav-profile-name"><?= h($attendeeName) ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="nav-dropdown">
            <a href="../../attendee/onboarding/index.php" class="dropdown-item">Profile</a>
            <a href="../../auth/logout.php" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>
