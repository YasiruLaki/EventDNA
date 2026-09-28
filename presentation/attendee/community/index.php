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
          <h1 class="page-title">Communities</h1>
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
              <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search communities..." style="width: 100%; padding: 0.8rem 1rem 0.8rem 2.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 0.95rem; background: var(--surface-color);">
            </div>
            <button type="submit" class="btn-primary" style="padding: 0 1.5rem; border-radius: 4px;">Search</button>
          </form>
        </div>

        <!-- Standard Tabs -->
        <div class="standard-tabs" style="display: flex; gap: 2rem; border-bottom: 1px solid var(--border-color); margin-bottom: 2rem;">
            <a href="?tab=discover&q=<?= urlencode($search) ?>" style="text-decoration: none; padding-bottom: 0.75rem; font-weight: 500; font-size: 0.95rem; color: <?= $tab === 'discover' ? 'var(--primary)' : 'var(--text-secondary)' ?>; border-bottom: 2px solid <?= $tab === 'discover' ? 'var(--primary)' : 'transparent' ?>;">Discover Groups</a>
            <a href="?tab=mygroups&q=<?= urlencode($search) ?>" style="text-decoration: none; padding-bottom: 0.75rem; font-weight: 500; font-size: 0.95rem; color: <?= $tab === 'mygroups' ? 'var(--primary)' : 'var(--text-secondary)' ?>; border-bottom: 2px solid <?= $tab === 'mygroups' ? 'var(--primary)' : 'transparent' ?>;">My Groups</a>
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
              $memberCount = isset($g['member_count']) ? $g['member_count'] : 1;
          ?>
          <article class="event-card" onclick="window.location.href='view.php?id=<?= $g['group_id'] ?>'" style="cursor: pointer; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; border-top: 4px solid var(--primary); background: #fff; border-left: 1px solid var(--border-color); border-right: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); border-radius: 4px;">
            <div style="display: flex; flex-direction: column; align-items: flex-start; gap: 0.75rem;">
              <h4 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--secondary);"><?= htmlspecialchars($g['name']) ?></h4>
              <span class="event-chip" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-secondary); border-radius: 4px; padding: 0.25rem 0.5rem; font-size: 0.8rem; font-weight: 500;"><?= htmlspecialchars($mainInterest) ?></span>
            </div>
            <p style="margin: 0; color: var(--text-secondary); font-size: 0.95rem; line-height: 1.5; flex-grow: 1;">
              <?= htmlspecialchars(strlen($g['description']) > 120 ? substr($g['description'], 0, 120) . '...' : $g['description']) ?>
            </p>
            <div class="event-card-footer" style="margin-top: auto; border-top: 1px solid var(--border-color); padding-top: 1rem; display: flex; align-items: center; justify-content: space-between;">
              <span style="font-weight: 500; font-size: 0.9rem; color: var(--text-secondary); display: flex; align-items: center; gap: 0.4rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <?= number_format($memberCount) ?> members
              </span>
              <a href="view.php?id=<?= $g['group_id'] ?>" class="btn-secondary" style="padding: 0.4rem 1rem; font-size: 0.85rem; border-radius: 4px; text-decoration: none;">View</a>
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