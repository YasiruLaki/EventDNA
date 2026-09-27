<?php 
require_once "includes/guard.php"; 
require_once "../../data/database.php";
require_once "../../application/controllers/AdminController.php";

$controller = new AdminController($conn);
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$events = $controller->getAllEvents($search, $statusFilter);
?>
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

      <form method="GET" class="filters-bar" style="display: flex; gap: 1rem; margin-bottom: 2rem;">
        <div class="search-box" style="flex: 1; display: flex; align-items: center; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 0 1rem;">
          <svg style="color: var(--text-secondary); width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          <input type="text" name="search" value="<?= h($search) ?>" placeholder="Search events or organizers..." style="border: none; outline: none; padding: 0.75rem; width: 100%;" />
        </div>
        <select class="filter-select" name="status" style="padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; outline: none;" onchange="this.form.submit()">
          <option value="">All Statuses</option>
          <option value="ACTIVE" <?= $statusFilter === 'ACTIVE' ? 'selected' : '' ?>>Active</option>
          <option value="CANCELLED" <?= $statusFilter === 'CANCELLED' ? 'selected' : '' ?>>Cancelled</option>
        </select>
      </form>

      <div style="overflow-x: auto; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border-color);">
        <table class="data-table" style="width: 100%; border-collapse: collapse; text-align: left;">
          <thead>
            <tr style="border-bottom: 1px solid var(--border-color); background: #f8fafc;">
              <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Event</th>
              <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Organizer</th>
              <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Event Date</th>
              <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Status</th>
              <th style="padding: 1rem 1.5rem; font-weight: 600; color: var(--text-secondary); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Cancellation Reason</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($events as $ev): ?>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 1.25rem 1.5rem; font-weight: 600; color: var(--secondary);"><?= h($ev['name']) ?></td>
              <td style="padding: 1.25rem 1.5rem; color: var(--text-secondary);"><?= h($ev['organizer_name']) ?></td>
              <td style="padding: 1.25rem 1.5rem; color: var(--text-secondary);"><?= h(date('M d, Y', strtotime($ev['event_date']))) ?> at <?= h(date('h:i A', strtotime($ev['start_time']))) ?></td>
              <td style="padding: 1.25rem 1.5rem;">
                  <?php if ($ev['status'] === 'ACTIVE'): ?>
                      <span class="status-badge" style="background: rgba(34, 197, 94, 0.1); color: var(--success); padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">Active</span>
                  <?php elseif ($ev['status'] === 'CANCELLED'): ?>
                      <span class="status-badge" style="background: rgba(220, 38, 38, 0.1); color: var(--danger); padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">Cancelled</span>
                  <?php else: ?>
                      <span class="status-badge" style="background: #f1f5f9; color: var(--text-secondary); padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700;"><?= h(ucfirst(strtolower($ev['status']))) ?></span>
                  <?php endif; ?>
              </td>
              <td style="padding: 1.25rem 1.5rem; color: var(--text-secondary); font-size: 0.9rem;">
                  <?= $ev['status'] === 'CANCELLED' && $ev['cancellation_reason'] ? h($ev['cancellation_reason']) : '<span style="color: #cbd5e1;">—</span>' ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($events)): ?>
                <tr>
                    <td colspan="5" style="padding: 2rem; text-align: center; color: var(--text-secondary);">No events found.</td>
                </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>