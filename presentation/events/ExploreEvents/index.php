<?php
session_start();
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
      <article class="feature-card">
        <div class="feature-cover">
          <img src="https://orlandosydney.com/wp-content/uploads/2023/08/Business-Networking-Photo-Example-for-Professionals-at-the-ICC-Sydney-Convention-Centre.-Photography.-By-orlandosydney.com-OS1_7380.jpg" alt="Featured event cover">
          <span class="date-tag feature-date">Oct 12–14</span>
        </div>
        <div class="feature-info">
            <div class="feature-badges">
            <span class="pill pill-primary">Featured</span>
            <span class="pill">In-person</span>
            </div>
            <div class="feature-copy">
            <h2>Global Tech Innovators Summit 2026</h2>
            <div class="feature-meta">
                <span>Oct 12–14, 2026</span>
                <span>San Francisco, CA</span>
                <span>1,200+ attendees</span>
            </div>
            <p class="desc">
                Join industry leaders, founders, and investors for three days of keynotes,
                panels, and curated networking sessions built around this year's biggest
                shifts in technology.
            </p>
            </div>
            <div class="feature-actions">
            <a class="btn-primary" href="../eventView/index.php">View Event &rarr;</a>
            </div>
        </div>
      </article>

      <section class="section-row events-section">
        <div class="section-head">
          <h3>Upcoming Events</h3>
        </div>

        <div class="recommendation-row">
          
          <article class="event-card">
            <div class="event-image grad-1">
              <span class="date-tag">Sep 4 &middot; 9:00 AM</span>
            </div>
            <div class="event-body">
              <span class="event-chip">Design</span>
              <h4>UX/UI Masterclass: Designing for Humans</h4>
              <p>Colombo &bull; 320 attending</p>
              <div class="event-card-footer">
                <span class="event-price">120 spots left</span>
                <a href="../eventView/index.php" class="btn-view">View Details</a>
              </div>
            </div>
          </article>

          <article class="event-card">
            <div class="event-image grad-2">
              <span class="date-tag">Sep 10 &middot; 2:00 PM</span>
            </div>
            <div class="event-body">
              <span class="event-chip">Business</span>
              <h4>Seed to Series A: Founders Mixer</h4>
              <p>Kandy &bull; 540 attending</p>
              <div class="event-card-footer">
                <span class="event-price">Registration Open</span>
                <a href="../eventView/index.php" class="btn-view">View Details</a>
              </div>
            </div>
          </article>

          <article class="event-card">
            <div class="event-image grad-3">
              <span class="date-tag">Sep 18 &middot; 10:00 AM</span>
            </div>
            <div class="event-body">
              <span class="event-chip">Healthcare</span>
              <h4>HealthTech Innovators Gala</h4>
              <p>Galle &bull; 410 attending</p>
              <div class="event-card-footer">
                <span class="event-price">45 spots left</span>
                <a href="../eventView/index.php" class="btn-view">View Details</a>
              </div>
            </div>
          </article>

        </div>
        
        <div class="load-more-wrap">
          <button class="btn-secondary">Load More Events</button>
        </div>
      </section>

    </main>
  </div>

</body>
</html>