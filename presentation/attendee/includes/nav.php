<?php
require_once __DIR__ . '/avatar.php';
$activeNav = $activeNav ?? '';
$attendeeName = $_SESSION['full_name'] ?? 'Attendee';
?>
<nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="../../events/ExploreEvents/index.php" class="nav-logo">
          <img src="../../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="../../events/ExploreEvents/index.php" class="nav-link <?= $activeNav === 'find' ? 'active' : '' ?>">Find Events</a>
          <a href="../../events/myEvents/index.php" class="nav-link <?= $activeNav === 'myevents' ? 'active' : '' ?>">My Events</a>
          <a href="../community/index.php" class="nav-link <?= $activeNav === 'communities' ? 'active' : '' ?>">Communities</a>
          <a href="../myConnections/index.php" class="nav-link <?= $activeNav === 'connections' ? 'active' : '' ?>">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <?= nav_avatar_html($attendeeName) ?>
            <span class="nav-profile-name"><?= htmlspecialchars($attendeeName) ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="nav-dropdown">
            <hr class="dropdown-divider">
            <a href="../../auth/logout/index.php" class="dropdown-item text-danger">Log out</a>
          </div>
        </div>
      </div>
    </div>
</nav>
