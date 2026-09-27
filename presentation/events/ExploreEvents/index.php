<?php
session_start();
require_once __DIR__ . '/../../attendee/includes/avatar.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}
$attendeeName = $_SESSION['full_name'] ?? 'Attendee';

require_once __DIR__ . '/../../../data/database.php';
require_once __DIR__ . '/../../../data/EventRepository.php';

$eventRepo = new EventRepository($conn);
$events = $eventRepo->getUpcomingEvents();

// Pick a featured event (e.g. the first one or one with most capacity)
$featuredEvent = !empty($events) ? $events[0] : null;

// The rest of the events
$regularEvents = count($events) > 1 ? array_slice($events, 1) : [];

// Helper to format date
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
<title>Discover Events - EventDNA</title>
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
          <a href="#" class="nav-link active">Find Events</a>
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

  <div class="dashboard-shell">
    <main class="dashboard-content">
      
      <!-- Hero & Search -->
      <section class="hero-row hero-header">
        <div class="hero-copy">
          <h1 class="page-title">Discover Events</h1>
          <p class="supporting-copy">
            Discover events that match your interests and networking goals.
          </p>
        </div>
        
        <div class="search-container">
          <div class="search-bar-premium">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><line x1="21" y1="21" x2="16.5" y2="16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <input type="text" placeholder="Search events, topics, or organizers...">
            <button class="search-filter-btn">Filter</button>
          </div>
          <div class="category-pills">
            <span class="pill pill-active">All</span>
            <span class="pill">Business</span>
            <span class="pill">Technology</span>
            <span class="pill">Healthcare</span>
            <span class="pill">Design</span>
            <span class="pill">Arts</span>
          </div>
        </div>
      </section>

      <!-- Featured event -->
      <?php if ($featuredEvent): 
        // Need to get interest names for the chip (just get the first one)
        $interests = $eventRepo->getEventInterestNames($featuredEvent['event_id']);
        $mainInterest = !empty($interests) ? $interests[0] : 'Event';
      ?>
      <article class="feature-card">
        <div class="feature-cover">
          <?php if (!empty($featuredEvent['cover_photo'])): ?>
            <img src="<?= htmlspecialchars(coverUrl($featuredEvent['cover_photo'])) ?>" alt="<?= htmlspecialchars($featuredEvent['name']) ?>">
          <?php endif; ?>
          <span class="date-tag feature-date"><?= formatShortDate($featuredEvent['event_date']) ?></span>
        </div>
        <div class="feature-info">
            <div class="feature-badges">
            <span class="pill pill-primary">Featured</span>
            <span class="pill"><?= htmlspecialchars($mainInterest) ?></span>
            </div>
            <div class="feature-copy">
            <h2><?= htmlspecialchars($featuredEvent['name']) ?></h2>
            <div class="feature-meta">
                <span><?= formatDate($featuredEvent['event_date']) ?></span>
                <span><?= htmlspecialchars($featuredEvent['location']) ?></span>
                <span><?= number_format($featuredEvent['capacity']) ?> spots total</span>
            </div>
            <p class="desc">
                <?= nl2br(htmlspecialchars($featuredEvent['description'])) ?>
            </p>
            </div>
            <div class="feature-actions">
            <a class="btn-primary" href="../eventView/index.php?id=<?= $featuredEvent['event_id'] ?>">View Event &rarr;</a>
            </div>
        </div>
      </article>
      <?php endif; ?>

      <section class="section-row events-section">
        <div class="section-head">
          <h3>Upcoming Events</h3>
        </div>

        <div class="recommendation-row">
          <?php foreach ($regularEvents as $idx => $ev): 
              $interests = $eventRepo->getEventInterestNames($ev['event_id']);
              $mainInterest = !empty($interests) ? $interests[0] : 'Event';
              $spotsLeft = max(0, $ev['capacity'] - $ev['registered_count']);
              $gradClass = 'grad-' . (($idx % 3) + 1);
          ?>
          <article class="event-card">
            <div class="event-image <?= $gradClass ?>" style="background-image: url('<?= htmlspecialchars(coverUrl($ev['cover_photo'])) ?>'); background-size: cover; background-position: center;">
              <span class="date-tag"><?= formatShortDate($ev['event_date']) ?> &middot; <?= formatTime($ev['start_time']) ?></span>
            </div>
            <div class="event-body">
              <span class="event-chip"><?= htmlspecialchars($mainInterest) ?></span>
              <h4><?= htmlspecialchars($ev['name']) ?></h4>
              <p><?= htmlspecialchars($ev['location']) ?> &bull; <?= number_format($ev['registered_count']) ?> attending</p>
              <div class="event-card-footer">
                <span class="event-price"><?= $spotsLeft > 0 ? $spotsLeft . ' spots left' : 'Sold Out' ?></span>
                <a href="../eventView/index.php?id=<?= $ev['event_id'] ?>" class="btn-view">View Details</a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
          <?php if (empty($regularEvents) && empty($featuredEvent)): ?>
            <p style="color: var(--text-secondary); grid-column: 1 / -1; text-align: center; padding: 2rem 0;">No upcoming events found.</p>
          <?php endif; ?>
        </div>
        
        <div class="load-more-wrap">
          <button class="btn-secondary">Load More Events</button>
        </div>
      </section>

    </main>
  </div>

</body>
</html>