<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../data/database.php";
require_once "../../application/controllers/GroupController.php";

$organizerId = $_SESSION['user_id'];
$groupController = new GroupController($conn);
$interests = $groupController->getInterests();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
        'interests' => $_POST['interests'] ?? []
    ];
    $result = $groupController->createGroup($organizerId, $data);
    if ($result['success']) {
        header("Location: view-group.php?id=" . $result['group_id']);
        exit;
    }
    $errors = $result['errors'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Community - EventDNA</title>
    <link rel="stylesheet" href="../attendee/dashboard/styles.css" />
    <link rel="stylesheet" href="organizer.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        
        .form-card { background: #ffffff; border-radius: 16px; padding: 2.5rem; margin-bottom: 2rem; box-shadow: 0 4px 24px -4px rgba(0,0,0,0.03), 0 2px 8px -2px rgba(0,0,0,0.02); max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--secondary); }
        .form-input, .form-textarea { width: 100%; padding: 0.85rem 1.25rem; border: 1px solid var(--border-color); border-radius: 10px; background: #f8fafc; font-family: inherit; transition: all 0.2s; }
        .form-input:focus, .form-textarea:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px var(--primary-tint); background: #fff; }
        .tag-list { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .tag-chip { padding: 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 999px; font-size: 0.85rem; cursor: pointer; background: #f8fafc; transition: all 0.2s; font-weight: 500; }
        .tag-chip input { display: none; }
        .tag-chip:has(input:checked) { border-color: var(--primary); background: var(--primary); color: #fff; }
        .tag-chip:hover:not(:has(input:checked)) { background: #e2e8f0; }
    </style>
</head>
<body class="org-page-bg">
    <?php include 'includes/nav.php'; ?>
    <div class="dashboard-shell">
        <main class="dashboard-content">
            <div class="org-hero-header" style="max-width: 800px; margin: 0 auto 2rem;">
                <h1 class="page-title">Create Community</h1>
                <p class="supporting-copy">Build a dedicated space for your attendees to connect and share.</p>
            </div>

            <?php if ($errors): ?>
                <div class="error-box" role="alert" style="max-width: 800px; margin: 0 auto 1.5rem;">
                    <strong>Please fix the following:</strong>
                    <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" class="form-card">
                <div class="form-group">
                    <label>Community Name</label>
                    <input type="text" name="name" class="form-input" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="e.g. AI & Machine Learning Sri Lanka">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-textarea" rows="5" required placeholder="What is this community about? Who should join?"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Interest Tags (Used for discoverability)</label>
                    <div class="tag-list">
                        <?php foreach ($interests as $interest): ?>
                            <label class="tag-chip">
                                <input type="checkbox" name="interests[]" value="<?= $interest['interest_id'] ?>" <?= in_array($interest['interest_id'], $_POST['interests'] ?? []) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($interest['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9;">
                    <a href="manage-groups.php" class="btn-secondary" style="padding: 0.75rem 1.5rem; text-decoration: none;">Cancel</a>
                    <button type="submit" class="btn-primary org-btn-pad">Launch Community <i data-lucide="rocket" style="width: 16px; margin-left: 0.4rem;"></i></button>
                </div>
            </form>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
