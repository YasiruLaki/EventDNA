<?php
// Top navigation for organizer pages. Set $activeNav to 'dashboard', 'events' or 'create' before including.
$activeNav = $activeNav ?? '';
$organizerName = $organizerName ?? 'Organizer';
$activeStyle ='style="box-shadow: inset 0 -2px 0 var(--primary); color: var(--primary); font-weight: 600;"';
?>
  <nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="dashboard.php" class="nav-logo">
          <img src="../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="dashboard.php" class="nav-link<?= $activeNav === 'dashboard' ? ' active' : '' ?>" <?= $activeNav === 'dashboard' ? $activeStyle : '' ?>>Dashboard</a>
          <a href="dashboard.php#events" class="nav-link<?= $activeNav === 'events' ? ' active' : '' ?>" <?= $activeNav === 'events' ? $activeStyle : '' ?>>My Events</a>
          <a href="create-event.php" class="nav-link<?= $activeNav === 'create' ? ' active' : '' ?>" <?= $activeNav === 'create' ? $activeStyle : '' ?>>Create Event</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <span class="nav-avatar" style="display: inline-flex; align-items: center; justify-content: center; background: var(--primary); color: #fff; font-weight: 700; font-size: 0.85rem;"><?= h(mb_strtoupper(mb_substr($organizerName, 0, 1))) ?></span>
            <span class="nav-profile-name"><?= h($organizerName) ?></span>
            <i data-lucide="chevron-down" style="width:16px;height:16px;"></i>
          </button>
          <div class="nav-dropdown">
            <a href="../auth/logout.php" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>
