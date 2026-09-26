<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../data/database.php";
require_once "../../application/controllers/EventController.php";

$eventController = new EventController($conn);
$dashboard = $eventController->getOrganizerDashboard($organizerId);
$events = $dashboard["events"];
$activeNav = 'events';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Events - EventDNA</title>
    <link rel="stylesheet" href="../attendee/dashboard/styles.css" />
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .event-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color, #e2e8f0);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
        }

        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .event-image {
            position: relative;
            height: 160px;
            background: var(--bg-color, #f1f5f9);
        }

        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .date-tag {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: white;
            color: var(--secondary, #0f172a);
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .event-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .event-chip {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            background: var(--primary-tint, #f3f0ff);
            color: var(--primary, #4f10ff);
            align-self: flex-start;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
        }

        .event-body h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1.25rem;
            color: var(--secondary, #0f172a);
            font-weight: 700;
        }

        .event-body p {
            margin: 0 0 1.5rem 0;
            color: var(--text-secondary, #64748b);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .event-card-actions {
            margin-top: auto;
            display: flex;
            gap: 0.5rem;
        }

        .event-card-actions a {
            flex: 1;
            text-align: center;
            padding: 0.6rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-edit {
            background: var(--primary, #4f10ff);
            color: white;
        }

        .btn-edit:hover {
            background: var(--primary-hover, #3b00d6);
        }

        .btn-manage {
            background: var(--tertiary, #f1f5f9);
            color: var(--secondary, #0f172a);
        }

        .btn-manage:hover {
            background: #e2e8f0;
        }
    </style>
</head>

<body style="background-color: #f8fafc; min-height: 100vh;">
    <?php include 'includes/nav.php'; ?>

    <div class="dashboard-shell">
        <main class="dashboard-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div>
                    <h1 class="page-title" style="margin: 0; font-size: 1.75rem;">My Events</h1>
                    <p class="supporting-copy" style="margin: 0.5rem 0 0 0;">Manage and edit your upcoming and past
                        events.</p>
                </div>
                <a href="create-event.php" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 8px;">
                    Create New Event
                </a>
            </div>

            <?php if (empty($events)): ?>
                <div
                    style="text-align: center; padding: 4rem; background: white; border-radius: 12px; border: 1px dashed #cbd5e1;">
                    <h3 style="color: var(--secondary); margin-bottom: 0.5rem;">No Events Found</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">You haven't created any events yet.</p>
                    <a href="create-event.php" class="btn-primary"
                        style="padding: 0.75rem 1.5rem; border-radius: 8px;">Create Your First Event</a>
                </div>
            <?php else: ?>
                <section class="events-grid">
                    <?php foreach ($events as $event): ?>
                        <article class="event-card">
                            <div class="event-image">
                                <?php if (!empty($event['cover_photo'])): ?>
                                    <img src="../../<?= htmlspecialchars($event['cover_photo']) ?>" alt="Cover Photo">
                                <?php else: ?>
                                    <div
                                        style="width: 100%; height: 100%; background: var(--tertiary); display: flex; align-items: center; justify-content: center; color: var(--text-tertiary);">
                                        No Cover</div>
                                <?php endif; ?>
                                <span class="date-tag"><?= date('M d', strtotime($event['event_date'])) ?></span>
                            </div>
                            <div class="event-body">
                                <span class="event-chip"><?= htmlspecialchars($event['display_status']) ?></span>
                                <h4><?= htmlspecialchars($event['name']) ?></h4>
                                <p>
                                    <i data-lucide="map-pin" style="width: 16px; height: 16px;"></i>
                                    <?= htmlspecialchars($event['location']) ?>
                                </p>
                                <div class="event-card-actions">
                                    <a href="create-event.php?id=<?= $event['event_id'] ?>" class="btn-edit">Edit</a>
                                    <a href="event-details.php?id=<?= $event['event_id'] ?>" class="btn-manage">Manage</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>

</html>