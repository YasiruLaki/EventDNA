<?php
$activeNav = $activeNav ?? '';
$attendeeName = $_SESSION['full_name'] ?? 'Attendee';
?>
<nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="../../events/ExploreEvents/index.html" class="nav-logo">
          <img src="../../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="../../events/ExploreEvents/index.html" class="nav-link <?= $activeNav === 'find' ? 'active' : '' ?>">Find Events</a>
          <a href="../../events/myEvents/index.html" class="nav-link <?= $activeNav === 'myevents' ? 'active' : '' ?>">My Events</a>
          <a href="../groups/" class="nav-link <?= $activeNav === 'communities' ? 'active' : '' ?>">Communities</a>
          <a href="../attendee/myConnections/index.html" class="nav-link <?= $activeNav === 'connections' ? 'active' : '' ?>">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;"><?= substr($attendeeName, 0, 1) ?></div>
            <span class="nav-profile-name"><?= htmlspecialchars($attendeeName) ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="nav-dropdown">
            <a href="../attendee/settings/index.html" class="dropdown-item">Settings</a>
            <hr class="dropdown-divider">
            <a href="../../auth/logout.php" class="dropdown-item text-danger">Log out</a>
          </div>
        </div>
      </div>
    </div>
</nav>
