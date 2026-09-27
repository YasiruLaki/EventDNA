<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../data/database.php";
require_once "../../application/controllers/GroupController.php";

$activeNav = 'communities';
$organizerId = $_SESSION['user_id'];
$groupController = new GroupController($conn);
$groups = $groupController->getGroupsByOrganizer($organizerId);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Communities - EventDNA Organizer</title>
  <link rel="stylesheet" href="../attendee/dashboard/styles.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet" href="organizer.css" />
</head>
<body class="org-page-bg" >
    <?php include 'includes/nav.php'; ?>

    <div class="dashboard-shell">
        <main class="dashboard-content" >
            
            <div class="org-page-header-flex" style="margin-bottom: 2rem;" >
                <div>
                    <h1 class="page-title org-page-title-lg" >My Communities</h1>
                    <p class="supporting-copy org-page-subtitle" >Manage your event communities and moderate discussions.</p>
                </div>
                <a href="create-group.php" class="btn-primary org-btn-rounded" style="text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="plus" style="width: 18px;"></i> Create Community
                </a>
            </div>

            <?php if (empty($groups)): ?>
                <div class="org-empty-state-dashed" >
                    <div class="org-empty-icon"><i data-lucide="users" style="width: 32px;"></i></div>
                    <h3 class="org-empty-state-title" >No communities yet</h3>
                    <p class="org-empty-state-desc" >Create a group to start engaging with your attendees before and after events.</p>
                    <a href="create-group.php" class="btn-primary" style="margin-top: 1.5rem; display: inline-block; text-decoration: none;">Launch a Community</a>
                </div>
            <?php else: ?>
                <div class="group-grid">
                    <?php foreach ($groups as $g): ?>
                        <div class="manage-card">
                            <div class="card-banner">
                                <div class="card-icon-wrap"><i data-lucide="users" style="width: 24px;"></i></div>
                            </div>
                            <div class="card-body">
                                <h3 style="margin-bottom: 0.5rem; color: var(--secondary); font-size: 1.15rem; font-weight: 700; line-height: 1.3;"><?= htmlspecialchars($g['name']) ?></h3>
                                <div style="display: flex; gap: 1rem; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem;">
                                    <span style="display: flex; align-items: center; gap: 0.25rem;"><i data-lucide="users" style="width: 14px;"></i> <?= $g['member_count'] ?> members</span>
                                    <span style="display: flex; align-items: center; gap: 0.25rem;"><i data-lucide="calendar" style="width: 14px;"></i> <?= date('M d, Y', strtotime($g['created_at'])) ?></span>
                                </div>
                                <p style="font-size: 0.9rem; color: var(--text-primary); line-height: 1.5; margin-bottom: 1.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($g['description']) ?>
                                </p>
                                <?php if ($g['status'] === 'ARCHIVED'): ?>
                                    <span style="align-self: flex-start; padding: 0.2rem 0.6rem; background: #fee2e2; color: #ef4444; border-radius: 999px; font-size: 0.75rem; font-weight: 700; margin-bottom: 1rem;">ARCHIVED</span>
                                <?php endif; ?>
                                
                                <div class="manage-actions">
                                    <a href="view-group.php?id=<?= $g['group_id'] ?>" class="btn-primary btn-sm"><i data-lucide="eye" style="width: 14px; display: inline; margin-bottom: -2px;"></i> View Hub</a>
                                    <a href="edit-group.php?id=<?= $g['group_id'] ?>" class="btn-secondary btn-sm"><i data-lucide="settings" style="width: 14px; display: inline; margin-bottom: -2px;"></i> Manage</a>
                                    <form action="delete-group.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this group?');">
                                        <input type="hidden" name="group_id" value="<?= $g['group_id'] ?>">
                                        <button type="submit" class="btn-secondary btn-sm" style="color: #ef4444; border-color: #fee2e2; background: #fee2e2;"><i data-lucide="trash-2" style="width: 14px; display: inline; margin-bottom: -2px;"></i> Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
