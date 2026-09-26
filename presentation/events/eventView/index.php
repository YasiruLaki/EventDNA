<?php
require_once __DIR__ . "/../includes/guard.php";
require_once "../../../data/database.php";
require_once "../../../application/controllers/EventController.php";

$eventController = new EventController($conn);
$event = $eventController->getEventForAttendee($attendeeId, (int)($_GET['id'] ?? 0));
if (!$event) {
    http_response_code(404);
    die("Event not found.");
}

$eventOver = in_array($event['display_status'], ['Cancelled', 'Completed'], true);
$isRegistered = !$eventOver && in_array($event['my_registration'], ['PENDING', 'APPROVED', 'REGISTERED'], true);
$wasTurnedAway = in_array($event['my_registration'], ['REJECTED', 'REMOVED'], true);
$canRegister = !$isRegistered && !$wasTurnedAway && $event['registration_state'] === 'Open';

// Label for the disabled button when the attendee cannot register
if ($event['display_status'] === 'Cancelled') {
    $closedLabel = 'Event Cancelled';
} elseif ($event['display_status'] === 'Completed') {
    $closedLabel = 'Event Ended';
} elseif ($wasTurnedAway) {
    $closedLabel = 'Registration Unavailable';
} elseif ($event['registration_state'] === 'Full') {
    $closedLabel = 'Fully Booked';
} elseif ($event['registration_state'] === 'Not yet open') {
    $closedLabel = 'Registration Not Yet Open';
} else {
    $closedLabel = 'Registration Closed';
}
$fewSeatsLeft = $event['remaining_seats'] > 0 && $event['remaining_seats'] <= max(5, (int)ceil($event['capacity'] * 0.1));
$heroStyle = $event['cover_photo']
    ? "background: linear-gradient(0deg, rgba(0, 0, 0, 0.904) 0%, rgba(7, 16, 32, 0.6) 45%, rgba(7, 16, 32, 0.32) 100%), url('" . h(cover_url($event['cover_photo'])) . "') center center / cover no-repeat;"
    : "";
$activeNav = 'explore';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($event['name']) ?> — EventDNA</title>
<link rel="stylesheet" href="../../../globals.css" />
<link rel="stylesheet" href="./styles.css">
</head>
<body>

<!-- Nav -->
<?php include __DIR__ . "/../includes/nav.php"; ?>

<!-- Hero -->
<section class="hero"<?= $heroStyle ? ' style="' . $heroStyle . '"' : '' ?>>
  <div class="container hero-inner">
    <?php if ($event['interest_names']): ?>
      <span class="hero-badge"><?= h($event['interest_names'][0]) ?></span>
    <?php endif; ?>
    <h1><?= h($event['name']) ?></h1>
  </div>
</section>

<!-- Info bar -->
<div class="info-bar-wrap">
  <div class="container">
    <div class="info-bar">
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="info-label">Date</div>
          <div class="info-value"><?= h(format_event_date($event['event_date'])) ?></div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div>
          <div class="info-label">Time</div>
          <div class="info-value"><?= h(format_time_range($event['start_time'], $event['end_time'])) ?></div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
        </div>
        <div>
          <div class="info-label">Location</div>
          <div class="info-value"><?= h($event['location']) ?></div>
          <?php if ($event['address']): ?>
            <div class="info-label"><?= h($event['address']) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="8" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M4 18c0-2.6 2.2-4.7 5-4.7s5 2.1 5 4.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="17" cy="9" r="2" stroke="currentColor" stroke-width="1.6"/><path d="M15 13.5c2 0 4.5 1.8 4.5 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="info-label">Seats Left</div>
          <div class="info-value"><?= $event['remaining_seats'] ?> of <?= (int)$event['capacity'] ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Body -->
<section class="body-section">
  <div class="container body-grid">

    <div class="main-col">
      <div class="organizer-section">
        <h2>Organized by</h2>
        <div class="organizer-flex">
          <div class="organizer-avatar" style="display: flex; align-items: center; justify-content: center; background: var(--primary); color: #fff; font-weight: 700;">
            <?= h(mb_strtoupper(mb_substr($event['organizer_name'], 0, 1))) ?>
          </div>
          <div>
            <div class="organizer-role">Event Organizer</div>
            <div class="organizer-name"><?= h($event['organizer_name']) ?></div>
          </div>
        </div>
      </div>

      <?php if ($event['description']): ?>
      <h2>About Event</h2>
      <div class="about-text">
        <p>
          <?= nl2br(h($event['description'])) ?>
        </p>
      </div>
      <?php endif; ?>

      <?php if ($event['interest_names']): ?>
      <div class="event-interests-section">
        <h2>Interests</h2>
        <div class="event-interests-flex">
          <?php foreach ($event['interest_names'] as $interestName): ?>
            <span class="interest-pill"><?= h($interestName) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <h2 class="why-heading">Highlights</h2>
      <div class="why-grid">
        <div class="why-card">
          <div class="why-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l2.6 6.2L21 10l-5.4 3.9L17 21l-5-3.8L7 21l1.4-7.1L3 10l6.4-1.8L12 2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
          </div>
          <h3>Expert Sessions</h3>
          <p>Learn from professionals and researchers.</p>
        </div>
        <div class="why-card">
          <div class="why-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 2L4 14h6l-1 8 9-12h-6l1-8z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
          </div>
          <h3>Networking after check-in</h3>
          <p>Discover relevant connections after you check in at the event.</p>
        </div>
        <div class="why-card">
          <div class="why-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 20V10l8-6 8 6v10" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M9 20v-6h6v6" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
          </div>
          <h3>Startup Showcase</h3>
          <p>Explore emerging products and ideas.</p>
        </div>
      </div>
    </div>

    <div class="side-col">
      <div class="reg-card">
        <div class="reg-header">
          <h3>Registration</h3>
        </div>
        <div class="reg-sub">
          <?php if ($event['display_status'] === 'Cancelled'): ?>
            <span class="reg-status" style="color: var(--text-secondary);">Event Cancelled</span>
          <?php elseif ($event['display_status'] === 'Completed'): ?>
            <span class="reg-status" style="color: var(--text-secondary);">Event Ended</span>
          <?php elseif ($event['registration_state'] === 'Open'): ?>
            <span class="reg-status">Registration Open</span>
          <?php else: ?>
            <span class="reg-status" style="color: var(--text-secondary);">Registration <?= h($event['registration_state']) ?></span>
          <?php endif; ?>
        </div>
        
        <div class="reg-count">
          <?= (int)$event['registered_count'] ?> / <?= (int)$event['capacity'] ?> registered
        </div>
        <div class="reg-count<?= $fewSeatsLeft ? ' spots-warning' : '' ?>">
          <?= $event['remaining_seats'] === 0 ? 'No spots left' : h(seats_label($event['remaining_seats'])) ?>
        </div>
        <div class="reg-deadline">
          <?php if ($event['registration_state'] === 'Not yet open'): ?>
            Registration opens <?= h(format_event_date($event['registration_open'])) ?>
          <?php else: ?>
            Registration closes <?= h(format_event_date($event['registration_close'])) ?>
          <?php endif; ?>
        </div>

        <?php if ($isRegistered): ?>
          <a href="../myEvents/index.php" class="btn-primary reg-cta" style="text-decoration: none;">
            <?= $event['my_registration'] === 'PENDING' ? 'Awaiting Approval' : "You're Registered" ?> &middot; My Events
          </a>
        <?php elseif ($canRegister): ?>
          <button class="btn-primary reg-cta">
            Register Now &rarr;
          </button>
        <?php else: ?>
          <button class="btn-primary reg-cta" disabled style="opacity: 0.5; cursor: not-allowed;">
            <?= $closedLabel ?>
          </button>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>


<!-- Registration Modal -->
<div class="modal-overlay" id="registrationModal">
  <div class="modal">
    <div class="modal-banner">
      <button class="modal-close" aria-label="Close" id="modalCloseBtn">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
      <div class="modal-banner-top">
      </div>
    </div>

    <div class="modal-body">
      <h1><?= h($event['name']) ?></h1>
      <div class="hosted-by">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 9h18" stroke="currentColor" stroke-width="1.6"/></svg>
        Hosted by <?= h($event['organizer_name']) ?>
      </div>

      <div class="meta-row">
        <span class="meta-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          <?= h(date('M j', strtotime($event['event_date']))) ?>, <?= h(format_time($event['start_time'])) ?>
        </span>
        <span class="meta-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
          <?= h($event['location']) ?>
        </span>
        <span class="meta-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><line x1="10" y1="7" x2="10" y2="17" stroke="currentColor" stroke-width="1.4" stroke-dasharray="1.8 2" stroke-linecap="round"/></svg>
          Free Entry
        </span>
      </div>

      <div class="attending-row">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="8" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M4 18c0-2.6 2.2-4.7 5-4.7s5 2.1 5 4.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="17" cy="9" r="2" stroke="currentColor" stroke-width="1.6"/><path d="M15 13.5c2 0 4.5 1.8 4.5 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        <?= (int)$event['registered_count'] ?> Attending &middot; <?= h(seats_label($event['remaining_seats'])) ?>
      </div>

      <div class="info-box">
        <div class="info-box-header">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><line x1="12" y1="11" x2="12" y2="16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="8" r="0.9" fill="currentColor"/></svg>
          What happens next?
        </div>

        <div class="info-list-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 6.5l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          <div>
            <div class="info-list-title">Confirmation Email &amp; Ticket</div>
            <div class="info-list-sub">Your digital pass with QR code will be sent immediately.</div>
          </div>
        </div>

        <div class="info-list-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
          <div>
            <div class="info-list-title">Event Reminders</div>
            <div class="info-list-sub">We'll notify you 24 hours before the event starts.</div>
          </div>
        </div>

        <div class="info-list-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 12s3.8-6.5 10-6.5S22 12 22 12s-3.8 6.5-10 6.5S2 12 2 12z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.6"/></svg>
          <div>
            <div class="info-list-title">Compatibility Matching</div>
            <div class="info-list-sub">Opt-in to share your profile for networking opportunities.</div>
          </div>
        </div>
      </div>

      <label class="check-row">
        <input type="checkbox" checked>
        <div>
          <div class="check-label">Send me event updates and alerts</div>
          <div class="check-sub">Receive notifications about schedule changes and important announcements.</div>
        </div>
      </label>

      <label class="check-row">
        <input type="checkbox">
        <div>
          <div class="check-label">I agree to the Community Guidelines <span class="req">*</span></div>
          <div class="check-sub">Read our code of conduct for a safe and respectful event.</div>
        </div>
      </label>

      <div class="modal-actions">
        <button class="btn-cancel" id="modalCancelBtn">Cancel</button>
        <a href="../registrationSuccess/index.html" class="btn-primary" style="text-decoration: none;">
          Register Now
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12h14M13 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("registrationModal");
    const openBtn = document.querySelector("button.reg-cta:not([disabled])");
    const closeBtns = [document.getElementById("modalCloseBtn"), document.getElementById("modalCancelBtn")];
    
    if (openBtn) {
      openBtn.addEventListener("click", () => {
        modal.classList.add("active");
        document.body.style.overflow = "hidden";
      });
    }

    closeBtns.forEach(btn => {
      btn.addEventListener("click", () => {
        modal.classList.remove("active");
        document.body.style.overflow = "";
      });
    });

    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  });
</script>

</body>
</html>