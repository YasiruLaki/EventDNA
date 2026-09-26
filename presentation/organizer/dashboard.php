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

  <link rel="stylesheet" href="organizer.css" />
</head>

<body>
  <?php include 'includes/nav.php'; ?>

  <div class="dashboard-shell">
    <main class="dashboard-content">
      <section class="hero-row hero-header org-hero-header" >
        <div class="hero-copy">
          <h1 class="page-title"><?= $greeting ?>, <?= h($firstName) ?>!</h1>
          <p class="supporting-copy">
            Manage your events, registrations, and attendance.
          </p>
        </div>
      </section>

      <section class="stats-grid org-stats-grid" aria-label="Overview stats"
        >
        <article class="stat-card org-stat-card-pad" >
          <div class="stat-content">
            <h2 class="stat-value org-stat-value" ><?= number_format($stats['total']) ?></h2>
            <p class="stat-label org-stat-label" >My
              Events</p>
          </div>
        </article>

        <article class="stat-card org-stat-card-pad" >
          <div class="stat-content">
            <h2 class="stat-value org-stat-value" ><?= number_format($stats['upcoming']) ?></h2>
            <p class="stat-label org-stat-label" >
              Upcoming Events</p>
          </div>
        </article>

        <article class="stat-card org-stat-card-pad" >
          <div class="stat-content">
            <h2 class="stat-value org-stat-value" ><?= number_format($stats['registrations']) ?></h2>
            <p class="stat-label org-stat-label" >Total
              Registrations</p>
          </div>
        </article>
      </section>

      <?php if ($active): ?>
      <div class="org-mt-3-5" >
        <h2 class="org-section-title" ><?= $active['display_status'] === 'Live' ? 'Currently Active' : 'Next Up' ?></h2>

        <article style="display: flex; flex-wrap: wrap; background: #ffffff; border-radius: 16px; box-shadow: 0 12px 32px -4px rgba(0,0,0,0.04), 0 4px 12px -2px rgba(0,0,0,0.02); overflow: hidden; align-items: center; transition: transform 0.2s, box-shadow 0.2s;">
          
          <?php if (!empty($active['cover_photo'])): ?>
            <div style="flex: 1 1 320px; padding: 1.25rem;">
              <div style="width: 100%; height: 220px; border-radius: 12px; background: url('../../<?= htmlspecialchars($active['cover_photo']) ?>') center center / cover no-repeat; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05);"></div>
            </div>
          <?php endif; ?>

          <div style="flex: 1.5 1 400px; padding: <?= !empty($active['cover_photo']) ? '1.5rem 2.5rem 1.5rem 0.5rem' : '2.5rem' ?>; display: flex; flex-direction: column; gap: 1.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
              <div>
                <h3 style="font-size: 1.6rem; font-weight: 800; color: var(--secondary); margin: 0 0 0.4rem 0; letter-spacing: -0.01em;"><?= h($active['name']) ?></h3>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem; font-weight: 500;"><?= h(format_event_date($active['event_date'])) ?> · <?= h($active['location']) ?></p>
              </div>
              <span style="<?= status_badge_style($active['display_status']) ?> font-size: 0.75rem; font-weight: 800; padding: 0.4rem 0.8rem; border-radius: 999px; letter-spacing: 0.05em; text-transform: uppercase;">
                <?= h($active['display_status']) ?>
              </span>
            </div>

            <div>
              <?php
                $fillBase = $active['display_status'] === 'Live' ? (int)$active['registered_count'] : (int)$active['capacity'];
                $fillValue = $active['display_status'] === 'Live' ? (int)$active['checked_in_count'] : (int)$active['registered_count'];
                $fillPercent = $fillBase > 0 ? min(100, round($fillValue / $fillBase * 100)) : 0;
              ?>
              <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.6rem;">
                <span><?= (int)$active['registered_count'] ?> registered</span>
                <span><?= $active['display_status'] === 'Live' ? (int)$active['checked_in_count'] . ' checked in' : (int)$active['capacity'] . ' capacity' ?></span>
              </div>
              <div style="height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; display: flex;">
                <div style="width: <?= $fillPercent ?>%; background: var(--primary); border-radius: 999px;"></div>
              </div>
            </div>

            <div style="display: flex; gap: 0.75rem;">
              <a href="event-details.php?id=<?= (int)$active['event_id'] ?>" class="btn-primary" style="padding: 0.6rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: 6px;">View Event</a>
              <a href="event-details.php?id=<?= (int)$active['event_id'] ?>#qr" style="padding: 0.6rem 1.25rem; font-size: 0.9rem; font-weight: 600; border-radius: 6px; background: rgba(79, 16, 255, 0.1); color: var(--primary); text-decoration: none; transition: background 0.2s;">Check-in</a>
            </div>
          </div>
        </article>
      </div>
      <?php endif; ?>

      <div class="org-mt-4" id="community" >
        <div class="org-flex-between-end" >
          <div>
            <h2 class="org-event-title" >Community
            </h2>
            <p class="org-event-meta" >Create and manage groups for your
              attendees.</p>
          </div>
          <div class="org-flex-gap-1" >
            <a href="create-group.html" class="btn-primary">+ Create Group</a>
            <a href="manage-groups.html" class="btn-secondary">Manage Groups</a>
          </div>
        </div>
      </div>

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
            <h2 class="org-event-title" >Your Events
            </h2>
            <p class="org-event-meta" >Manage the events you've created.</p>
          </div>
          <a href="create-event.php" class="btn-primary">+ Create Event</a>
        </div>

        <div class="org-card-base"
          >
          <?php if (!$events): ?>
            <div class="org-empty-state-pad" >
              <p class="org-empty-state-text" >You haven't created any events yet.</p>
              <a href="create-event.php" class="btn-primary">Create your first event</a>
            </div>
          <?php else: ?>
          <div class="org-list-item"
            >
            <div class="org-relative-flex-1" >
              <i class="org-icon-left" data-lucide="search"
                ></i>
              <input class="org-input-with-icon" type="text" id="eventSearch" placeholder="Search events..." aria-label="Search events"
                >
            </div>
            <select class="org-input-default" id="statusFilter" aria-label="Filter by status"
              >
              <option value="">All statuses</option>
              <option>Live</option>
              <option>Upcoming</option>
              <option>Completed</option>
              <option>Cancelled</option>
            </select>
          </div>

          <div class="org-overflow-x-auto" >
          <table class="org-table-base" >
            <thead>
              <tr class="org-table-header"
                >
                <th class="org-table-cell-pad" >Event</th>
                <th class="org-table-cell-pad" >Date</th>
                <th class="org-table-cell-pad" >Registrations</th>
                <th class="org-table-cell-pad" >Status</th>
                <th class="org-table-cell-pad" >Action</th>
              </tr>
            </thead>
            <tbody class="org-text-secondary-md" id="eventRows" >
              <?php foreach ($events as $event): ?>
                <?php $muted = in_array($event['display_status'], ['Completed', 'Cancelled'], true); ?>
                <tr data-name="<?= h(mb_strtolower($event['name'] . ' ' . $event['location'])) ?>" data-status="<?= h($event['display_status']) ?>"
                  style="border-bottom: 1px solid rgba(226, 232, 240, 0.9);">
                  <td style="padding: 1.25rem 1.5rem; font-weight: 600;<?= $muted ? ' color: var(--text-secondary);' : '' ?>"><?= h($event['name']) ?></td>
                  <td class="org-table-cell-nowrap" ><?= h(date('M j, Y', strtotime($event['event_date']))) ?></td>
                  <td class="org-font-medium-pad" ><?= (int)$event['registered_count'] ?> / <?= (int)$event['capacity'] ?></td>
                  <td class="org-table-cell-default" >
                    <span style="<?= status_badge_style($event['display_status']) ?> font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.6rem; border-radius: 6px;"><?= h($event['display_status']) ?></span>
                  </td>
                  <td class="org-table-cell-default" ><a href="event-details.php?id=<?= (int)$event['event_id'] ?>" class="btn-secondary"
                      style="padding: 0.4rem 1rem; font-size: 0.85rem; font-weight: 600;">Manage</a></td>
                </tr>
              <?php endforeach; ?>
              <tr class="org-hidden" id="noMatches" >
                <td class="org-text-center-pad" colspan="5" >No events match your search.</td>
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
