<?php
session_start();
require_once "../../../data/database.php";
require_once "../../../application/controllers/RegistrationController.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}

$controller = new RegistrationController($conn);
$eventId = (int) ($_GET['id'] ?? 0);
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty($_POST['agree'])) {
        $error = "Please agree to the Community Guidelines.";
    } else {
        $result = $controller->register($_SESSION['user_id'], $eventId);

        if ($result['success']) {
            header("Location: ../registrationSuccess/index.php?id=" . $eventId);
            exit;
        }
        $error = $result['message'];
    }
}

$details = $controller->getEventDetails($eventId, $_SESSION['user_id']);

if (!$details) {
    http_response_code(404);
    echo "Event not found.";
    exit;
}

$event = $details['event'];
$state = $details['state'];
$isInviteOnly = $event['visibility'] === 'INVITE_ONLY';

$statusLabels = [
    'OPEN' => 'Registration Open',
    'FULL' => 'Event Full',
    'NOT_OPEN' => 'Registration Not Open',
    'CLOSED' => 'Registration Closed',
    'EVENT_CANCELLED' => 'Event Cancelled',
    'REGISTERED' => 'You are registered',
    'APPROVED' => 'Your request was approved',
    'PENDING' => 'Request pending approval',
    'REJECTED' => 'Your request was declined',
    'REMOVED' => 'Registration removed by organizer',
    'OWN' => 'You are the organizer'
];

$buttonLabels = [
    'FULL' => 'Event Full',
    'NOT_OPEN' => 'Opens ' . date('M j, g:i A', strtotime($event['registration_open'])),
    'CLOSED' => 'Registration Closed',
    'EVENT_CANCELLED' => 'Event Cancelled',
    'REGISTERED' => 'Registered',
    'APPROVED' => 'Approved',
    'PENDING' => 'Pending Approval',
    'REJECTED' => 'Request Declined',
    'REMOVED' => 'Registration Removed',
    'OWN' => 'Your Event'
];

$eventDate = date('M j, Y', strtotime($event['event_date']));
$eventTime = date('g:i A', strtotime($event['start_time'])) . ' – ' . date('g:i A', strtotime($event['end_time']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($event['name']); ?> — EventDNA</title>
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
          <a href="../ExploreEvents/index.html" class="nav-link active">Find Events</a>
          <a href="../myEvents/index.html" class="nav-link">My Events</a>
          <a href="../../attendee/community/community-hub/index.html" class="nav-link">Communities</a>
          <a href="../../attendee/myConnections/index.html" class="nav-link">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80" alt="Profile" class="nav-avatar" />
            <span class="nav-profile-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="nav-dropdown">
            <a href="../../attendee/onboarding/index.php" class="dropdown-item">Profile</a>
            <a href="#" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

<section class="hero">
  <div class="container hero-inner">
    <span class="hero-badge"><?php echo $isInviteOnly ? 'Invite Only' : 'Public Event'; ?></span>
    <h1><?php echo htmlspecialchars($event['name']); ?></h1>
    <p><?php echo htmlspecialchars($event['description'] ?? ''); ?></p>
  </div>
</section>

<div class="info-bar-wrap">
  <div class="container">
    <div class="info-bar">
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="info-label">Date</div>
          <div class="info-value"><?php echo $eventDate; ?></div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div>
          <div class="info-label">Time</div>
          <div class="info-value"><?php echo $eventTime; ?></div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
        </div>
        <div>
          <div class="info-label">Location</div>
          <div class="info-value"><?php echo htmlspecialchars($event['location']); ?></div>
        </div>
      </div>
      <div class="info-item">
        <div class="info-icon">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="8" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M4 18c0-2.6 2.2-4.7 5-4.7s5 2.1 5 4.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="17" cy="9" r="2" stroke="currentColor" stroke-width="1.6"/><path d="M15 13.5c2 0 4.5 1.8 4.5 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        </div>
        <div>
          <div class="info-label">Capacity</div>
          <div class="info-value"><?php echo (int) $event['capacity']; ?> seats</div>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="body-section">
  <div class="container body-grid">
    <div class="main-col">
      <div class="organizer-section">
        <h2>Organized by</h2>
        <div class="organizer-flex">
          <div class="organizer-avatar">
            <img src="https://images.unsplash.com/photo-1542442828-287217bfb87f?auto=format&fit=crop&w=150&q=80" alt="<?php echo htmlspecialchars($event['organizer_name']); ?>">
          </div>
          <div>
            <div class="organizer-role">Event Organizer</div>
            <div class="organizer-name"><?php echo htmlspecialchars($event['organizer_name']); ?></div>
          </div>
        </div>
      </div>

      <h2>About Event</h2>
      <div class="about-text">
        <p><?php echo nl2br(htmlspecialchars($event['description'] ?? '')); ?></p>
        <?php if (!empty($event['address'])): ?>
          <p><strong>Address:</strong> <?php echo htmlspecialchars($event['address']); ?></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($details['interests'])): ?>
      <div class="event-interests-section">
        <h2>Interests</h2>
        <div class="event-interests-flex">
          <?php foreach ($details['interests'] as $interest): ?>
            <span class="interest-pill"><?php echo htmlspecialchars($interest['interest_name']); ?></span>
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
          <?php if ($isInviteOnly): ?>
            <span class="reg-badge">Invite Only</span>
          <?php endif; ?>
        </div>
        <div class="reg-sub">
          <span class="reg-status reg-status-<?php echo strtolower($state); ?>"><?php echo $statusLabels[$state]; ?></span>
        </div>
        <div class="reg-count">
          <?php echo $details['registeredCount']; ?> / <?php echo (int) $event['capacity']; ?> registered
        </div>
        <div class="reg-deadline">
          Registration closes <?php echo date('M j, Y', strtotime($event['registration_close'])); ?>
        </div>
        <?php if ($error): ?>
          <p class="reg-error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <?php if ($state === 'OPEN'): ?>
          <button class="btn-primary reg-cta">
            <?php echo $isInviteOnly ? 'Request to Join' : 'Register Now'; ?> &rarr;
          </button>
        <?php else: ?>
          <button class="btn-primary reg-cta" disabled><?php echo $buttonLabels[$state]; ?></button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php if ($state === 'OPEN'): ?>
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
      <h1><?php echo htmlspecialchars($event['name']); ?></h1>
      <div class="hosted-by">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M3 9h18" stroke="currentColor" stroke-width="1.6"/></svg>
        Hosted by <?php echo htmlspecialchars($event['organizer_name']); ?>
      </div>

      <div class="meta-row">
        <span class="meta-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          <?php echo date('M j', strtotime($event['event_date'])) . ', ' . date('g:i A', strtotime($event['start_time'])); ?>
        </span>
        <span class="meta-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
          <?php echo htmlspecialchars($event['location']); ?>
        </span>
        <span class="meta-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><line x1="10" y1="7" x2="10" y2="17" stroke="currentColor" stroke-width="1.4" stroke-dasharray="1.8 2" stroke-linecap="round"/></svg>
          Free Entry
        </span>
      </div>

      <div class="attending-row">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="8" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M4 18c0-2.6 2.2-4.7 5-4.7s5 2.1 5 4.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="17" cy="9" r="2" stroke="currentColor" stroke-width="1.6"/><path d="M15 13.5c2 0 4.5 1.8 4.5 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
        <?php echo $details['registeredCount']; ?> Attending
      </div>

      <div class="info-box">
        <div class="info-box-header">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><line x1="12" y1="11" x2="12" y2="16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="8" r="0.9" fill="currentColor"/></svg>
          What happens next?
        </div>
        <?php if ($isInviteOnly): ?>
        <div class="info-list-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 6.5l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          <div>
            <div class="info-list-title">Organizer Approval</div>
            <div class="info-list-sub">This is an invite-only event. The organizer will review your request.</div>
          </div>
        </div>
        <?php else: ?>
        <div class="info-list-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 6.5l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
          <div>
            <div class="info-list-title">Confirmation &amp; Event Pass</div>
            <div class="info-list-sub">Your registration is confirmed immediately.</div>
          </div>
        </div>
        <?php endif; ?>
        <div class="info-list-item">
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M2 12s3.8-6.5 10-6.5S22 12 22 12s-3.8 6.5-10 6.5S2 12 2 12z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.6"/></svg>
          <div>
            <div class="info-list-title">Compatibility Matching</div>
            <div class="info-list-sub">Networking unlocks after you check in at the event.</div>
          </div>
        </div>
      </div>

      <form method="post">
      <label class="check-row">
        <input type="checkbox" name="agree" value="1" required>
        <div>
          <div class="check-label">I agree to the Community Guidelines <span class="req">*</span></div>
          <div class="check-sub">Read our code of conduct for a safe and respectful event.</div>
        </div>
      </label>

      <div class="modal-actions">
        <button type="button" class="btn-cancel" id="modalCancelBtn">Cancel</button>
        <button type="submit" class="btn-primary" id="registerSubmitBtn">
          <?php echo $isInviteOnly ? 'Send Request' : 'Register Now'; ?>
          <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 12h14M13 6l6 6-6 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("registrationModal");
    const openBtn = document.querySelector(".reg-cta");
    const closeBtns = [document.getElementById("modalCloseBtn"), document.getElementById("modalCancelBtn")];

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

    modal.querySelector("form").addEventListener("submit", () => {
      document.getElementById("registerSubmitBtn").disabled = true;
    });

    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.remove("active");
        document.body.style.overflow = "";
      }
    });
  });
</script>
<?php endif; ?>
</body>
</html>
