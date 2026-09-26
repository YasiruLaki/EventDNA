<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../data/database.php";
require_once "../../application/controllers/EventController.php";

$eventController = new EventController($conn);
$eventId = (int)($_GET['id'] ?? 0);
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST['action'] ?? '') === 'cancel') {
    if (!csrf_valid()) {
        $error = "Your session expired. Please try again.";
    } else {
        $result = $eventController->cancelEvent($organizerId, $eventId);
        if ($result["success"]) {
            header("Location: event-details.php?id=" . $eventId . "&cancelled=1");
            exit;
        }
        $error = $result["message"];
    }
}

$event = $eventController->getOwnedEvent($organizerId, $eventId);
if (!$event) {
    http_response_code(404);
    die("Event not found.");
}

$notice = isset($_GET['saved']) ? "Event saved." : (isset($_GET['cancelled']) ? "Event cancelled." : "");
$registrationColors = ['Open' => 'var(--success)', 'Full' => 'var(--danger)'];
$activeNav = 'events';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= h($event['name']) ?> - EventDNA</title>
  <link rel="stylesheet" href="../attendee/dashboard/styles.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .manage-nav {
      display: flex;
      gap: 2rem;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 2rem;
    }
    .manage-tab {
      padding: 1rem 0;
      color: var(--text-secondary);
      font-weight: 600;
      font-size: 0.95rem;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      background: none;
      border-top: none;
      border-left: none;
      border-right: none;
      transition: all 0.2s;
    }
    .manage-tab:hover {
      color: var(--secondary);
    }
    .manage-tab.active {
      color: var(--primary);
      border-bottom-color: var(--primary);
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(5px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .manage-card {
      background: rgba(255, 255, 255, 0.95);
      border: 1px solid rgba(226, 232, 240, 0.9);
      border-radius: 12px;
      padding: 2rem;
    }
    .manage-card h3 {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--secondary);
      margin: 0 0 1.5rem 0;
    }
    .data-row {
      display: flex;
      justify-content: space-between;
      padding: 1rem 0;
      border-bottom: 1px solid var(--border-color);
    }
    .data-row:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
    .data-label {
      color: var(--text-secondary);
      font-weight: 600;
      font-size: 0.95rem;
    }
    .data-value {
      color: var(--secondary);
      font-weight: 600;
      font-size: 0.95rem;
      text-align: right;
    }
    .flash {
      border-radius: 8px;
      padding: 0.9rem 1.25rem;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
      font-weight: 600;
    }
    .flash-ok {
      background: rgba(22, 163, 74, 0.08);
      color: var(--success);
    }
    .flash-error {
      background: rgba(220, 38, 38, 0.06);
      color: var(--danger);
    }
  </style>
</head>
<body style="background-color: #f9f9f9; min-height: 100vh;">
  <nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="dashboard.php" class="nav-logo">
          <img src="../../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="dashboard.php" class="nav-link">Dashboard</a>
          <a href="dashboard.php#events" class="nav-link">My Events</a>
          <a href="create-event.html" class="nav-link">Create Event</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&q=80" alt="Profile" class="nav-avatar" />
            <span class="nav-profile-name">Hanan</span>
            <i data-lucide="chevron-down" style="width:16px;height:16px;"></i>
          </button>
          <div class="nav-dropdown">
            <a href="../auth/login/index.php" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="dashboard-shell">
    <main class="dashboard-content" style="max-width: 900px; margin: 0 auto;">

      <?php if ($notice): ?>
        <div class="flash flash-ok" role="status"><?= h($notice) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="flash flash-error" role="alert"><?= h($error) ?></div>
      <?php endif; ?>

      <div style="margin-bottom: 2rem;">
        <h1 class="page-title" style="margin-bottom: 0.5rem;"><?= h($event['name']) ?></h1>
        <div style="display: flex; gap: 1rem; align-items: center; color: var(--text-secondary); font-size: 0.95rem; flex-wrap: wrap;">
          <span style="<?= status_badge_style($event['display_status']) ?> font-size: 0.75rem; font-weight: 800; padding: 0.3rem 0.6rem; border-radius: 999px; text-transform: uppercase;"><?= h($event['display_status']) ?></span>
          <span><?= h(format_event_date($event['event_date'])) ?> · <?= h($event['location']) ?></span>
        </div>
      </div>

      <nav class="manage-nav">
        <button class="manage-tab active" data-target="overview">Overview</button>
        <button class="manage-tab" data-target="attendees">Attendees</button>
        <button class="manage-tab" data-target="qr">QR Check-in</button>
        <button class="manage-tab" data-target="analytics">Analytics</button>
        <button class="manage-tab" data-target="reports">Reports</button>
      </nav>

      <!-- Overview Tab -->
      <section id="overview" class="tab-content active">
        <div class="manage-card">
          <h3>Event Information</h3>
          
          <?php if (!empty($event['cover_photo'])): ?>
            <img src="../../<?= h($event['cover_photo']) ?>" alt="Cover photo" style="width: 100%; max-height: 240px; object-fit: cover; border-radius: 8px; margin-bottom: 1.5rem;">
          <?php endif; ?>

          <div class="data-row">
            <span class="data-label">Event Name</span>
            <span class="data-value"><?= h($event['name']) ?></span>
          </div>
          <?php if (!empty($event['description'])): ?>
            <div class="data-row" style="flex-direction: column; gap: 0.5rem;">
              <span class="data-label">Description</span>
              <span style="color: var(--secondary); font-size: 0.95rem; line-height: 1.6; white-space: pre-line;"><?= h($event['description']) ?></span>
            </div>
          <?php endif; ?>
          <div class="data-row">
            <span class="data-label">Date</span>
            <span class="data-value"><?= h(format_event_date($event['event_date'])) ?></span>
          </div>
          <div class="data-row">
            <span class="data-label">Time</span>
            <span class="data-value"><?= h(format_time_range($event['start_time'], $event['end_time'])) ?></span>
          </div>
          <div class="data-row">
            <span class="data-label">Location</span>
            <span class="data-value"><?= h($event['location']) ?><?= $event['address'] ? '<br><span style="font-weight: 400; color: var(--text-secondary);">' . h($event['address']) . '</span>' : '' ?></span>
          </div>
          <div class="data-row">
            <span class="data-label">Capacity</span>
            <span class="data-value"><?= (int)$event['registered_count'] ?> / <?= (int)$event['capacity'] ?> registered</span>
          </div>
          <div class="data-row">
            <span class="data-label">Visibility</span>
            <span class="data-value"><?= $event['visibility'] === 'PUBLIC' ? 'Public' : 'Invite Only' ?></span>
          </div>
          <div class="data-row">
            <span class="data-label">Registration Window</span>
            <span class="data-value"><?= h(format_event_date($event['registration_open'])) ?> – <?= h(format_event_date($event['registration_close'])) ?></span>
          </div>
          <div class="data-row">
            <span class="data-label">Registration</span>
            <span class="data-value" style="color: <?= $registrationColors[$event['registration_state']] ?? 'var(--text-secondary)' ?>;"><?= h($event['registration_state']) ?></span>
          </div>
          <div class="data-row" style="margin-bottom: 1.5rem;">
            <span class="data-label">Interest Tags</span>
            <span class="data-value"><?= $event['interest_names'] ? h(implode(', ', $event['interest_names'])) : '<span style="font-weight: 400; color: var(--text-secondary);">None</span>' ?></span>
          </div>

          <?php if ($event['editable']): ?>
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
              <a href="create-event.php?id=<?= (int)$event['event_id'] ?>" class="btn-primary" style="padding: 0.75rem 1.5rem; text-decoration: none;">Edit Event</a>
              <button id="cancelEventBtn" class="btn-secondary" style="padding: 0.75rem 1.5rem; color: var(--danger); border-color: rgba(220, 38, 38, 0.2); background: rgba(220, 38, 38, 0.05);">Cancel Event</button>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Attendees Tab -->
      <section id="attendees" class="tab-content">
        <div class="manage-card" style="padding: 0; overflow: hidden;">
          <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Attendees</h3>
            <div style="position: relative; width: 250px;">
              <i data-lucide="search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-tertiary);"></i>
              <input type="text" placeholder="Search attendees..." style="width: 100%; padding: 0.5rem 1rem 0.5rem 2.5rem; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 0.9rem; background: #f8fafc; color: var(--secondary);">
            </div>
          </div>
          
          <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
              <tr style="background: rgba(248, 250, 252, 0.8); border-bottom: 1px solid rgba(226, 232, 240, 0.9); font-size: 0.85rem; color: var(--text-secondary); font-weight: 600;">
                <th style="padding: 1rem 1.5rem;">Name</th>
                <th style="padding: 1rem 1.5rem;">Email</th>
                <th style="padding: 1rem 1.5rem;">Status</th>
                <th style="padding: 1rem 1.5rem;">Action</th>
              </tr>
            </thead>
            <tbody style="font-size: 0.95rem; color: var(--secondary);">
              <tr style="border-bottom: 1px solid rgba(226, 232, 240, 0.9);">
                <td style="padding: 1rem 1.5rem; font-weight: 600;">Kamal Perera</td>
                <td style="padding: 1rem 1.5rem; color: var(--text-secondary);">kamal@email.com</td>
                <td style="padding: 1rem 1.5rem;"><span style="color: var(--success); font-weight: 600; font-size: 0.85rem;">Checked-in</span></td>
                <td style="padding: 1rem 1.5rem;"><button class="btn-text" style="color: var(--danger); font-size: 0.85rem; font-weight: 600;">Remove</button></td>
              </tr>
              <tr style="border-bottom: 1px solid rgba(226, 232, 240, 0.9);">
                <td style="padding: 1rem 1.5rem; font-weight: 600;">Sarah Fernando</td>
                <td style="padding: 1rem 1.5rem; color: var(--text-secondary);">sarah@email.com</td>
                <td style="padding: 1rem 1.5rem;"><span style="color: var(--text-secondary); font-weight: 600; font-size: 0.85rem;">Registered</span></td>
                <td style="padding: 1rem 1.5rem;"><button class="btn-text" style="color: var(--danger); font-size: 0.85rem; font-weight: 600;">Remove</button></td>
              </tr>
              <tr style="border-bottom: 1px solid rgba(226, 232, 240, 0.9);">
                <td style="padding: 1rem 1.5rem; font-weight: 600;">James Doe</td>
                <td style="padding: 1rem 1.5rem; color: var(--text-secondary);">james@email.com</td>
                <td style="padding: 1rem 1.5rem;"><span style="color: #D97706; font-weight: 600; font-size: 0.85rem;">Pending</span></td>
                <td style="padding: 1rem 1.5rem; display: flex; gap: 0.5rem;">
                  <button class="btn-text" style="color: var(--primary); font-size: 0.85rem; font-weight: 600;">Approve</button>
                  <button class="btn-text" style="color: var(--danger); font-size: 0.85rem; font-weight: 600;">Reject</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- QR Check-in Tab -->
      <section id="qr" class="tab-content">
        <div class="manage-card" style="text-align: center; max-width: 500px; margin: 0 auto;">
          <h3>Event QR</h3>
          <p style="color: var(--text-secondary); margin-bottom: 2rem; font-size: 0.95rem;">Show this QR code at the event entrance.</p>
          
          <div style="background: #fff; padding: 2rem; border-radius: 12px; border: 1px solid var(--border-color); display: inline-block; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
            <!-- Mock QR Code visual -->
            <div style="width: 200px; height: 200px; background: url('https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=AIInnovationSummit2026') no-repeat center center; background-size: contain;"></div>
          </div>

          <h4 style="font-size: 1.1rem; font-weight: 700; color: var(--secondary); margin: 0 0 1rem 0;"><?= h($event['name']) ?></h4>

          <div style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 2rem;">
            <div style="text-align: center;">
              <span style="display: block; font-size: 1.5rem; font-weight: 800; color: var(--secondary);"><?= (int)$event['registered_count'] ?></span>
              <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600;">Registered</span>
            </div>
            <div style="text-align: center;">
              <span style="display: block; font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?= (int)$event['checked_in_count'] ?></span>
              <span style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600;">Checked-in</span>
            </div>
          </div>

          <button class="btn-secondary" style="padding: 0.75rem 1.5rem;">Regenerate QR</button>
        </div>
      </section>

      <!-- Analytics Tab -->
      <section id="analytics" class="tab-content">
        <div class="manage-card">
          <h3>Event Analytics</h3>
          
          <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 3rem;">
            <div style="padding: 1.5rem; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border-color);">
              <span style="display: block; color: var(--text-secondary); font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem;">Registrations</span>
              <span style="display: block; font-size: 2rem; font-weight: 800; color: var(--secondary);"><?= (int)$event['registered_count'] ?></span>
            </div>
            <div style="padding: 1.5rem; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border-color);">
              <span style="display: block; color: var(--text-secondary); font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem;">Check-ins</span>
              <span style="display: block; font-size: 2rem; font-weight: 800; color: var(--secondary);"><?= (int)$event['checked_in_count'] ?></span>
            </div>
            <div style="padding: 1.5rem; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border-color);">
              <span style="display: block; color: var(--text-secondary); font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem;">Attendance Rate</span>
              <span style="display: block; font-size: 2rem; font-weight: 800; color: var(--primary);"><?= (int)$event['registered_count'] > 0 ? round($event['checked_in_count'] / $event['registered_count'] * 100) : 0 ?>%</span>
            </div>
          </div>

          <h4 style="font-size: 1.1rem; font-weight: 700; color: var(--secondary); margin: 0 0 1.5rem 0;">Interest Distribution</h4>
          <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div>
              <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; color: var(--secondary); margin-bottom: 0.4rem;">
                <span>Technology</span>
                <span>42%</span>
              </div>
              <div style="height: 6px; background: #e2e8f0; border-radius: 999px;"><div style="width: 42%; height: 100%; background: var(--primary); border-radius: 999px;"></div></div>
            </div>
            <div>
              <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; color: var(--secondary); margin-bottom: 0.4rem;">
                <span>Business</span>
                <span>28%</span>
              </div>
              <div style="height: 6px; background: #e2e8f0; border-radius: 999px;"><div style="width: 28%; height: 100%; background: #8568FF; border-radius: 999px;"></div></div>
            </div>
            <div>
              <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; color: var(--secondary); margin-bottom: 0.4rem;">
                <span>Research</span>
                <span>18%</span>
              </div>
              <div style="height: 6px; background: #e2e8f0; border-radius: 999px;"><div style="width: 18%; height: 100%; background: #a78bfa; border-radius: 999px;"></div></div>
            </div>
            <div>
              <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; color: var(--secondary); margin-bottom: 0.4rem;">
                <span>Other</span>
                <span>12%</span>
              </div>
              <div style="height: 6px; background: #e2e8f0; border-radius: 999px;"><div style="width: 12%; height: 100%; background: #cbd5e1; border-radius: 999px;"></div></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Reports Tab -->
      <section id="reports" class="tab-content">
        <div class="manage-card">
          <h3>Event Reports</h3>
          
          <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 12px; background: #f8fafc;">
              <div>
                <strong style="display: block; font-size: 1rem; color: var(--secondary); margin-bottom: 0.25rem;">Registration Report</strong>
                <span style="color: var(--text-secondary); font-size: 0.9rem;">List of registered attendees.</span>
              </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 12px; background: #f8fafc;">
              <div>
                <strong style="display: block; font-size: 1rem; color: var(--secondary); margin-bottom: 0.25rem;">Attendance Report</strong>
                <span style="color: var(--text-secondary); font-size: 0.9rem;">Check-in and attendance information.</span>
              </div>
            </div>
          </div>

          <div style="display: flex; gap: 1rem;">
            <button class="btn-primary" style="padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="download" style="width:16px;height:16px;"></i> Export CSV</button>
            <button class="btn-secondary" style="padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="file-text" style="width:16px;height:16px;"></i> Export PDF</button>
          </div>
        </div>
      </section>

    </main>
  </div>

  <!-- Cancel Modal -->
  <div id="cancelModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #fff; padding: 2.5rem; border-radius: 12px; max-width: 450px; width: 90%; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
      <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--secondary); margin: 0 0 1rem 0;">Cancel this event?</h3>
      <p style="color: var(--text-secondary); margin: 0 0 2rem 0; line-height: 1.6;">Are you sure you want to cancel <strong><?= h($event['name']) ?></strong>? This can't be undone. Registration will close and the <?= (int)$event['registered_count'] ?> registered attendees will see the event as cancelled.</p>

      <form method="post" style="display: flex; gap: 1rem; justify-content: flex-end;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="cancel">
        <button type="button" id="closeModalBtn" class="btn-secondary" style="padding: 0.75rem 1.5rem;">Keep Event</button>
        <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem; background: var(--danger); border-color: var(--danger);">Cancel Event</button>
      </form>
    </div>
  </div>

  <script>
    lucide.createIcons();
    
    // Tab switching logic
    const tabs = document.querySelectorAll('.manage-tab');
    const contents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        contents.forEach(c => c.classList.remove('active'));
        
        tab.classList.add('active');
        document.getElementById(tab.dataset.target).classList.add('active');
      });
    });
    
    // Auto-select tab if hash is present
    if (window.location.hash) {
      const hash = window.location.hash.substring(1);
      const targetTab = document.querySelector(`.manage-tab[data-target="${hash}"]`);
      if (targetTab) {
        targetTab.click();
      }
    }
    
    // Simple profile menu toggle
    const profileBtn = document.querySelector('.nav-profile-btn');
    const profileDropdown = document.querySelector('.nav-dropdown');
    
    if (profileBtn && profileDropdown) {
      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
      });
      
      document.addEventListener('click', () => {
        profileDropdown.classList.remove('show');
      });
    }

    // Modal Logic
    const cancelBtn = document.getElementById('cancelEventBtn');
    const cancelModal = document.getElementById('cancelModal');
    const closeModalBtn = document.getElementById('closeModalBtn');

    if(cancelBtn && cancelModal && closeModalBtn) {
      cancelBtn.addEventListener('click', () => {
        cancelModal.style.display = 'flex';
      });
      closeModalBtn.addEventListener('click', () => {
        cancelModal.style.display = 'none';
      });
      cancelModal.addEventListener('click', (e) => {
        if(e.target === cancelModal) cancelModal.style.display = 'none';
      });
    }
  </script>
</body>
</html>
