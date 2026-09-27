<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}
$attendeeName = $_SESSION['full_name'] ?? 'Attendee';

require_once __DIR__ . '/../../../data/database.php';
require_once __DIR__ . '/../../../data/EventRepository.php';

$eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$eventRepo = new EventRepository($conn);
$event = $eventRepo->getEventById($eventId);

if (!$event) {
    header("Location: ../ExploreEvents/index.php");
    exit;
}

function formatDate($dateStr) {
    return date('M j, Y', strtotime($dateStr));
}
function formatTime($timeStr) {
    return date('g:i A', strtotime($timeStr));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>You're Registered — EventDNA</title>
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
          <a href="../myEvents/index.php" class="nav-link">My Events</a>
          <a href="../../attendee/community/community-hub/index.php" class="nav-link">Communities</a>
          <a href="../../attendee/myConnections/index.php" class="nav-link">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;"><?= htmlspecialchars(mb_substr($attendeeName, 0, 1)) ?></div>
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

<div class="dashboard-shell success-shell">
  <div class="card">
    <!-- Left column -->
    <div class="left-col">
      <div class="status-row">
        <div class="status-text">
          <h1>You're Registered!</h1>
          <p>Your registration is confirmed. Your event pass is ready.</p>
        </div>
      </div>

      <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--primary); font-weight: 600; font-size: 1rem; margin-bottom: 1.5rem;">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="18" height="18"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Registration confirmed
      </div>

      <div class="journey-label" style="text-transform: none; font-size: 1.25rem; color: var(--text-primary); letter-spacing: -0.02em;">What's next?</div>

      <ul class="timeline">
        <li class="timeline-item active">
          <div class="timeline-title">Registered</div>
          <div class="timeline-sub">Your place is confirmed.</div>
        </li>
        <li class="timeline-item">
          <div class="timeline-title">Attend Event</div>
          <div class="timeline-sub">Attend the event on the scheduled date.</div>
        </li>
        <li class="timeline-item">
          <div class="timeline-title">Check In</div>
          <div class="timeline-sub">Scan the event QR code when you arrive.</div>
        </li>
        <li class="timeline-item">
          <div class="timeline-title">Networking Unlocked</div>
          <div class="timeline-sub">Discover relevant connections after check-in.</div>
        </li>
      </ul>

      <div class="left-actions">
        <a href="../myEvents/index.php" class="btn-primary">Go to My Events &rarr;</a>
        <a href="../ExploreEvents/index.php" class="btn-tint">Continue Exploring</a>
      </div>
    </div>

    <!-- Right column: ticket -->
    <div class="right-col">
      <div class="ticket">
        <div class="ticket-banner">
          <span class="ticket-badge">Registered</span>
        </div>
        <div class="ticket-body">
          <h3>AI Innovation Summit 2026</h3>
          <div class="ticket-meta">
            <span class="ticket-meta-item">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              Oct 24, 2026 &middot; 9:00 AM
            </span>
            <span class="ticket-meta-item">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
              BMICH, Colombo
            </span>
            <span class="ticket-meta-item">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M5 20c0-3.4 3-6 7-6s7 2.6 7 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              Registered Attendee
            </span>
          </div>
        </div>

        <div class="ticket-divider"></div>

        <div style="padding: 1.25rem; text-align: center;">
          <a href="#" onclick="document.getElementById('passModal').classList.add('active'); return false;" class="btn-primary" style="width: 100%; justify-content: center;">View Event Pass &rarr;</a>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="modal-overlay" id="passModal">
  <div class="modal pass-modal">
    <button class="modal-close" onclick="document.getElementById('passModal').classList.remove('active')">
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
// Close modal when clicking outside
document.getElementById('passModal').addEventListener('click', function(e) {
  if (e.target === this) {
    this.classList.remove('active');
  }
});
</script>

</body>
</html>