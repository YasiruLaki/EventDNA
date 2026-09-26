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
    .manage-hero {
      position: relative;
      overflow: visible;
      min-height: 400px;
      display: flex;
      align-items: flex-end;
      background: linear-gradient(0deg, rgba(0, 0, 0, 0.904) 0%, rgba(7, 16, 32, 0.6) 45%, rgba(7, 16, 32, 0.32) 100%),
        url('../../<?= h($event['cover_photo'] ?: 'https://images.unsplash.com/photo-1542442828-287217bfb87f?auto=format&fit=crop&w=1920&q=80') ?>') center center / cover no-repeat;
    }
    .manage-hero::before {
      content: "";
      position: absolute;
      inset: 0;
    }
    .hero-inner {
      position: relative;
      z-index: 1;
      color: #fff;
      padding-bottom: 4.5rem;
      width: min(1280px, calc(100% - 2rem));
      margin: 0 auto;
    }
    .hero-badge {
      display: inline-flex;
      align-items: center;
      min-height: 30px;
      padding: 0 0.8rem;
      border-radius: 999px;
      background: rgba(79, 16, 255, 0.42);
      color: #fff;
      font-size: 0.76rem;
      font-weight: 700;
      backdrop-filter: blur(6px);
    }
    .hero-inner h1 {
      margin: 0.9rem 0 0;
      color: #fff;
      font-size: clamp(2.5rem, 4vw, 4rem);
      line-height: 1.1;
      letter-spacing: -0.04em;
      font-weight: 800;
    }
    .hero-inner p {
      margin: 1rem 0 0;
      max-width: 700px;
      color: rgba(255, 255, 255, 0.84);
      font-size: 1rem;
      line-height: 1.6;
    }
    .info-bar-wrap {
      position: relative;
      z-index: 2;
      margin-top: -2rem;
      margin-bottom: 3rem;
    }
    .info-bar-container {
      width: min(1280px, calc(100% - 2rem));
      margin: 0 auto;
    }
    .info-bar {
      padding: 1.25rem;
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 0.75rem;
      background: rgb(255, 255, 255);
      border: 1px solid rgba(226, 232, 240, 0.92);
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.04);
    }
    .info-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.5rem;
    }
    .info-icon {
      flex-shrink: 0;
      width: 44px;
      height: 44px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 12px;
      background: rgba(79, 16, 255, 0.08);
      color: var(--primary);
    }
    .info-icon svg {
      width: 20px;
      height: 20px;
    }
    .info-label {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-weight: 700;
      color: var(--text-secondary);
      margin-bottom: 0.15rem;
    }
    .info-value {
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--text-primary);
    }
    @media (max-width: 900px) {
      .info-bar {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    @media (max-width: 640px) {
      .info-bar {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <link rel="stylesheet" href="organizer.css" />
</head>
<body class="org-page-wrapper" >
  <?php include 'includes/nav.php'; ?>

  <?php if ($notice): ?>
    <div class="flash flash-ok" role="status" style="width: min(1280px, calc(100% - 2rem)); margin: 1rem auto;"><?= h($notice) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="flash flash-error" role="alert" style="width: min(1280px, calc(100% - 2rem)); margin: 1rem auto;"><?= h($error) ?></div>
  <?php endif; ?>

  <section class="manage-hero">
    <div class="hero-inner">
      <span class="hero-badge" style="<?= status_badge_style($event['display_status']) ?>"><?= h($event['display_status']) ?></span>
      <h1><?= h($event['name']) ?></h1>
      <?php if (!empty($event['description'])): ?>
      <p><?= h(strlen($event['description']) > 150 ? substr($event['description'], 0, 150) . '...' : $event['description']) ?></p>
      <?php endif; ?>
    </div>
  </section>

  <div class="info-bar-wrap">
    <div class="info-bar-container">
      <div class="info-bar">
        <div class="info-item">
          <div class="info-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.6"/><line x1="8" y1="3" x2="8" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><line x1="16" y1="3" x2="16" y2="7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          </div>
          <div>
            <div class="info-label">Date</div>
            <div class="info-value"><?= h(format_event_date($event['event_date'])) ?></div>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3.5 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div>
            <div class="info-label">Time</div>
            <div class="info-value"><?= h(format_time_range($event['start_time'], $event['end_time'])) ?></div>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
          </div>
          <div>
            <div class="info-label">Location</div>
            <div class="info-value"><?= h($event['location']) ?></div>
          </div>
        </div>
        <div class="info-item">
          <div class="info-icon">
            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="9" cy="8" r="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M4 18c0-2.6 2.2-4.7 5-4.7s5 2.1 5 4.7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="17" cy="9" r="2" stroke="currentColor" stroke-width="1.6"/><path d="M15 13.5c2 0 4.5 1.8 4.5 4.2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
          </div>
          <div>
            <div class="info-label">Event Type</div>
            <div class="info-value">In-person</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="dashboard-shell">
    <main class="dashboard-content">

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
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0;">Event Information</h3>
            <a href="edit-event.php?id=<?= $eventId ?>" class="btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.85rem; border-radius: 6px;">Edit Details</a>
          </div>

          <?php if (!empty($event['description'])): ?>
            <div class="data-row org-flex-col-gap-0-5" >
              <span class="data-label">Full Description</span>
              <span class="org-text-pre-line" ><?= h($event['description']) ?></span>
            </div>
          <?php endif; ?>
          <div class="data-row">
            <span class="data-label">Address</span>
            <span class="data-value"><?= h($event['address'] ?: 'Not specified') ?></span>
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
          <div class="data-row org-mb-1-5" >
            <span class="data-label">Interest Tags</span>
            <span class="data-value"><?= $event['interest_names'] ? h(implode(', ', $event['interest_names'])) : '<span class="org-font-normal-sec" >None</span>' ?></span>
          </div>

          <?php if ($event['editable']): ?>
            <div class="org-actions-row" >
              <a href="create-event.php?id=<?= (int)$event['event_id'] ?>" class="btn-primary" style="padding: 0.75rem 1.5rem; text-decoration: none;">Edit Event</a>
              <button id="cancelEventBtn" class="btn-secondary org-btn-danger-outline" >Cancel Event</button>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Attendees Tab -->
      <section id="attendees" class="tab-content">
        <div class="manage-card org-p-0-overflow-hidden" >
          <div class="org-card-header" >
            <h3 class="org-m-0" >Attendees</h3>
            <div class="org-relative-w-250" >
              <i class="org-icon-left-sm" data-lucide="search" ></i>
              <input class="org-input-sm-icon" type="text" placeholder="Search attendees..." >
            </div>
          </div>
          
          <table class="org-table-base" >
            <thead>
              <tr class="org-table-header-sm" >
                <th class="org-table-cell-pad" >Name</th>
                <th class="org-table-cell-pad" >Email</th>
                <th class="org-table-cell-pad" >Status</th>
                <th class="org-table-cell-pad" >Action</th>
              </tr>
            </thead>
            <tbody class="org-text-secondary-md" >
              <tr class="org-border-b" >
                <td class="org-font-semibold-pad" >Kamal Perera</td>
                <td class="org-text-sec-pad" >kamal@email.com</td>
                <td class="org-table-cell-pad" ><span class="org-text-success-sm" >Checked-in</span></td>
                <td class="org-table-cell-pad" ><button class="btn-text org-text-danger-sm" >Remove</button></td>
              </tr>
              <tr class="org-border-b" >
                <td class="org-font-semibold-pad" >Sarah Fernando</td>
                <td class="org-text-sec-pad" >sarah@email.com</td>
                <td class="org-table-cell-pad" ><span class="org-text-sec-sm" >Registered</span></td>
                <td class="org-table-cell-pad" ><button class="btn-text org-text-danger-sm" >Remove</button></td>
              </tr>
              <tr class="org-border-b" >
                <td class="org-font-semibold-pad" >James Doe</td>
                <td class="org-text-sec-pad" >james@email.com</td>
                <td class="org-table-cell-pad" ><span class="org-text-warning-sm" >Pending</span></td>
                <td class="org-flex-gap-0-5-pad" >
                  <button class="btn-text org-text-primary-sm" >Approve</button>
                  <button class="btn-text org-text-danger-sm" >Reject</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- QR Check-in Tab -->
      <section id="qr" class="tab-content">
        <div class="manage-card org-modal-center" >
          <h3>Event QR</h3>
          <p class="org-modal-subtitle" >Show this QR code at the event entrance.</p>
          
          <div class="org-qr-card" >
            <!-- Mock QR Code visual -->
            <div class="org-qr-image" ></div>
          </div>

          <h4 class="org-qr-title" ><?= h($event['name']) ?></h4>

          <div class="org-qr-stats-row" >
            <div class="org-text-center" >
              <span class="org-stat-lg" ><?= (int)$event['registered_count'] ?></span>
              <span class="org-stat-label-sm" >Registered</span>
            </div>
            <div class="org-text-center" >
              <span class="org-stat-lg-primary" ><?= (int)$event['checked_in_count'] ?></span>
              <span class="org-stat-label-sm" >Checked-in</span>
            </div>
          </div>

          <button class="btn-secondary org-btn-pad" >Regenerate QR</button>
        </div>
      </section>

      <!-- Analytics Tab -->
      <section id="analytics" class="tab-content">
        <div class="manage-card">
          <h3>Event Analytics</h3>
          
          <div class="org-grid-3-mb-3" >
            <div class="org-info-card" >
              <span class="org-info-label" >Registrations</span>
              <span class="org-info-val" ><?= (int)$event['registered_count'] ?></span>
            </div>
            <div class="org-info-card" >
              <span class="org-info-label" >Check-ins</span>
              <span class="org-info-val" ><?= (int)$event['checked_in_count'] ?></span>
            </div>
            <div class="org-info-card" >
              <span class="org-info-label" >Attendance Rate</span>
              <span class="org-info-val-primary" ><?= (int)$event['registered_count'] > 0 ? round($event['checked_in_count'] / $event['registered_count'] * 100) : 0 ?>%</span>
            </div>
          </div>

          <h4 class="org-section-title-sm" >Interest Distribution</h4>
          <div class="org-flex-col-gap-1" >
            <div>
              <div class="org-flex-between-sm" >
                <span>Technology</span>
                <span>42%</span>
              </div>
              <div class="org-progress-track-sm" ><div class="org-progress-fill-primary" ></div></div>
            </div>
            <div>
              <div class="org-flex-between-sm" >
                <span>Business</span>
                <span>28%</span>
              </div>
              <div class="org-progress-track-sm" ><div class="org-progress-fill-purple" ></div></div>
            </div>
            <div>
              <div class="org-flex-between-sm" >
                <span>Research</span>
                <span>18%</span>
              </div>
              <div class="org-progress-track-sm" ><div class="org-progress-fill-light" ></div></div>
            </div>
            <div>
              <div class="org-flex-between-sm" >
                <span>Other</span>
                <span>12%</span>
              </div>
              <div class="org-progress-track-sm" ><div class="org-progress-fill-gray" ></div></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Reports Tab -->
      <section id="reports" class="tab-content">
        <div class="manage-card">
          <h3>Event Reports</h3>
          
          <div class="org-flex-col-gap-1-5" >
            <div class="org-info-row" >
              <div>
                <strong class="org-info-row-title" >Registration Report</strong>
                <span class="org-info-row-desc" >List of registered attendees.</span>
              </div>
            </div>
            <div class="org-info-row" >
              <div>
                <strong class="org-info-row-title" >Attendance Report</strong>
                <span class="org-info-row-desc" >Check-in and attendance information.</span>
              </div>
            </div>
          </div>

          <div class="org-flex-gap-1" >
            <button class="btn-primary org-btn-with-icon" ><i class="org-icon-md" data-lucide="download" ></i> Export CSV</button>
            <button class="btn-secondary org-btn-with-icon" ><i class="org-icon-md" data-lucide="file-text" ></i> Export PDF</button>
          </div>
        </div>
      </section>

    </main>
  </div>

  <!-- Cancel Modal -->
  <div class="org-modal-overlay" id="cancelModal" >
    <div class="org-modal-content" >
      <h3 class="org-modal-title" >Cancel this event?</h3>
      <p class="org-modal-desc" >Are you sure you want to cancel <strong><?= h($event['name']) ?></strong>? This can't be undone. Registration will close and the <?= (int)$event['registered_count'] ?> registered attendees will see the event as cancelled.</p>

      <form class="org-modal-actions" method="post" >
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="cancel">
        <button type="button" id="closeModalBtn" class="btn-secondary org-btn-pad" >Keep Event</button>
        <button type="submit" class="btn-primary org-btn-danger" >Cancel Event</button>
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
