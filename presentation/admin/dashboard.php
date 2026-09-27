<?php require_once "includes/guard.php"; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - EventDNA</title>
  <link rel="stylesheet" href="../../globals.css" />
  <link rel="stylesheet" href="./styles.css" />
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="admin-logo">
        <img src="../images/logo.png" alt="EventDNA" />
        <span style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--primary); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 0.25rem;">Admin</span>
      </div>
      <nav class="admin-nav">
        <a href="dashboard.php" class="admin-nav-item active"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg> Dashboard</a>
        <a href="users.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Users</a>
        <a href="events.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg> Events</a>
        <a href="moderation.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg> Moderation</a>
        <a href="analytics.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg> Analytics</a>
      </nav>
      <div class="admin-footer">
        <a href="logout.php" class="admin-nav-item" style="color: var(--danger);"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg> Log Out</a>
      </div>
    </aside>

    <main class="admin-content">
      <h1 class="page-title">Good Evening, Admin!</h1>
      <p class="supporting-copy">Here's what's happening across EventDNA.</p>

      <div class="stats-grid">
        <article class="stat-card">
          <h2 class="stat-value">1,248</h2>
          <p class="stat-label">Users</p>
        </article>
        <article class="stat-card">
          <h2 class="stat-value">86</h2>
          <p class="stat-label">Events</p>
        </article>
        <article class="stat-card">
          <h2 class="stat-value">4,921</h2>
          <p class="stat-label">Registrations</p>
        </article>
      </div>

      <div class="stats-grid">
        <article class="stat-card">
          <h2 class="stat-value">3,842</h2>
          <p class="stat-label">Check-ins</p>
        </article>
        <article class="stat-card">
          <h2 class="stat-value">324</h2>
          <p class="stat-label">Connections</p>
        </article>
        <article class="stat-card">
          <h2 class="stat-value">42</h2>
          <p class="stat-label">Communities</p>
        </article>
      </div>

      <div class="activity-card">
        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--secondary); margin: 0 0 1.5rem 0;">Recent System Activity</h3>
        
        <div class="activity-item">
          <span class="activity-text">User registered</span>
          <span class="activity-time">2 min ago</span>
        </div>
        <div class="activity-item">
          <span class="activity-text">New event created</span>
          <span class="activity-time">15 min ago</span>
        </div>
        <div class="activity-item">
          <span class="activity-text">Event cancelled</span>
          <span class="activity-time">1 hr ago</span>
        </div>
        <div class="activity-item">
          <span class="activity-text" style="color: var(--danger);">Post reported</span>
          <span class="activity-time">2 hrs ago</span>
        </div>
      </div>
    </main>
  </div>

  </body>
</html>
