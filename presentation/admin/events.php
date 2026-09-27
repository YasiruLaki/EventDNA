<?php require_once "includes/guard.php"; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Event Management - EventDNA Admin</title>
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
        <a href="dashboard.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg> Dashboard</a>
        <a href="users.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Users</a>
        <a href="events.php" class="admin-nav-item active"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg> Events</a>
        <a href="moderation.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg> Moderation</a>
        <a href="analytics.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg> Analytics</a>
      </nav>
      <div class="admin-footer">
        <a href="logout.php" class="admin-nav-item" style="color: var(--danger);"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg> Log Out</a>
      </div>
    </aside>

    <main class="admin-content">
      <div class="page-header">
        <h1 class="page-title">All Events</h1>
      </div>

      <div class="filters-bar">
        <div class="search-box">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" placeholder="Search events..." />
        </div>
        <select class="filter-select">
          <option>All Statuses</option>
          <option>Live</option>
          <option>Upcoming</option>
          <option>Draft</option>
        </select>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>Event</th>
            <th>Organizer</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="font-weight: 600;">AI Innovation Summit 2026</td>
            <td>Hanan Perera</td>
            <td>Oct 24</td>
            <td><span class="status-badge status-live">Live</span></td>
            <td><a href="event-details.php" class="btn-view">View</a></td>
          </tr>
          <tr>
            <td style="font-weight: 600;">Career Fair</td>
            <td>Kamal Silva</td>
            <td>Dec 03</td>
            <td><span class="status-badge status-upcoming">Upcoming</span></td>
            <td><a href="event-details.php" class="btn-view">View</a></td>
          </tr>
          <tr>
            <td style="font-weight: 600;">Design Masterclass</td>
            <td>Sarah Fernando</td>
            <td>Jan 18</td>
            <td><span class="status-badge status-draft">Draft</span></td>
            <td><a href="event-details.php" class="btn-view">View</a></td>
          </tr>
        </tbody>
      </table>
    </main>
  </div>

  </body>
</html>
