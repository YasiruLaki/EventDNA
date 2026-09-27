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

$eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$eventRepo = new EventRepository($conn);
$event = $eventRepo->getEventById($eventId);

if (!$event) {
    header("Location: ../ExploreEvents/index.php");
    exit;
}

$interests = $eventRepo->getEventInterestNames($eventId);

// Check if already registered
$checkStmt = $conn->prepare("SELECT status, receive_updates FROM event_registrations WHERE event_id = ? AND user_id = ? AND status IN ('PENDING','APPROVED','REGISTERED')");
$checkStmt->bind_param("ii", $eventId, $userId);
$checkStmt->execute();
$registration = $checkStmt->get_result()->fetch_assoc();
$isRegistered = $registration !== null;
$errorMessage = $_GET['error'] ?? '';
$successMessage = $_GET['success'] ?? '';

// For cancelled events, show registrants the message they were notified with (it carries the organizer's reason)
$isCancelled = $event['status'] === 'CANCELLED';
$cancelMessage = '';
if ($isCancelled) {
    $noteStmt = $conn->prepare("SELECT message FROM notifications WHERE user_id = ? AND type = 'EVENT_CANCELLED' AND reference_type = 'event' AND reference_id = ? ORDER BY created_at DESC LIMIT 1");
    $noteStmt->bind_param("ii", $userId, $eventId);
    $noteStmt->execute();
    $cancelMessage = $noteStmt->get_result()->fetch_assoc()['message'] ?? '';
}

function formatDate($dateStr) {
    return date('M j, Y', strtotime($dateStr));
}
function formatTime($timeStr) {
    return date('g:i A', strtotime($timeStr));
}
// Cover photos are stored relative to the project root (e.g. uploads/events/x.jpg)
function coverUrl($path) {
    if (!$path) {
        return '';
    }
    return preg_match('#^https?://#i', $path) ? $path : '../../../' . $path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($event['name']) ?> — EventDNA</title>
<link rel="stylesheet" href="../../../globals.css" />
<link rel="stylesheet" href="./styles.css">
</head>
<body>

  <!-- Nav -->
  <nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="../../attendee/dashboard/index.php" class="nav-logo">
          <img src="../../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="../ExploreEvents/index.php" class="nav-link active">Find Events</a>
          <a href="../myEvents/index.php" class="nav-link">My Events</a>
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

<!-- Hero -->
<section class="hero" style="background-image: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.9)), url('<?= htmlspecialchars(coverUrl($event['cover_photo'])) ?>');">
  <div class="container hero-inner">
    <?php if ($isCancelled): ?>
    <div class="cancelled-banner" role="alert">
      <strong>This event has been cancelled.</strong>
      <span><?= htmlspecialchars($cancelMessage ?: 'The organizer cancelled this event. Registration is closed.') ?></span>
    </div>
    <?php endif; ?>
    <?php if(!empty($interests)): ?>
    <span class="hero-badge"><?= htmlspecialchars($interests[0]) ?></span>
    <?php endif; ?>
    <h1><?= htmlspecialchars($event['name']) ?></h1>
    <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
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
          <div class="info-value"><?= formatDate($event['event_date']) ?></div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div>
          <div class="info-label">Time</div>
          <div class="info-value"><?= formatTime($event['start_time']) ?> – <?= formatTime($event['end_time']) ?></div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
        </div>
        <div>
          <div class="info-label">Location</div>
          <div class="info-value"><?= htmlspecialchars($event['location']) ?></div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="8" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M4 18c0-2.6 2.2-4.7 5-4.7s5 2.1 5 4.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="17" cy="9" r="2" stroke="currentColor" stroke-width="1.6"/><path d="M15 13.5c2 0 4.5 1.8 4.5 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="info-label">Event Type</div>
          <div class="info-value"><?= htmlspecialchars(ucfirst(strtolower($event['visibility']))) ?></div>
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
          <div class="organizer-avatar">
            <img src="https://images.unsplash.com/photo-1542442828-287217bfb87f?auto=format&fit=crop&w=150&q=80" alt="EventDNA Sri Lanka">
          </div>
          <div>
            <div class="organizer-role">Event Organizer</div>
            <div class="organizer-name">EventDNA Sri Lanka</div>
          </div>
        </div>
      </div>

      <h2>About Event</h2>
      <div class="about-text">
        <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
      </div>

      <div class="event-interests-section">
        <h2>Interests</h2>
        <div class="event-interests-flex">
          <?php foreach($interests as $interest): ?>
          <span class="interest-pill"><?= htmlspecialchars($interest) ?></span>
          <?php endforeach; ?>
          <?php if(empty($interests)): ?>
          <span style="color:var(--text-tertiary); font-size:0.9rem;">No specific interests listed.</span>
          <?php endif; ?>
        </div>
      </div>

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
        <?php $spotsLeft = max(0, $event['capacity'] - $event['registered_count']); ?>
        <div class="reg-sub">
          <?php if ($isCancelled): ?>
          <span class="reg-status reg-status-cancelled">Cancelled</span>
          <?php else: ?>
          <span class="reg-status"><?= $spotsLeft > 0 ? 'Registration Open' : 'Sold Out' ?></span>
          <?php endif; ?>
        </div>
        
        <div class="reg-count">
          <?= number_format($event['registered_count']) ?> / <?= number_format($event['capacity']) ?> registered
        </div>
        <div class="reg-deadline">
          Registration closes <?= formatDate($event['registration_close']) ?>
        </div>

        <?php if ($errorMessage !== ''): ?>
          <div class="reg-alert reg-alert-error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php elseif ($successMessage !== ''): ?>
          <div class="reg-alert reg-alert-success"><?= htmlspecialchars($successMessage) ?></div>
        <?php endif; ?>

        <?php $isPast = strtotime($event['event_date'] . ' ' . $event['end_time']) < time(); ?>
        <?php if ($isCancelled): ?>
          <button class="btn-secondary reg-cta" disabled style="opacity: 0.8; cursor: not-allowed;">Event Cancelled</button>
        <?php elseif ($isRegistered): ?>
          <button class="btn-secondary reg-cta" disabled style="opacity: 0.8; cursor: default;">Already Registered</button>
          <?php if (!$isPast): ?>
            <form method="POST" action="updates_action.php" class="reg-updates-form">
              <input type="hidden" name="event_id" value="<?= $eventId ?>">
              <label class="toggle-row">
                <div>
                  <div class="check-label">Event updates</div>
                  <div class="check-sub">Get notified about schedule changes and announcements.</div>
                </div>
                <span class="toggle-switch">
                  <input type="checkbox" name="updates" value="1" id="updatesToggle" <?= (int)$registration['receive_updates'] === 1 ? 'checked' : '' ?>>
                  <span class="toggle-slider"></span>
                </span>
              </label>
            </form>
            <form method="POST" action="cancel_action.php" id="cancelRegForm">
              <input type="hidden" name="event_id" value="<?= $eventId ?>">
              <button type="submit" class="btn-cancel-reg">Cancel Registration</button>
            </form>
          <?php endif; ?>
        <?php elseif ($isPast): ?>
          <button class="btn-secondary reg-cta" disabled style="opacity: 0.8; cursor: default;">Event Ended</button>
        <?php elseif ($spotsLeft > 0): ?>
          <button class="btn-primary reg-cta">Register Now &rarr;</button>
        <?php else: ?>
          <button class="btn-secondary reg-cta" disabled style="opacity: 0.8; cursor: default;">Sold Out</button>
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
      <h1><?= htmlspecialchars($event['name']) ?></h1>
      <div class="hosted-by">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 9h18" stroke="currentColor" stroke-width="1.6"/></svg>
        EventDNA
      </div>

      <div class="meta-row">
        <span class="meta-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          <?= formatDate($event['event_date']) ?> · <?= formatTime($event['start_time']) ?>
        </span>
        <span class="meta-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
          <?= htmlspecialchars($event['location']) ?>
        </span>
        <span class="meta-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><line x1="10" y1="7" x2="10" y2="17" stroke="currentColor" stroke-width="1.4" stroke-dasharray="1.8 2" stroke-linecap="round"/></svg>
          <?= htmlspecialchars(ucfirst(strtolower($event['visibility']))) ?>
        </span>
      </div>

      <div class="attending-row">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="8" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M4 18c0-2.6 2.2-4.7 5-4.7s5 2.1 5 4.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="17" cy="9" r="2" stroke="currentColor" stroke-width="1.6"/><path d="M15 13.5c2 0 4.5 1.8 4.5 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        <?= number_format($event['registered_count']) ?> Attending
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
            <div class="info-list-sub">Your digital pass with QR code will be generated immediately.</div>
          </div>
        </div>
      </div>

      <form method="POST" action="register_action.php">
        <input type="hidden" name="event_id" value="<?= $eventId ?>">
        <label class="check-row">
          <input type="checkbox" name="updates" value="1" checked>
          <div>
            <div class="check-label">Send me event updates and alerts</div>
            <div class="check-sub">Receive notifications about schedule changes and announcements.</div>
          </div>
        </label>
        <label class="check-row">
          <input type="checkbox" required>
          <div>
            <div class="check-label">I agree to the Community Guidelines <span class="req">*</span></div>
            <div class="check-sub">Read our code of conduct for a safe and respectful event.</div>
          </div>
        </label>
        <div class="modal-actions">
          <button type="button" class="btn-cancel" id="modalCancelBtn">Cancel</button>
          <button type="submit" class="btn-primary" style="border:none; font-family:inherit;">
            Register Now
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12h14M13 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if ($isRegistered && !$isPast && !$isCancelled): ?>
<div class="modal-overlay" id="cancelRegModal">
  <div class="modal confirm-modal" role="dialog" aria-modal="true" aria-labelledby="cancelRegTitle">
    <div class="modal-body">
      <div class="confirm-icon">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><line x1="12" y1="7.5" x2="12" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="16.3" r="1" fill="currentColor"/></svg>
      </div>
      <h1 id="cancelRegTitle">Cancel your registration?</h1>
      <p class="confirm-text">You will lose your spot at <strong><?= htmlspecialchars($event['name']) ?></strong>. You can register again while registration is still open.</p>
      <div class="modal-actions">
        <button type="button" class="btn-cancel" id="keepRegBtn">Keep Registration</button>
        <button type="button" class="btn-danger" id="confirmCancelRegBtn">Cancel Registration</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("registrationModal");
    const openBtn = document.querySelector(".reg-cta");
    const closeBtns = [document.getElementById("modalCloseBtn"), document.getElementById("modalCancelBtn")];
    const updatesToggle = document.getElementById("updatesToggle");
    const cancelRegForm = document.getElementById("cancelRegForm");

    if (updatesToggle) {
      updatesToggle.addEventListener("change", () => updatesToggle.form.submit());
    }

    if (cancelRegForm) {
      const cancelRegModal = document.getElementById("cancelRegModal");
      const closeCancelRegModal = () => {
        cancelRegModal.classList.remove("active");
        document.body.style.overflow = "";
      };

      cancelRegForm.addEventListener("submit", (e) => {
        e.preventDefault();
        cancelRegModal.classList.add("active");
        document.body.style.overflow = "hidden";
      });

      document.getElementById("keepRegBtn").addEventListener("click", closeCancelRegModal);

      document.getElementById("confirmCancelRegBtn").addEventListener("click", (e) => {
        e.currentTarget.disabled = true;
        cancelRegForm.submit();
      });

      cancelRegModal.addEventListener("click", (e) => {
        if (e.target === cancelRegModal) closeCancelRegModal();
      });

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && cancelRegModal.classList.contains("active")) closeCancelRegModal();
      });
    }
    
    openBtn.addEventListener("click", () => {
      modal.classList.add("active");
      document.body.style.overflow = "hidden"; 
    });

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