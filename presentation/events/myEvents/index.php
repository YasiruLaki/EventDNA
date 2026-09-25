<?php
session_start();
require_once "../../../data/database.php";
require_once "../../../application/controllers/RegistrationController.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}

$controller = new RegistrationController($conn);
$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $result = $controller->cancel($_SESSION['user_id'], (int) ($_POST['event_id'] ?? 0));

    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
}

$registrations = $controller->getMyRegistrations($_SESSION['user_id']);

$badgeLabels = [
    'REGISTERED' => 'REGISTERED',
    'APPROVED' => 'APPROVED',
    'PENDING' => 'PENDING',
    'REJECTED' => 'DECLINED',
    'REMOVED' => 'REMOVED'
];
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
          <a href="../ExploreEvents/index.html" class="nav-link">Find Events</a>
          <a href="index.php" class="nav-link active">My Events</a>
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

<div class="dashboard-shell">

  <header class="hero-row">
    <div class="hero-copy">
      <h1 class="page-title">My Events</h1>
      <p class="supporting-copy">Manage your registered events and past attendance.</p>
    </div>
  </header>

  <?php if ($message): ?>
    <p class="flash flash-success"><?php echo htmlspecialchars($message); ?></p>
  <?php endif; ?>
  <?php if ($error): ?>
    <p class="flash flash-error"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <div class="dashboard-content main-grid">
    <div class="main-col">
      <div class="section-title-row">
        <h2>Upcoming Events</h2>
      </div>

      <?php if (empty($registrations['upcoming'])): ?>
      <div class="empty-state" style="text-align: center; padding: 4rem 2rem; background: rgba(255, 255, 255, 0.92); border: 1px solid rgba(226, 232, 240, 0.92); border-radius: 8px; margin-top: 1.5rem;">
        <h3 style="font-size: 1.25rem; color: #0f172a; margin-bottom: 0.5rem;">No upcoming events</h3>
        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 1.5rem;">You haven't registered for any upcoming events yet.</p>
        <a href="../ExploreEvents/index.html" class="btn-primary" style="display: inline-flex; padding: 0.75rem 1.5rem; border-radius: 8px;">Discover Events &rarr;</a>
      </div>
      <?php else: ?>
      <div class="events-grid">
        <?php foreach ($registrations['upcoming'] as $registration): ?>
        <div class="event-card-vertical">
          <div class="event-thumb" style="background: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=800&q=80') center/cover;">
            <div class="event-thumb-brand">EventDNA</div>
          </div>

          <div class="event-body">
            <div class="event-info-top">
              <div class="tag-row">
                <span class="tag"><?php echo $registration['visibility'] === 'INVITE_ONLY' ? 'Invite Only' : 'Public'; ?></span>
                <?php if ($registration['event_status'] === 'CANCELLED'): ?>
                  <span class="tag">Event Cancelled</span>
                <?php endif; ?>
              </div>
              <span class="registered-badge badge-<?php echo strtolower($registration['status']); ?>"><?php echo $badgeLabels[$registration['status']]; ?></span>
            </div>

            <h3><?php echo htmlspecialchars($registration['name']); ?></h3>

            <div class="event-meta">
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                <?php echo date('M j, Y', strtotime($registration['event_date'])); ?> &middot; <?php echo date('g:i A', strtotime($registration['start_time'])); ?>
              </span>
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
                <?php echo htmlspecialchars($registration['location']); ?>
              </span>
            </div>

            <div class="event-actions">
              <a href="../eventView/index.php?id=<?php echo $registration['event_id']; ?>" class="btn-primary">View Details</a>
              <?php if (in_array($registration['status'], ['REGISTERED', 'APPROVED', 'PENDING']) && $registration['event_status'] === 'ACTIVE'): ?>
              <form method="post" onsubmit="return confirm('Cancel your registration for this event?');">
                <input type="hidden" name="event_id" value="<?php echo $registration['event_id']; ?>">
                <button type="submit" class="btn-secondary btn-cancel-reg"><?php echo $registration['status'] === 'PENDING' ? 'Withdraw Request' : 'Cancel Registration'; ?></button>
              </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($registrations['past'])): ?>
      <div class="section-title-row" style="margin-top: 3.5rem;">
        <h2>Past Events</h2>
      </div>

      <?php foreach ($registrations['past'] as $registration): ?>
      <div class="event-card-horizontal past-event">
        <div class="event-thumb" style="background: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=800&q=80') center/cover; opacity: 0.85;">
          <div class="event-thumb-brand">EventDNA</div>
        </div>

        <div class="event-body">
          <div class="event-info-top">
            <div class="tag-row">
              <span class="tag"><?php echo $registration['visibility'] === 'INVITE_ONLY' ? 'Invite Only' : 'Public'; ?></span>
            </div>
            <span class="registered-badge badge-<?php echo strtolower($registration['status']); ?>"><?php echo $badgeLabels[$registration['status']]; ?></span>
          </div>

          <h3 style="color: var(--text-secondary);"><?php echo htmlspecialchars($registration['name']); ?></h3>

          <div class="event-meta">
            <span class="meta-item">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
              <?php echo date('M j, Y', strtotime($registration['event_date'])); ?>
            </span>
            <span class="meta-item">
              <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
              <?php echo htmlspecialchars($registration['location']); ?>
            </span>
          </div>

          <div class="event-actions">
            <a href="../eventView/index.php?id=<?php echo $registration['event_id']; ?>" class="btn-outline" style="border: 1px solid var(--border-color); color: var(--text-secondary); padding: 0.6rem 1.25rem; font-size: 0.82rem; border-radius: 8px; font-weight: 500;">View Details</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="side-col">
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-num"><?php echo count($registrations['upcoming']); ?></div>
          <div class="stat-label">Upcoming</div>
        </div>
        <div class="stat-card">
          <div class="stat-num"><?php echo count($registrations['past']); ?></div>
          <div class="stat-label">Past</div>
        </div>
      </div>

      <?php if (!empty($registrations['upcoming'])): ?>
      <div class="month-card">
        <h4>Coming Up</h4>
        <?php foreach (array_slice($registrations['upcoming'], 0, 3) as $registration): ?>
        <div class="month-item">
          <div class="month-date">
            <span class="m"><?php echo date('M', strtotime($registration['event_date'])); ?></span>
            <span class="d"><?php echo date('j', strtotime($registration['event_date'])); ?></span>
          </div>
          <div>
            <div class="month-item-title"><?php echo htmlspecialchars($registration['name']); ?></div>
            <div class="month-item-sub"><?php echo htmlspecialchars($registration['location']); ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>
