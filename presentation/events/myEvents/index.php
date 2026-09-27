<?php
session_start();
require_once __DIR__ . '/../../attendee/includes/avatar.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}
$attendeeName = $_SESSION['full_name'] ?? 'Attendee';
$userId = $_SESSION['user_id'];

require_once __DIR__ . '/../../../data/database.php';
require_once __DIR__ . '/../../../data/EventRepository.php';

$eventRepo = new EventRepository($conn);
$allEvents = $eventRepo->getRegisteredEvents($userId);

$upcomingEvents = [];
$pastEvents = [];
$cancelledEvents = [];
$now = time();

foreach ($allEvents as $ev) {
    // Organizer-cancelled events get their own section so they don't look like they're still on
    if ($ev['status'] === 'CANCELLED') {
        $cancelledEvents[] = $ev;
        continue;
    }
    $evTime = strtotime($ev['event_date'] . ' ' . $ev['start_time']);
    if ($evTime > $now) {
        $upcomingEvents[] = $ev;
    } else {
        $pastEvents[] = $ev;
    }
}

function formatDate($dateStr) {
    return date('M j, Y', strtotime($dateStr));
}
function formatShortDate($dateStr) {
    return date('M j', strtotime($dateStr));
}
function formatTime($timeStr) {
    return date('g:i A', strtotime($timeStr));
}
// Cover photos are stored relative to the project root (e.g. uploads/events/x.jpg)
function coverUrl($path) {
    return $path ? '../../../' . $path : '';
}
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

  <nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="../../attendee/dashboard/index.php" class="nav-logo">
          <img src="../../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="../ExploreEvents/index.php" class="nav-link">Find Events</a>
          <a href="#" class="nav-link active">My Events</a>
          <a href="../../attendee/community/community-hub/index.php" class="nav-link">Communities</a>
          <a href="../../attendee/myConnections/index.php" class="nav-link">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <?= nav_notifications_html() ?>
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <?= nav_avatar_html($attendeeName) ?>
            <span class="nav-profile-name"><?= htmlspecialchars(explode(' ', trim($attendeeName))[0]) ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="nav-dropdown">
            <a href="../../attendee/settings/index.php" class="dropdown-item">Profile</a>
            <a href="../../auth/logout/index.php" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

<div class="dashboard-shell">

  <header class="hero-row">
    <div class="hero-copy">
      <h1 class="page-title">My Events</h1>
      <p class="supporting-copy">Manage your registered events and past attendance.</p>
    </div>
    <div class="hero-actions">

      <div class="search-bar-premium">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><line x1="21" y1="21" x2="16.5" y2="16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        <input type="text" placeholder="Search events...">
      </div>
    </div>
  </header>

  <div class="dashboard-content main-grid">
    <div class="main-col">
      <div class="section-title-row">
        <h2>Upcoming Events</h2>
      </div>


      <?php if (empty($upcomingEvents)): ?>
      <div class="empty-state" style="text-align: center; padding: 4rem 2rem; background: rgba(255, 255, 255, 0.92); border: 1px solid rgba(226, 232, 240, 0.92); border-radius: 8px; margin-top: 1.5rem;">
        <h3 style="font-size: 1.25rem; color: #0f172a; margin-bottom: 0.5rem;">No upcoming events</h3>
        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1.5rem;">You haven't registered for any upcoming events yet.</p>
        <a href="../ExploreEvents/index.php" class="btn-primary" style="display: inline-flex; padding: 0.75rem 1.5rem; border-radius: 8px;">Discover Events &rarr;</a>
      </div>
      <?php else: ?>
        <div class="events-grid" style="margin-top: 2rem;">
        <?php foreach ($upcomingEvents as $ev): 
            $interests = $eventRepo->getEventInterestNames($ev['event_id']);
            $mainInterest = !empty($interests) ? $interests[0] : 'Event';
        ?>
        <div class="event-card-vertical">
          <div class="event-thumb" style="background: url('<?= htmlspecialchars(coverUrl($ev['cover_photo'])) ?>') center/cover;">
            <div class="event-thumb-brand">EventDNA</div>
          </div>
          <div class="event-body">
            <div class="event-info-top">
              <div class="tag-row">
                <span class="tag"><?= htmlspecialchars($mainInterest) ?></span>
              </div>
              <span class="registered-badge"><?= htmlspecialchars($ev['reg_status']) ?></span>
            </div>
            <h3><?= htmlspecialchars($ev['name']) ?></h3>
            <div class="event-meta">
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                <?= formatDate($ev['event_date']) ?>
              </span>
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
                <?= htmlspecialchars($ev['location']) ?>
              </span>
            </div>
            <div class="event-actions">
              <a href="../eventView/index.php?id=<?= $ev['event_id'] ?>" class="btn-primary">View Details</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($cancelledEvents)): ?>
      <div class="section-title-row">
        <h2>Cancelled</h2>
      </div>

        <?php foreach ($cancelledEvents as $ev): ?>
        <div class="event-card-horizontal cancelled-event">
          <div class="event-thumb" style="background: url('<?= htmlspecialchars(coverUrl($ev['cover_photo'])) ?>') center/cover;">
            <div class="event-thumb-brand">EventDNA</div>
          </div>

          <div class="event-body">
            <div class="event-info-top">
              <div class="tag-row">
              </div>
              <span class="registered-badge cancelled-badge">Cancelled</span>
            </div>

            <h3><?= htmlspecialchars($ev['name']) ?></h3>

            <div class="event-meta">
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                <?= formatDate($ev['event_date']) ?>
              </span>
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
                <?= htmlspecialchars($ev['location']) ?>
              </span>
            </div>
            <p class="cancelled-note">The organizer cancelled this event.</p>

            <div class="event-actions">
              <a href="../eventView/index.php?id=<?= $ev['event_id'] ?>" class="btn-outline" style="border: 1px solid var(--border-color); color: var(--text-secondary); padding: 0.6rem 1.25rem; font-size: 0.82rem; border-radius: 8px; font-weight: 500;">View Details</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="section-title-row">
        <h2>Past Events</h2>
      </div>
      
      <?php if (empty($pastEvents)): ?>
      <div class="empty-state" style="text-align: center; padding: 4rem 2rem; background: rgba(255, 255, 255, 0.92); border: 1px solid rgba(226, 232, 240, 0.92); border-radius: 8px; margin-top: 1.5rem;">
        <h3 style="font-size: 1.25rem; color: #0f172a; margin-bottom: 0.5rem;">No past events</h3>
        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1.5rem;">You haven't attended any events yet.</p>
      </div>
      <?php else: ?>
        <?php foreach ($pastEvents as $ev): ?>
        <div class="event-card-horizontal past-event">
          <div class="event-thumb" style="background: url('<?= htmlspecialchars(coverUrl($ev['cover_photo'])) ?>') center/cover; opacity: 0.85;">
            <div class="event-thumb-brand">EventDNA</div>
          </div>

          <div class="event-body">
            <div class="event-info-top">
              <div class="tag-row">
              </div>
              <span class="registered-badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">&#10003; <?= htmlspecialchars($ev['reg_status']) ?></span>
            </div>

            <h3 style="color: var(--text-secondary);"><?= htmlspecialchars($ev['name']) ?></h3>

            <div class="event-meta">
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                <?= formatDate($ev['event_date']) ?>
              </span>
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
                <?= htmlspecialchars($ev['location']) ?>
              </span>
            </div>

            <div class="event-actions">
              <a href="../eventView/index.php?id=<?= $ev['event_id'] ?>" class="btn-outline" style="border: 1px solid var(--border-color); color: var(--text-secondary); padding: 0.6rem 1.25rem; font-size: 0.82rem; border-radius: 8px; font-weight: 500;">View Details</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="side-col">
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-num"><?= count($upcomingEvents) ?></div>
          <div class="stat-label">Upcoming</div>
        </div>
        <div class="stat-card">
          <div class="stat-num"><?= count($pastEvents) ?></div>
          <div class="stat-label">Past</div>
        </div>
      </div>

      <div class="month-card">
        <h4>Recent Registration</h4>
        <?php if (!empty($upcomingEvents)): 
          $latest = $upcomingEvents[0];
        ?>
        <div class="month-item">
          <div class="month-date">
            <span class="m"><?= date('M', strtotime($latest['event_date'])) ?></span>
            <span class="d"><?= date('d', strtotime($latest['event_date'])) ?></span>
          </div>
          <div>
            <div class="month-item-title"><?= htmlspecialchars($latest['name']) ?></div>
            <div class="month-item-sub"><?= htmlspecialchars($latest['location']) ?></div>
          </div>
        </div>
        <?php else: ?>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem;">No recent registrations.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>


<div class="modal-overlay" id="eventPassModal">
  <div class="modal pass-modal">
    <button class="modal-close" id="closePassModal">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    <div class="modal-body pass-modal-body">
      <div class="pass-header">
        <h1>Your Event Pass</h1>
        <p>Registration complete. You are ready to go.</p>
      </div>

      <div class="pass-top-grid">
        <div class="pass-card">
          <span class="registered-pill">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="white" stroke-width="2"/><path d="M8 12.5l2.5 2.5L16 9" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Registered
          </span>
          <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem; margin-top:2rem;">Your Event Pass</div>
          <h3 style="font-size:1.4rem; font-weight:700; text-align:center; margin:0 0 1.25rem 0; line-height:1.2;">AI Innovation Summit 2026</h3>
          
          <div style="text-align:center; margin-bottom:1.75rem;">
            <div style="font-size:1.15rem; font-weight:600; color:var(--text-primary); margin-bottom:0.15rem;"><?= htmlspecialchars($attendeeName) ?></div>
            <div style="font-size:0.9rem; color:var(--text-secondary);">Attendee</div>
          </div>

          <div style="font-size:0.85rem; font-weight:600; color:var(--text-primary); letter-spacing:0.02em; margin-bottom:0.4rem;">EVENT ID: AIS2026</div>
          <div style="font-size:0.75rem; color:var(--text-tertiary);">Check in at the event venue</div>
        </div>

        <div class="pass-right-col">
          <div class="pass-info-card">
            <h3>AI Innovation Summit 2026</h3>
            <div class="pass-info-row">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              Oct 24–26, 2026 · 9:00 AM
            </div>
            <div class="pass-info-row">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
              BMICH, Colombo
            </div>
            <div class="pass-info-row">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Starts in 14 days, 5 hours
            </div>
          </div>

          <div class="pass-info-card calendar-card">
            <div class="cal-label">CALENDAR</div>
            <div class="cal-grid">
              <button class="cal-btn">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/></svg>
                Google
              </button>
              <button class="cal-btn">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/></svg>
                Apple
              </button>
              <button class="cal-btn">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/></svg>
                Outlook
              </button>
              <button class="cal-btn">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3v12m0 0l-4-4m4 4l4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                ICS
              </button>
            </div>
          </div>

          <button class="btn-primary share-btn">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="6" cy="12" r="2" stroke="white" stroke-width="1.6"/><circle cx="18" cy="6" r="2" stroke="white" stroke-width="1.6"/><circle cx="18" cy="18" r="2" stroke="white" stroke-width="1.6"/><line x1="7.7" y1="11" x2="16.3" y2="7" stroke="white" stroke-width="1.6"/><line x1="7.7" y1="13" x2="16.3" y2="17" stroke="white" stroke-width="1.6"/></svg>
            Share Event
          </button>
        </div>
      </div>

      <div class="access-card">
        <h2>Event Access &amp; Networking</h2>
        <div class="access-grid">
          <div class="access-tile available">
            <div class="access-tile-icon">
              <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                <rect x="5" y="5" width="3" height="3" fill="var(--primary-tint)"/><rect x="16" y="5" width="3" height="3" fill="var(--primary-tint)"/><rect x="5" y="16" width="3" height="3" fill="var(--primary-tint)"/>
                <rect x="14" y="14" width="3" height="3"/><rect x="18" y="14" width="3" height="3"/><rect x="14" y="18" width="3" height="3"/><rect x="18" y="18" width="3" height="3"/>
                <rect x="12" y="3" width="1.5" height="1.5"/><rect x="12" y="7" width="1.5" height="1.5"/>
                <rect x="3" y="12" width="1.5" height="1.5"/><rect x="7" y="12" width="1.5" height="1.5"/>
              </svg>
            </div>
            <div class="access-tile-title">Event Check-in</div>
            <div class="access-tile-sub">Scan the event QR to confirm your attendance.</div>
          </div>

          <div class="access-tile locked" style="opacity: 0.65; filter: grayscale(100%);">
            <div class="access-tile-icon">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="5" y="11" width="14" height="9" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 11V8a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
            </div>
            <div class="access-tile-title">Personal Networking</div>
            <div class="access-tile-sub">🔒 Locked &middot; Check in to unlock networking</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const passBtns = document.querySelectorAll('.btn-secondary');
  const modalOverlay = document.getElementById('eventPassModal');
  const closeModalBtn = document.getElementById('closePassModal');

  passBtns.forEach(btn => {
    if(btn.textContent.trim() === 'View Pass') {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
      });
    }
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
</script>

</body>
</html>