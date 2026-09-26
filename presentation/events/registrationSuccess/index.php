<?php
require_once __DIR__ . "/../includes/guard.php";
require_once "../../../data/database.php";
require_once "../../../application/controllers/EventController.php";

$eventController = new EventController($conn);
$eventId = (int)($_GET['id'] ?? 0);
$event = $eventController->getEventForAttendee($attendeeId, $eventId);

// Only people who hold a seat get the success page; everyone else goes back to the event
if (!$event || !in_array($event['my_registration'], ['PENDING', 'APPROVED', 'REGISTERED'], true)) {
    header("Location: ../eventView/index.php?id=" . $eventId);
    exit;
}

$pending = $event['my_registration'] === 'PENDING';
$bannerStyle = $event['cover_photo']
    ? "background: linear-gradient(180deg, rgba(15,23,42,0.4), rgba(15,23,42,0.1)), url('" . h(cover_url($event['cover_photo'])) . "') center/cover;"
    : "background: linear-gradient(135deg, #6D3BFF 0%, #2A0BB0 100%);";
$activeNav = 'mine';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pending ? 'Request Sent' : "You're Registered" ?> — EventDNA</title>
<link rel="stylesheet" href="../../../globals.css" />
<link rel="stylesheet" href="./styles.css">
</head>
<body>

<?php include __DIR__ . "/../includes/nav.php"; ?>

<div class="dashboard-shell success-shell">
  <div class="card">
    <!-- Left column -->
    <div class="left-col">
      <div class="status-row">
        <div class="status-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M8 12.5l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="status-text">
          <?php if ($pending): ?>
            <h1>Request Sent!</h1>
            <p>This is an invite-only event. The organizer will review your request.</p>
          <?php else: ?>
            <h1>You're Registered!</h1>
            <p>Your registration is confirmed. Your event pass is ready.</p>
          <?php endif; ?>
        </div>
      </div>

      <div style="display: flex; align-items: center; gap: 0.5rem; color: <?= $pending ? '#b45309' : 'var(--primary)' ?>; font-weight: 600; font-size: 0.9rem; margin-bottom: 1.5rem;">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="18" height="18"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <?= $pending ? 'Awaiting organizer approval' : 'Registration confirmed' ?>      </div>

      <div class="journey-label" style="text-transform: none; font-size: 1.05rem; color: var(--text-primary); letter-spacing: 0;">What's next?</div>

      <ul class="timeline">
        <?php if ($pending): ?>
        <li class="timeline-item active">
          <div class="timeline-title">Request Sent</div>
          <div class="timeline-sub">Your seat is held while the organizer reviews your request.</div>
        </li>
        <li class="timeline-item">
          <div class="timeline-title">Organizer Approval</div>
          <div class="timeline-sub">You'll get a notification once you're approved.</div>
        </li>
        <?php else: ?>
        <li class="timeline-item active">
          <div class="timeline-title">Registered</div>
          <div class="timeline-sub">Your place is confirmed.</div>
        </li>
        <?php endif; ?>
        <li class="timeline-item">
          <div class="timeline-title">Attend Event</div>
          <div class="timeline-sub"><?= h(format_event_date($event['event_date'])) ?> at <?= h(format_time($event['start_time'])) ?>, <?= h($event['location']) ?>.</div>
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
        <div class="ticket-banner" style="<?= $bannerStyle ?>">
          <span class="ticket-badge"><?= $pending ? 'Pending' : 'Registered' ?></span>
        </div>
        <div class="ticket-body">
          <h3><?= h($event['name']) ?></h3>
          <div class="ticket-meta">
            <span class="ticket-meta-item">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              <?= h(format_event_date($event['event_date'])) ?> &middot; <?= h(format_time($event['start_time'])) ?>
            </span>
            <span class="ticket-meta-item">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
              <?= h($event['location']) ?>
            </span>
            <span class="ticket-meta-item">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="8" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M5 20c0-3.4 3-6 7-6s7 2.6 7 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              <?= h($attendeeName) ?>
            </span>
          </div>
        </div>

        <div class="ticket-divider"></div>

        <div style="padding: 1.25rem; text-align: center;">
          <a href="../eventView/index.php?id=<?= (int)$event['event_id'] ?>" class="btn-primary" style="width: 100%; justify-content: center;">View Event &rarr;</a>
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>
