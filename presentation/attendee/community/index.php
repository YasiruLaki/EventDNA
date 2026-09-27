<?php
require_once __DIR__ . '/../includes/guard.php';
require_once "../../../data/database.php";
require_once "../../../application/controllers/GroupController.php";

$groupController = new GroupController($conn);
$search = $_GET['q'] ?? '';
$tab = $_GET['tab'] ?? 'discover'; // discover or mygroups
$userId = (int)$_SESSION['user_id'];

if ($tab === 'mygroups') {
    $groups = $groupController->getMyJoinedGroups($userId);
} else {
    $groups = $groupController->getActiveGroups($search);
}

// Helper to handle cover photos
function coverUrl($path) {
    if (!$path) return '';
    return preg_match('#^https?://#i', $path) ? $path : '../../../' . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Communities - EventDNA</title>
  <link rel="stylesheet" href="../../../globals.css" />
  <link rel="stylesheet" href="../../events/ExploreEvents/styles.css">
  <style>
    .event-card { text-decoration: none; display: flex; flex-direction: column; }
    .event-card:hover { transform: translateY(-4px); }
  </style>
</head>
<body>
  <?php 
  $activeNav = 'communities';
  include '../includes/nav.php'; 
  ?>
  <div class="dashboard-shell">
    <main class="dashboard-content">
      
      <!-- Header Section -->
      <section class="section-row" style="margin-bottom: 2rem;">
        <div class="section-head" style="margin-bottom: 1rem;">
          <h1 style="font-size: 2.2rem; font-weight: 700; color: #0f172a;">Communities</h1>
        </div>
        <p class="supporting-copy" style="margin-bottom: 1.5rem; color: var(--text-secondary); max-width: 600px;">
          Discover groups, join communities that match your interests, and connect through discussions and shared resources.
        </p>

        <!-- Search Bar -->
        <div class="search-bar" style="max-width: 500px; margin-bottom: 2rem;">
          <form method="get" style="display: flex; gap: 0.5rem; width: 100%;">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
            <div class="search-input-wrapper" style="flex: 1; position: relative;">
              <svg style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 20px; color: var(--text-tertiary);" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search communities..." style="width: 100%; padding: 0.8rem 1rem 0.8rem 2.75rem; border: 1px solid var(--border-color); border-radius: 999px; font-size: 0.95rem; background: var(--surface-color);">
            </div>
            <button type="submit" class="btn-primary" style="padding: 0 1.5rem; border-radius: 999px;">Search</button>
          </form>
        </div>

        <!-- Filter Tabs (Pills) -->
        <div class="filter-scroll">
          <div class="filter-pills">
            <a href="?tab=discover&q=<?= urlencode($search) ?>" class="pill <?= $tab === 'discover' ? 'active' : '' ?>">Discover Groups</a>
            <a href="?tab=mygroups&q=<?= urlencode($search) ?>" class="pill <?= $tab === 'mygroups' ? 'active' : '' ?>">My Groups</a>
          </div>
        </div>
      </section>

      <!-- Communities Grid -->
      <section class="section-row events-section">
        <div class="section-head">
          <h3><?= $tab === 'discover' ? 'All Communities' : 'Your Communities' ?></h3>
        </div>

        <div class="recommendation-row">
          <?php foreach ($groups as $idx => $g): 
              $mainInterest = !empty($g['interests']) ? $g['interests'][0]['name'] : 'Community';
              $gradClass = 'grad-' . (($idx % 3) + 1);
          ?>
          <article class="event-card" onclick="window.location.href='view.php?id=<?= $g['group_id'] ?>'" style="cursor: pointer;">
            <!-- Dummy cover since groups don't have covers yet, or use abstract gradient -->
            <div class="event-image <?= $gradClass ?>" style="height: 140px; display: flex; align-items: center; justify-content: center;">
              <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="width: 48px; opacity: 0.8;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <div class="event-body">
              <span class="event-chip"><?= htmlspecialchars($mainInterest) ?></span>
              <h4><?= htmlspecialchars($g['name']) ?></h4>
              <p><?= number_format($g['member_count']) ?> members</p>
              <div class="event-card-footer" style="margin-top: auto; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <span class="event-price" style="font-weight: 400; font-size: 0.85rem; color: var(--text-secondary);"><?= htmlspecialchars(substr($g['description'], 0, 60)) ?>...</span>
                <a href="view.php?id=<?= $g['group_id'] ?>" class="btn-view">View</a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
          <?php if (empty($groups)): ?>
            <p style="color: var(--text-secondary); grid-column: 1 / -1; text-align: center; padding: 2rem 0;">No communities found.</p>
          <?php endif; ?>
        </div>
      </section>

    </main>
  </div>
</body>
</html>