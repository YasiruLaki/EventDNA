<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../../auth/login.php"); exit; }
require_once "../../../data/database.php";
require_once "../../../application/controllers/GroupController.php";

$groupController = new GroupController($conn);
$search = $_GET['q'] ?? '';
$groups = $groupController->getActiveGroups($search);
$isOrganizer = (int)($_SESSION['role_id'] ?? 0) === 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community Hub - EventDNA</title>
    <link rel="stylesheet" href="../dashboard/styles.css" />
    <link rel="stylesheet" href="../../organizer/organizer.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        
        .group-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem; margin-top: 2rem; }
        .group-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; text-decoration: none; color: inherit; }
        .group-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.08), 0 4px 12px -4px rgba(0,0,0,0.04); border-color: var(--primary-tint); }
        .card-banner { height: 80px; background: linear-gradient(135deg, var(--primary) 0%, #4f46e5 100%); position: relative; }
        .card-icon-wrap { position: absolute; bottom: -20px; left: 1.5rem; width: 48px; height: 48px; background: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); color: var(--primary); }
        .card-body { padding: 2.5rem 1.5rem 1.5rem; flex: 1; display: flex; flex-direction: column; }
        .group-title { font-size: 1.15rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.4rem; line-height: 1.3; }
        .group-meta { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; }
        .group-desc { font-size: 0.9rem; color: var(--text-primary); line-height: 1.5; margin-bottom: 1.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .group-tags { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: auto; }
        .g-tag { background: var(--primary-tint); color: var(--primary); padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .search-bar-wrap { position: relative; max-width: 400px; width: 100%; }
        .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary); width: 18px; }
        .search-input { width: 100%; padding: 0.85rem 1rem 0.85rem 2.75rem; border: 1px solid var(--border-color); border-radius: 12px; font-family: inherit; font-size: 0.95rem; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .search-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px var(--primary-tint); }
    </style>
</head>
<body class="org-page-bg">
    <?php include '../includes/nav.php'; ?>
    <div class="dashboard-shell">
        <div class="org-hero-header" style="margin-bottom: 2rem; display: flex; flex-direction: row; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 class="page-title">Discover Communities</h1>
                <p class="supporting-copy">Find and join groups that match your interests.</p>
            </div>
            <form method="get" class="search-bar-wrap">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" name="q" class="search-input" value="<?= htmlspecialchars($search) ?>" placeholder="Search communities by name or topic...">
            </form>
        </div>

        <div class="group-grid">
            <?php foreach ($groups as $g): ?>
                <a href="view.php?id=<?= $g['group_id'] ?>" class="group-card">
                    <div class="card-banner">
                        <div class="card-icon-wrap"><i data-lucide="users" style="width: 24px;"></i></div>
                    </div>
                    <div class="card-body">
                        <h3 class="group-title"><?= htmlspecialchars($g['name']) ?></h3>
                        <div class="group-meta">
                            <i data-lucide="user" style="width: 14px;"></i> <?= $g['member_count'] ?> Members
                        </div>
                        <p class="group-desc"><?= htmlspecialchars($g['description']) ?></p>
                        <div class="group-tags">
                            <?php 
                            $tags = array_slice($g['interests'], 0, 3);
                            foreach ($tags as $tag): ?>
                                <span class="g-tag"><?= htmlspecialchars($tag['name']) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($g['interests']) > 3): ?>
                                <span class="g-tag" style="background: #e2e8f0; color: #475569;">+<?= count($g['interests']) - 3 ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($groups)): ?>
            <div class="org-empty-state-dashed" style="margin-top: 2rem;">
                <div class="org-empty-icon"><i data-lucide="search-x" style="width: 32px;"></i></div>
                <h3 class="org-empty-state-title">No communities found</h3>
                <p class="org-empty-state-desc">Try adjusting your search terms.</p>
            </div>
        <?php endif; ?>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
