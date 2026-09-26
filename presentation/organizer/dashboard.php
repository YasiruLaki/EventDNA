<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../data/database.php";
require_once "../../application/controllers/EventController.php";

$eventController = new EventController($conn);
$dashboard = $eventController->getOrganizerDashboard($organizerId);
$events = $dashboard["events"];
$active = $dashboard["active"];
$stats = $dashboard["stats"];

$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
$firstName = explode(' ', trim($organizerName))[0];
$activeNav = 'dashboard';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Organizer Dashboard - EventDNA</title>
  <link rel="stylesheet" href="../attendee/dashboard/styles.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>
  <nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="#" class="nav-logo">
          <img src="../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="dashboard.php" class="nav-link active"
            style="box-shadow: inset 0 -2px 0 var(--primary); color: var(--primary); font-weight: 600;">Dashboard</a>
          <a href="dashboard.php#events" class="nav-link">My Events</a>
          <a href="create-event.html" class="nav-link">Create Event</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&q=80"
              alt="Profile" class="nav-avatar" />
            <span class="nav-profile-name">Hanan</span>
            <i data-lucide="chevron-down" style="width:16px;height:16px;"></i>
          </button>
          <div class="nav-dropdown">
            <a href="../auth/login/index.php" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="dashboard-shell">
    <main class="dashboard-content">
      <section class="hero-row hero-header" style="margin-bottom: 2rem;">
        <div class="hero-copy">
          <h1 class="page-title"><?= $greeting ?>, <?= h($firstName) ?>!</h1>
          <p class="supporting-copy">
            Manage your events, registrations, and attendance.
          </p>
        </div>
      </section>

      <section class="stats-grid" aria-label="Overview stats"
        style="grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
        <article class="stat-card" style="padding: 1.5rem;">
          <div class="stat-content">
            <h2 class="stat-value" style="font-size: 2rem; margin: 0; color: var(--secondary);"><?= number_format($stats['total']) ?></h2>
            <p class="stat-label" style="margin: 0.25rem 0 0 0; color: var(--text-secondary); font-size: 0.95rem;">My
              Events</p>
          </div>
        </article>

        <article class="stat-card" style="padding: 1.5rem;">
          <div class="stat-content">
            <h2 class="stat-value" style="font-size: 2rem; margin: 0; color: var(--secondary);"><?= number_format($stats['upcoming']) ?></h2>
            <p class="stat-label" style="margin: 0.25rem 0 0 0; color: var(--text-secondary); font-size: 0.95rem;">
              Upcoming Events</p>
          </div>
        </article>

        <article class="stat-card" style="padding: 1.5rem;">
          <div class="stat-content">
            <h2 class="stat-value" style="font-size: 2rem; margin: 0; color: var(--secondary);"><?= number_format($stats['registrations']) ?></h2>
            <p class="stat-label" style="margin: 0.25rem 0 0 0; color: var(--text-secondary); font-size: 0.95rem;">Total
              Registrations</p>
          </div>
        </article>
      </section>

      <?php if ($active): ?>
      <div style="margin-top: 3.5rem;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--secondary); margin-bottom: 1.5rem;"><?= $active['display_status'] === 'Live' ? 'Currently Active' : 'Next Up' ?></h2>

        <article class="stat-card"
          style="padding: 2.5rem; border-left: 4px solid var(--primary); display: flex; flex-direction: column; gap: 2rem; background: rgba(255, 255, 255, 0.95);">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
            <div>
              <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--secondary); margin: 0 0 0.5rem 0;"><?= h($active['name']) ?></h3>
              <p style="color: var(--text-secondary); margin: 0; font-size: 1rem;"><?= h(format_event_date($active['event_date'])) ?> · <?= h($active['location']) ?></p>
            </div>
            <span
              style="<?= status_badge_style($active['display_status']) ?> font-size: 0.85rem; font-weight: 800; padding: 0.5rem 0.85rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 0.4rem; text-transform: uppercase;">
              <?php if ($active['display_status'] === 'Live'): ?>
                <span style="display: inline-block; width: 8px; height: 8px; background: var(--danger); border-radius: 50%;"></span>
              <?php endif; ?>
              <?= h($active['display_status']) ?>
            </span>
          </div>

          <div>
            <?php
              $fillBase = $active['display_status'] === 'Live' ? (int)$active['registered_count'] : (int)$active['capacity'];
              $fillValue = $active['display_status'] === 'Live' ? (int)$active['checked_in_count'] : (int)$active['registered_count'];
              $fillPercent = $fillBase > 0 ? min(100, round($fillValue / $fillBase * 100)) : 0;
            ?>
            <div
              style="display: flex; justify-content: space-between; font-size: 0.95rem; font-weight: 600; color: var(--secondary); margin-bottom: 0.75rem;">
              <span><?= (int)$active['registered_count'] ?> registered</span>
              <span><?= $active['display_status'] === 'Live' ? (int)$active['checked_in_count'] . ' checked in' : (int)$active['capacity'] . ' capacity' ?></span>
            </div>
            <div style="height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden; display: flex;">
              <div style="width: <?= $fillPercent ?>%; background: var(--primary); border-radius: 999px;"></div>
            </div>
          </div>

          <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
            <a href="event-details.php?id=<?= (int)$active['event_id'] ?>" class="btn-primary" style="padding: 0.75rem 1.5rem; font-size: 0.95rem;">View
              Event</a>
            <a href="event-details.php?id=<?= (int)$active['event_id'] ?>#qr" class="btn-secondary"
              style="padding: 0.75rem 1.5rem; font-size: 0.95rem;">Check-in</a>
          </div>
        </article>
      </div>
      <?php endif; ?>

      <!-- ===== COMMUNITY SECTION — new, per Organizer Create Group Implementation Plan section 4 ===== -->
      <div id="community" style="margin-top: 4rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem;">
          <div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--secondary); margin: 0 0 0.5rem 0;">Community
            </h2>
            <p style="color: var(--text-secondary); margin: 0; font-size: 1rem;">Create and manage groups for your
              attendees.</p>
          </div>
          <div style="display: flex; gap: 1rem;">
            <a href="create-group.html" class="btn-primary">+ Create Group</a>
            <a href="manage-groups.html" class="btn-secondary">Manage Groups</a>
          </div>
        </div>
      </div>

      <div id="events" style="margin-top: 4rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem;">
          <div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--secondary); margin: 0 0 0.5rem 0;">Your Events
            </h2>
            <p style="color: var(--text-secondary); margin: 0; font-size: 1rem;">Manage the events you've created.</p>
          </div>
          <a href="create-event.php" class="btn-primary">+ Create Event</a>
        </div>

        <div
          style="background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(226, 232, 240, 0.9); border-radius: 12px; overflow: hidden;">
          <?php if (!$events): ?>
            <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-secondary);">
              <p style="margin: 0 0 1rem 0; font-size: 1rem;">You haven't created any events yet.</p>
              <a href="create-event.php" class="btn-primary">Create your first event</a>
            </div>
          <?php else: ?>
          <div
            style="padding: 1.5rem; border-bottom: 1px solid rgba(226, 232, 240, 0.9); display: flex; gap: 1rem; align-items: center; background: #fff; flex-wrap: wrap;">
            <div style="position: relative; flex: 1; max-width: 350px; min-width: 200px;">
              <i data-lucide="search"
                style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--text-tertiary);"></i>
              <input type="text" id="eventSearch" placeholder="Search events..." aria-label="Search events"
                style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.5rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #f8fafc; color: var(--secondary);">
            </div>
            <select id="statusFilter" aria-label="Filter by status"
              style="padding: 0.75rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 0.95rem; background: #f8fafc; color: var(--secondary); outline: none;">
              <option value="">All statuses</option>
              <option>Live</option>
              <option>Upcoming</option>
              <option>Completed</option>
              <option>Cancelled</option>
            </select>
          </div>

          <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
              <tr
                style="background: rgba(248, 250, 252, 0.8); border-bottom: 1px solid rgba(226, 232, 240, 0.9); font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                <th style="padding: 1rem 1.5rem;">Event</th>
                <th style="padding: 1rem 1.5rem;">Date</th>
                <th style="padding: 1rem 1.5rem;">Registrations</th>
                <th style="padding: 1rem 1.5rem;">Status</th>
                <th style="padding: 1rem 1.5rem;">Action</th>
              </tr>
            </thead>
            <tbody id="eventRows" style="font-size: 0.95rem; color: var(--secondary);">
              <?php foreach ($events as $event): ?>
                <?php $muted = in_array($event['display_status'], ['Completed', 'Cancelled'], true); ?>
                <tr data-name="<?= h(mb_strtolower($event['name'] . ' ' . $event['location'])) ?>" data-status="<?= h($event['display_status']) ?>"
                  style="border-bottom: 1px solid rgba(226, 232, 240, 0.9);">
                  <td style="padding: 1.25rem 1.5rem; font-weight: 600;<?= $muted ? ' color: var(--text-secondary);' : '' ?>"><?= h($event['name']) ?></td>
                  <td style="padding: 1.25rem 1.5rem; color: var(--text-secondary); white-space: nowrap;"><?= h(date('M j, Y', strtotime($event['event_date']))) ?></td>
                  <td style="padding: 1.25rem 1.5rem; font-weight: 500;"><?= (int)$event['registered_count'] ?> / <?= (int)$event['capacity'] ?></td>
                  <td style="padding: 1.25rem 1.5rem;">
                    <span style="<?= status_badge_style($event['display_status']) ?> font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.6rem; border-radius: 6px;"><?= h($event['display_status']) ?></span>
                  </td>
                  <td style="padding: 1.25rem 1.5rem;"><a href="event-details.php?id=<?= (int)$event['event_id'] ?>" class="btn-secondary"
                      style="padding: 0.4rem 1rem; font-size: 0.85rem; font-weight: 600;">Manage</a></td>
                </tr>
              <?php endforeach; ?>
              <tr id="noMatches" style="display: none;">
                <td colspan="5" style="padding: 2rem 1.5rem; text-align: center; color: var(--text-secondary);">No events match your search.</td>
              </tr>
            </tbody>
          </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </div>

  <script>
    lucide.createIcons();

    const profileBtn = document.querySelector('.nav-profile-btn');
    const profileDropdown = document.querySelector('.nav-dropdown');

    if (profileBtn && profileDropdown) {
      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
      });

      document.addEventListener('click', () => {
        profileDropdown.classList.remove('show');
      });
    }

    // Search and status filter for the events table
    const search = document.getElementById('eventSearch');
    const statusFilter = document.getElementById('statusFilter');

    function filterEvents() {
      const q = search.value.trim().toLowerCase();
      const status = statusFilter.value;
      let visible = 0;
      document.querySelectorAll('#eventRows tr[data-name]').forEach(row => {
        const show = row.dataset.name.includes(q) && (!status || row.dataset.status === status);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
      });
      document.getElementById('noMatches').style.display = visible ? 'none' : '';
    }

    if (search && statusFilter) {
      search.addEventListener('input', filterEvents);
      statusFilter.addEventListener('change', filterEvents);
    }
  </script>
</body>

</html>
