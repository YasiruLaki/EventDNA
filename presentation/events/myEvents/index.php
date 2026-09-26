<?php
require_once __DIR__ . "/../includes/guard.php";
require_once "../../../data/database.php";
require_once "../../../application/controllers/EventController.php";

$eventController = new EventController($conn);
$myEvents = $eventController->getAttendeeEvents($attendeeId);
$activeNav = 'mine';

const CALENDAR_ICON = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>';
const PIN_ICON = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>';

function thumb_style($event, $dim = false) {
    $background = $event['cover_photo']
        ? "url('" . h(cover_url($event['cover_photo'])) . "') center/cover"
        : "linear-gradient(135deg, #6D3BFF 0%, #2A0BB0 100%)";
    return 'background: ' . $background . ';' . ($dim ? ' opacity: 0.85;' : '');
}

function starts_in($event) {
    if ($event['display_status'] === 'Live') {
        return 'Happening now';
    }
    $days = (new DateTime(date('Y-m-d')))->diff(new DateTime($event['event_date']))->days;
    if ($days === 0) {
        return 'Starts today at ' . format_time($event['start_time']);
    }
    return $days === 1 ? 'Starts tomorrow' : 'Starts in ' . $days . ' days';
}

function upcoming_card($event, $layout) {
    $pending = $event['registration_status'] === 'PENDING';
    ?>
      <div class="event-card-<?= $layout ?>" data-title="<?= h(mb_strtolower($event['name'])) ?>">
        <div class="event-thumb" style="<?= thumb_style($event) ?>">
          <div class="event-thumb-brand">EventDNA</div>
        </div>

        <div class="event-body">
          <div class="event-info-top">
            <div class="tag-row">
              <?php if ($event['primary_interest']): ?>
                <span class="tag"><?= h($event['primary_interest']) ?></span>
              <?php endif; ?>
              <?php if ($event['display_status'] === 'Live'): ?>
                <span class="tag">Live</span>
              <?php endif; ?>
            </div>
            <span class="registered-badge"<?= $pending ? ' style="background: rgba(245, 158, 11, 0.12); color: #b45309;"' : '' ?>><?= $pending ? 'PENDING APPROVAL' : 'REGISTERED' ?></span>
          </div>

          <h3><?= h($event['name']) ?></h3>

          <div class="event-meta">
            <span class="meta-item">
              <?= CALENDAR_ICON ?>
              <?= h(format_event_date($event['event_date'])) ?> &middot; <?= h(format_time($event['start_time'])) ?>
            </span>
            <span class="meta-item">
              <?= PIN_ICON ?>
              <?= h($event['location']) ?>
            </span>
          </div>

          <div class="event-actions">
            <a href="../eventView/index.php?id=<?= (int)$event['event_id'] ?>" class="btn-primary">
              View Details<?= $layout === 'horizontal' ? ' &rarr;' : '' ?>
            </a>
            <?php if (!$pending): ?>
            <a href="#" class="btn-secondary view-pass"
               data-name="<?= h($event['name']) ?>"
               data-date="<?= h(format_event_date($event['event_date']) . ' · ' . format_time_range($event['start_time'], $event['end_time'])) ?>"
               data-location="<?= h($event['location']) ?>"
               data-starts="<?= h(starts_in($event)) ?>">View Pass</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php
}

$featured = $myEvents['upcoming'][0] ?? null;
$others = array_slice($myEvents['upcoming'], 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Events — EventDNA</title>
<link rel="stylesheet" href="../../../globals.css" />
<link rel="stylesheet" href="./styles.css">
</head>
<body>

<?php include __DIR__ . "/../includes/nav.php"; ?>

<div class="dashboard-shell">

  <header class="hero-row">
    <div class="hero-copy">
      <h1 class="page-title">My Events</h1>
      <p class="supporting-copy">Manage your registered events and past attendance.</p>
    </div>
    <div class="hero-actions">

      <div class="search-bar-premium">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><line x1="21" y1="21" x2="16.5" y2="16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" id="myEventsSearch" placeholder="Search events...">
      </div>
    </div>
  </header>

  <div class="dashboard-content main-grid">
    <div class="main-col">
      <div class="section-title-row">
        <h2>Upcoming Events</h2>
      </div>

      <?php if ($featured): ?>
        <?php upcoming_card($featured, 'horizontal'); ?>
      <?php else: ?>
      <div class="empty-state" style="text-align: center; padding: 4rem 2rem; background: rgba(255, 255, 255, 0.92); border: 1px solid rgba(226, 232, 240, 0.92); border-radius: 8px; margin-top: 1.5rem;">
        <h3 style="font-size: 1.25rem; color: #0f172a; margin-bottom: 0.5rem;">No upcoming events</h3>
        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1.5rem;">You haven't registered for any upcoming events yet.</p>
        <a href="../ExploreEvents/index.php" class="btn-primary" style="display: inline-flex; padding: 0.75rem 1.5rem; border-radius: 8px;">Discover Events &rarr;</a>
      </div>
      <?php endif; ?>

      <?php if ($others): ?>
      <div class="events-grid">
        <?php foreach ($others as $event): ?>
          <?php upcoming_card($event, 'vertical'); ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if ($myEvents['past']): ?>
      <div class="section-title-row" style="margin-top: 3.5rem;">
        <h2>Past Events</h2>
      </div>

      <?php foreach ($myEvents['past'] as $event):
        if ((int)$event['checked_in'] === 1) {
            $badge = ['&#10003; ATTENDED', 'background: rgba(16, 185, 129, 0.1); color: #10b981;'];
        } elseif ($event['display_status'] === 'Cancelled') {
            $badge = ['CANCELLED', 'background: rgba(148, 163, 184, 0.15); color: var(--text-secondary);'];
        } else {
            $badge = ['NOT ATTENDED', 'background: rgba(148, 163, 184, 0.15); color: var(--text-secondary);'];
        }
      ?>
      <div class="event-card-horizontal past-event" data-title="<?= h(mb_strtolower($event['name'])) ?>">
        <div class="event-thumb" style="<?= thumb_style($event, true) ?>">
          <div class="event-thumb-brand">EventDNA</div>
        </div>

        <div class="event-body">
          <div class="event-info-top">
            <div class="tag-row">
              <?php if ($event['primary_interest']): ?>
                <span class="tag"><?= h($event['primary_interest']) ?></span>
              <?php endif; ?>
            </div>
            <span class="registered-badge" style="<?= $badge[1] ?>"><?= $badge[0] ?></span>
          </div>

          <h3 style="color: var(--text-secondary);"><?= h($event['name']) ?></h3>

          <div class="event-meta">
            <span class="meta-item">
              <?= CALENDAR_ICON ?>
              <?= h(format_event_date($event['event_date'])) ?>
            </span>
            <span class="meta-item">
              <?= PIN_ICON ?>
              <?= h($event['location']) ?>
            </span>
          </div>

          <div class="event-actions">
            <a href="../eventView/index.php?id=<?= (int)$event['event_id'] ?>" class="btn-outline" style="border: 1px solid var(--border-color); color: var(--text-secondary); padding: 0.6rem 1.25rem; font-size: 0.82rem; border-radius: 8px; font-weight: 500;">View Details</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="side-col">
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-num"><?= $myEvents['stats']['upcoming'] ?></div>
          <div class="stat-label">Upcoming</div>
        </div>
        <div class="stat-card">
          <div class="stat-num"><?= $myEvents['stats']['attended'] ?></div>
          <div class="stat-label">Attended</div>
        </div>
      </div>

      <div class="month-card">
        <h4>This Month</h4>
        <?php if (!$myEvents['this_month']): ?>
          <div class="month-item-sub">No events this month.</div>
        <?php endif; ?>
        <?php foreach ($myEvents['this_month'] as $event): ?>
        <a class="month-item" href="../eventView/index.php?id=<?= (int)$event['event_id'] ?>" style="text-decoration: none; color: inherit;">
          <div class="month-date">
            <span class="m"><?= h(date('M', strtotime($event['event_date']))) ?></span>
            <span class="d"><?= h(date('j', strtotime($event['event_date']))) ?></span>
          </div>
          <div>
            <div class="month-item-title"><?= h($event['name']) ?></div>
            <div class="month-item-sub"><?= h($event['location']) ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>


<div class="modal-overlay" id="eventPassModal">
  <div class="pass-modal">
    <button class="modal-close" id="closePassModal">
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </button>
    
    <div class="pass-modal-header">
      <h2>Your Event Pass</h2>
      <p>Registration complete. You are ready to go.</p>
    </div>

    <div class="pass-modal-body">

      <div class="pass-top-row">
        <div class="pass-visual">
          <span class="registered-pill">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Registered
          </span>
        </div>
        
        <div class="pass-details-col">
          <div class="pass-info-card">
            <h3 id="passName"></h3>
            <div class="pass-meta-list">
              <div class="pass-meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                <span id="passDate"></span>
              </div>
              <div class="pass-meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
                <span id="passLocation"></span>
              </div>
              <div class="pass-meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                <span id="passStarts"></span>
              </div>
            </div>
          </div>
          
          <div class="pass-calendar-card">
            <div class="calendar-label">CALENDAR</div>
            <div class="calendar-grid">
              <button class="btn-cal"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>Google</button>
              <button class="btn-cal"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>Apple</button>
              <button class="btn-cal"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>Outlook</button>
              <button class="btn-cal"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>ICS</button>
            </div>
          </div>
          
          <button class="btn-primary w-full share-btn">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="6" cy="12" r="2" stroke="currentColor" stroke-width="1.6"/><circle cx="18" cy="6" r="2" stroke="currentColor" stroke-width="1.6"/><circle cx="18" cy="18" r="2" stroke="currentColor" stroke-width="1.6"/><line x1="7.7" y1="11" x2="16.3" y2="7" stroke="currentColor" stroke-width="1.6"/><line x1="7.7" y1="13" x2="16.3" y2="17" stroke="currentColor" stroke-width="1.6"/></svg>
            Share Event
          </button>
        </div>
      </div>

      <div class="access-codes-section">
        <h3>Access Codes</h3>
        <div class="access-grid">
          <div class="access-card">
            <div class="access-icon">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" stroke="currentColor" stroke-width="2"/><path d="M14 14h7v7h-7z" fill="currentColor"/><path d="M14 14h2v2h-2z" fill="white"/></svg>
            </div>
            <h4>Event QR</h4>
            <p>Available on event day</p>
          </div>
          <div class="access-card locked">
            <div class="access-icon">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="7" y="11" width="10" height="10" rx="2" stroke="currentColor" stroke-width="2"/><path d="M17 11V7a5 5 0 0 0-10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="16" r="1.5" fill="currentColor"/></svg>
            </div>
            <h4>Personal Networking QR</h4>
            <p>Locked - Available after check-in</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const modalOverlay = document.getElementById('eventPassModal');
  const closeModalBtn = document.getElementById('closePassModal');

  document.querySelectorAll('.view-pass').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      document.getElementById('passName').textContent = btn.dataset.name;
      document.getElementById('passDate').textContent = btn.dataset.date;
      document.getElementById('passLocation').textContent = btn.dataset.location;
      document.getElementById('passStarts').textContent = btn.dataset.starts;
      modalOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  });

  closeModalBtn.addEventListener('click', () => {
    modalOverlay.classList.remove('active');
    document.body.style.overflow = '';
  });

  modalOverlay.addEventListener('click', (e) => {
    if(e.target === modalOverlay) {
      modalOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  });

  document.getElementById('myEventsSearch').addEventListener('input', (e) => {
    const term = e.target.value.trim().toLowerCase();
    document.querySelectorAll('[data-title]').forEach(card => {
      card.style.display = card.dataset.title.includes(term) ? '' : 'none';
    });
  });
</script>

</body>
</html>