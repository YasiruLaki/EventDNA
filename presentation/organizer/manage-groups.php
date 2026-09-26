<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../data/database.php";

$activeNav = 'communities';
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
        <main class="dashboard-content org-container-md" >
            
            <div class="org-page-header-flex" >
                <div>
                    <h1 class="page-title org-page-title-lg" >Communities</h1>
                    <p class="supporting-copy org-page-subtitle" >Manage your event communities and groups.</p>
                </div>
                <button class="btn-primary org-btn-rounded" >
                    Create Community
                </button>
            </div>

            <div class="org-empty-state-dashed" >
                <h3 class="org-empty-state-title" >Communities Feature Coming Soon</h3>
                <p class="org-empty-state-desc" >You will be able to manage groups and communities linked to your events here.</p>
            </div>

        </main>
    </div>

    <script>
      lucide.createIcons();
    </script>
</body>
</html>
