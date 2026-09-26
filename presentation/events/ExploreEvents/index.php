<?php
require_once __DIR__ . "/../includes/guard.php";
require_once "../../../data/database.php";
require_once "../../../application/controllers/EventController.php";

$eventController = new EventController($conn);
$search = trim($_GET['q'] ?? '');
$interestId = (int)($_GET['interest'] ?? 0);
$explore = $eventController->getExploreEvents($search, $interestId);
$featured = $explore['featured'];
$isFiltered = $search !== '' || $interestId > 0;
$activeNav = 'explore';

// Builds an explore URL that keeps the current search while changing the interest filter
function filter_url($search, $interestId) {
    $params = array_filter(['q' => $search, 'interest' => $interestId ?: null]);
    return 'index.php' . ($params ? '?' . http_build_query($params) : '');
}

function seats_text($event) {
    return $event['remaining_seats'] === 0 ? 'Fully booked' : seats_label($event['remaining_seats']);
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

<?php include __DIR__ . "/../includes/nav.php"; ?>

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
          <form class="search-bar-premium" method="get" action="index.php">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><line x1="21" y1="21" x2="16.5" y2="16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <input type="text" name="q" value="<?= h($search) ?>" maxlength="100" placeholder="Search events, locations, or organizers...">
            <?php if ($interestId > 0): ?>
              <input type="hidden" name="interest" value="<?= $interestId ?>">
            <?php endif; ?>
            <button type="submit" class="search-filter-btn">Search</button>
          </form>
          <?php if ($explore['interests']): ?>
          <div class="category-pills">
            <a href="<?= h(filter_url($search, 0)) ?>" class="pill<?= $interestId === 0 ? ' pill-active' : '' ?>">All</a>
            <?php foreach ($explore['interests'] as $interest): ?>
              <a href="<?= h(filter_url($search, (int)$interest['interest_id'])) ?>" class="pill<?= $interestId === (int)$interest['interest_id'] ? ' pill-active' : '' ?>"><?= h($interest['interest_name']) ?></a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </section>

      <?php if (!$featured): ?>
      <div class="empty-state">
        <h3><?= $isFiltered ? 'No events match your search' : 'No upcoming events yet' ?></h3>
        <p><?= $isFiltered ? 'Try a different search term or interest.' : 'Check back soon for new events.' ?></p>
        <?php if ($isFiltered): ?>
          <a href="index.php" class="btn-primary">Clear Filters</a>
        <?php endif; ?>
      </div>
      <?php else: ?>

      <!-- Featured event -->
      <article class="feature-card">
        <div class="feature-cover">
          <?php if ($featured['cover_photo']): ?>
            <img src="<?= h(cover_url($featured['cover_photo'])) ?>" alt="<?= h($featured['name']) ?> cover">
          <?php endif; ?>
          <span class="date-tag feature-date"><?= h(date('M j', strtotime($featured['event_date']))) ?></span>
        </div>
        <div class="feature-info">
            <div class="feature-badges">
            <span class="pill pill-primary"><?= $featured['display_status'] === 'Live' ? 'Happening Now' : 'Up Next' ?></span>
            <?php if ($featured['primary_interest']): ?>
              <span class="pill"><?= h($featured['primary_interest']) ?></span>
            <?php endif; ?>
            </div>
            <div class="feature-copy">
            <h2><?= h($featured['name']) ?></h2>
            <div class="feature-meta">
                <span><?= h(format_event_date($featured['event_date'])) ?> &middot; <?= h(format_time($featured['start_time'])) ?></span>
                <span><?= h($featured['location']) ?></span>
                <span><?= (int)$featured['registered_count'] ?> attending &middot; <?= h(seats_text($featured)) ?></span>
            </div>
            <?php if ($featured['description']): ?>
            <p class="desc">
                <?= h(mb_strimwidth($featured['description'], 0, 280, '…')) ?>
            </p>
            <?php endif; ?>
            </div>
            <div class="feature-actions">
            <a class="btn-primary" href="../eventView/index.php?id=<?= (int)$featured['event_id'] ?>">View Event &rarr;</a>
            </div>
        </div>
      </article>

      <?php if ($explore['events']): ?>
      <section class="section-row events-section">
        <div class="section-head">
          <h3>Upcoming Events</h3>
        </div>

        <div class="recommendation-row">
          <?php foreach ($explore['events'] as $i => $event): ?>
          <article class="event-card">
            <div class="event-image grad-<?= $i % 3 + 1 ?>"<?php if ($event['cover_photo']): ?> style="background: url('<?= h(cover_url($event['cover_photo'])) ?>') center/cover;"<?php endif; ?>>
              <span class="date-tag"><?= h(date('M j', strtotime($event['event_date']))) ?> &middot; <?= h(format_time($event['start_time'])) ?></span>
            </div>
            <div class="event-body">
              <?php if ($event['primary_interest']): ?>
                <span class="event-chip"><?= h($event['primary_interest']) ?></span>
              <?php endif; ?>
              <h4><?= h($event['name']) ?></h4>
              <p><?= h($event['location']) ?> &bull; <?= (int)$event['registered_count'] ?> attending</p>
              <div class="event-card-footer">
                <span class="event-price"><?= h(seats_text($event)) ?></span>
                <a href="../eventView/index.php?id=<?= (int)$event['event_id'] ?>" class="btn-view">View Details</a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php endif; ?>

    </main>
  </div>

</body>
</html>
