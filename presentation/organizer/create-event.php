<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../data/database.php";
require_once "../../application/controllers/EventController.php";

$eventController = new EventController($conn);
$interests = $eventController->getInterests();

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $eventId > 0;
$errors = [];
$event = null;

if ($isEdit) {
    $event = $eventController->getOwnedEvent($organizerId, $eventId);
    if (!$event) {
        http_response_code(404);
        die("Event not found.");
    }
    if (!$event['editable']) {
        header("Location: event-details.php?id=" . $eventId);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_valid()) {
        $errors[] = "Your session expired. Please submit the form again.";
    } else {
        $input = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'event_date' => $_POST['event_date'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time' => $_POST['end_time'] ?? '',
            'location' => $_POST['location'] ?? '',
            'address' => $_POST['address'] ?? '',
            'capacity' => $_POST['capacity'] ?? '',
            'interests' => $_POST['interests'] ?? [],
            'registration_open' => $_POST['registration_open'] ?? '',
            'registration_close' => $_POST['registration_close'] ?? '',
            'visibility' => $_POST['visibility'] ?? '',
        ];
        $cover = $_FILES['cover_photo'] ?? null;

        $result = $isEdit
            ? $eventController->updateEvent($organizerId, $eventId, $input, $cover)
            : $eventController->createEvent($organizerId, $input, $cover);

        if ($result["success"]) {
            header("Location: event-details.php?id=" . $result["event_id"] . "&saved=1");
            exit;
        }
        $errors = $result["errors"];
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $form = $_POST;
    $form['interests'] = array_map('intval', (array)($_POST['interests'] ?? []));
} elseif ($isEdit) {
    $form = $event;
    $form['start_time'] = substr($event['start_time'], 0, 5);
    $form['end_time'] = substr($event['end_time'], 0, 5);
    $form['registration_open'] = substr($event['registration_open'], 0, 10);
    $form['registration_close'] = substr($event['registration_close'], 0, 10);
    $form['interests'] = array_map('intval', $event['interest_ids']);
} else {
    $form = ['visibility' => 'PUBLIC', 'interests' => [], 'registration_open' => date('Y-m-d')];
}

function field($name) {
    global $form;
    return h($form[$name] ?? '');
}

$activeNav = $isEdit ? 'events' : 'create';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $isEdit ? 'Edit Event' : 'Create Event' ?> - EventDNA</title>
  <link rel="stylesheet" href="../attendee/dashboard/styles.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .dashboard-shell {
      width: min(1500px, calc(100% - 2rem)) !important;
      margin: 0 auto;
    }
    .org-form-container {
      max-width: 100% !important;
    }
    .form-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 2.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 24px -4px rgba(0,0,0,0.03), 0 2px 8px -2px rgba(0,0,0,0.02);
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      column-gap: 3rem;
      row-gap: 1.5rem;
    }
    .form-group {
      margin-bottom: 0;
    }
    .form-group.full-width {
      grid-column: 1 / -1;
    }
    .org-form-section-title {
      grid-column: 1 / -1;
      margin-bottom: 0.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #f1f5f9;
    }
    .step-actions {
      grid-column: 1 / -1;
      display: flex;
      justify-content: space-between;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid #f1f5f9;
    }
    .form-group label {
      display: block;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 0.6rem;
      color: var(--secondary);
    }
    .form-input, .form-textarea, .form-select {
      width: 100%;
      padding: 0.85rem 1.25rem;
      border: 1px solid var(--border-color);
      border-radius: 10px;
      font-family: inherit;
      font-size: 0.95rem;
      background: #f8fafc;
      color: var(--secondary);
      transition: all 0.2s;
    }
    .form-input:focus, .form-textarea:focus, .form-select:focus {
      outline: none;
      border-color: var(--primary);
      background: #fff;
      box-shadow: 0 0 0 4px rgba(79, 16, 255, 0.08);
    }
    .form-input[type="file"] {
      padding: 0.5rem;
      background: #fff;
      border: 2px dashed #cbd5e1;
      display: flex;
      align-items: center;
    }
    .form-input[type="file"]::file-selector-button {
      background: var(--primary-tint);
      color: var(--primary);
      border: none;
      padding: 0.5rem 1.25rem;
      border-radius: 6px;
      font-weight: 600;
      font-size: 0.9rem;
      cursor: pointer;
      margin-right: 1rem;
      transition: background 0.2s;
    }
    .form-input[type="file"]::file-selector-button:hover {
      background: rgba(79, 16, 255, 0.15);
    }
    .form-input[type="file"]:hover {
      border-color: var(--primary);
      background: #f8fafc;
    }
    .form-row {
      grid-column: 1 / -1;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem 3rem;
    }
    .radio-group {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }
    .radio-label {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      padding: 1.25rem;
      border: 1px solid var(--border-color);
      border-radius: 10px;
      cursor: pointer;
      background: #fff;
      transition: all 0.2s;
    }
    .radio-label:has(input:checked) {
      border-color: var(--primary);
      background: var(--primary-tint);
      box-shadow: 0 2px 8px rgba(79, 16, 255, 0.05);
    }
    .radio-label input {
      accent-color: var(--primary);
      width: 20px;
      height: 20px;
      cursor: pointer;
    }
    .radio-content strong {
      display: block;
      font-size: 0.95rem;
      color: var(--secondary);
      margin-bottom: 0.25rem;
    }
    .radio-content span {
      font-size: 0.85rem;
      color: var(--text-secondary);
      line-height: 1.4;
      display: block;
    }
    .hint {
      font-size: 0.8rem;
      color: var(--text-secondary);
      margin-top: 0.5rem;
    }
    .tag-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.6rem;
      max-height: 220px;
      overflow-y: auto;
      padding: 0.25rem;
    }
    .tag-chip {
      display: inline-flex;
      align-items: center;
      padding: 0.5rem 1rem;
      border: 1px solid var(--border-color);
      border-radius: 999px;
      font-size: 0.85rem;
      cursor: pointer;
      background: #f8fafc;
      color: var(--secondary);
      transition: all 0.2s;
      user-select: none;
    }
    .tag-chip:hover {
      background: #f1f5f9;
      border-color: #cbd5e1;
    }
    .tag-chip input {
      display: none;
    }
    .tag-chip:has(input:checked) {
      border-color: var(--primary);
      background: var(--primary);
      color: #fff;
      font-weight: 600;
      box-shadow: 0 4px 12px rgba(79, 16, 255, 0.2);
    }
    .error-box {
      background: rgba(220, 38, 38, 0.06);
      border: 1px solid rgba(220, 38, 38, 0.25);
      color: var(--danger);
      border-radius: 12px;
      padding: 1.25rem 1.5rem;
      margin-bottom: 2rem;
      font-size: 0.95rem;
    }
    .error-box ul {
      margin: 0.75rem 0 0 1.25rem;
      padding: 0;
    }
    .cover-preview {
      width: 100%;
      max-height: 240px;
      object-fit: cover;
      border-radius: 12px;
      margin-bottom: 1rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .stepper-wrap {
      margin-bottom: 2.5rem;
    }
    .stepper {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 600px;
      margin: 0 auto;
    }
    .step {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
      position: relative;
      z-index: 2;
      width: 100px;
    }
    .step-circle {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #fff;
      border: 2px solid var(--border-color);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      color: var(--text-secondary);
      transition: all 0.3s;
    }
    .step.active .step-circle {
      border-color: var(--primary);
      background: var(--primary);
      color: #fff;
    }
    .step.completed .step-circle {
      border-color: var(--success);
      background: var(--success);
      color: #fff;
    }
    .step-label {
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-secondary);
      text-align: center;
    }
    .step.active .step-label {
      color: var(--primary);
    }
    .step-line {
      flex: 1;
      height: 2px;
      background: var(--border-color);
      margin: 0 -30px;
      margin-bottom: 1.5rem;
      z-index: 1;
      transition: all 0.3s;
    }
    .step-line.completed {
      background: var(--success);
    }
    .form-step {
      display: none;
      animation: fadeIn 0.3s ease;
    }
    .form-step.active {
      display: block;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .step-actions {
      display: flex;
      justify-content: space-between;
      margin-top: 2rem;
    }
    @media (max-width: 640px) {
      .form-row { flex-direction: column; gap: 0; }
      .form-card { padding: 1.5rem; }
    }
  </style>

  <link rel="stylesheet" href="organizer.css" />
</head>
<body class="org-page-wrapper" >
<?php include __DIR__ . "/includes/nav.php"; ?>

  <div class="dashboard-shell">
    <main class="dashboard-content org-form-container" >

      <div class="org-hero-header" >
        <h1 class="page-title"><?= $isEdit ? 'Edit Event' : 'Create Event' ?></h1>
        <p class="supporting-copy"><?= $isEdit ? 'Update the details of ' . h($event['name']) . '.' : 'Set up a new event for your organization.' ?></p>
      </div>

      <?php if ($errors): ?>
        <div class="error-box" role="alert">
          <strong>Please fix the following:</strong>
          <ul>
            <?php foreach ($errors as $error): ?>
              <li><?= h($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div class="stepper-wrap">
        <div class="stepper">
          <div class="step active" id="indicator-1">
            <div class="step-circle"><i data-lucide="info" style="width: 16px;"></i></div>
            <div class="step-label">Basic Info</div>
          </div>
          <div class="step-line" id="line-1"></div>
          <div class="step" id="indicator-2">
            <div class="step-circle"><i data-lucide="map-pin" style="width: 16px;"></i></div>
            <div class="step-label">Location</div>
          </div>
          <div class="step-line" id="line-2"></div>
          <div class="step" id="indicator-3">
            <div class="step-circle"><i data-lucide="users" style="width: 16px;"></i></div>
            <div class="step-label">Registration</div>
          </div>
        </div>
      </div>

      <form id="eventForm" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="form-step active" id="step-1">
          <div class="form-card">
          <h2 class="org-form-section-title" >Basic Information</h2>

          <div class="form-group">
            <label for="name">Event Name</label>
            <input type="text" id="name" name="name" class="form-input" maxlength="200" required
              placeholder="e.g. AI Innovation Summit 2026" value="<?= field('name') ?>">
          </div>

          <div class="form-group">
            <label for="cover_photo">Cover Photo</label>
            <?php if ($isEdit && !empty($event['cover_photo'])): ?>
              <img src="../../<?= h($event['cover_photo']) ?>" alt="Current cover photo" class="cover-preview">
            <?php endif; ?>
            <input type="file" id="cover_photo" name="cover_photo" class="form-input" accept="image/jpeg,image/png,image/webp">
            <p class="hint">JPG, PNG or WebP, up to 5 MB.<?= $isEdit ? ' Leave empty to keep the current photo.' : '' ?></p>
          </div>

          <div class="form-group full-width">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-textarea" rows="4"
              placeholder="Briefly describe what this event is about..."><?= field('description') ?></textarea>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="event_date">Date</label>
              <input type="date" id="event_date" name="event_date" class="form-input" required value="<?= field('event_date') ?>">
            </div>
            <div class="form-group">
              <label for="start_time">Start Time</label>
              <input type="time" id="start_time" name="start_time" class="form-input" required value="<?= field('start_time') ?>">
            </div>
            <div class="form-group">
              <label for="end_time">End Time</label>
              <input type="time" id="end_time" name="end_time" class="form-input" required value="<?= field('end_time') ?>">
            </div>
          </div>

          <div class="step-actions">
            <a href="<?= $isEdit ? 'event-details.php?id=' . $eventId : 'dashboard.php' ?>" class="btn-secondary" style="padding: 0.75rem 1.5rem; text-decoration: none;">Cancel</a>
            <button type="button" class="btn-primary org-btn-pad" onclick="nextStep(1)">Next Step <i data-lucide="arrow-right" style="width: 16px; margin-left: 0.4rem;"></i></button>
          </div>
        </div>

        <div class="form-step" id="step-2">
          <div class="form-card">
          <h2 class="org-form-section-title" >Location & Details</h2>

          <div class="form-group">
            <label for="location">Venue / Location</label>
            <input type="text" id="location" name="location" class="form-input" maxlength="200" required
              placeholder="e.g. BMICH, Colombo" value="<?= field('location') ?>">
          </div>

          <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" class="form-input" maxlength="300"
              placeholder="Full street address" value="<?= field('address') ?>">
          </div>

          <div class="form-group">
            <label for="capacity">Capacity</label>
            <input type="number" id="capacity" name="capacity" class="form-input" min="1" max="100000" required
              placeholder="Max attendees" value="<?= field('capacity') ?>">
            <?php if ($isEdit && (int)$event['registered_count'] > 0): ?>
              <p class="hint"><?= (int)$event['registered_count'] ?> people are already registered, so capacity can't go below that.</p>
            <?php endif; ?>
          </div>

          <div class="form-group org-mb-0" >
            <label>Interest Tags <span class="org-font-normal-sec" >(up to <?= EventController::MAX_INTERESTS ?>, used for matching)</span></label>
            <input type="text" id="tagFilter" class="form-input org-mb-0-75" placeholder="Filter tags..." >
            <div class="tag-list" id="tagList">
              <?php foreach ($interests as $interest): ?>
                <label class="tag-chip">
                  <input type="checkbox" name="interests[]" value="<?= (int)$interest['interest_id'] ?>"
                    <?= in_array((int)$interest['interest_id'], $form['interests'], true) ? 'checked' : '' ?>>
                  <?= h($interest['interest_name']) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="step-actions">
            <button type="button" class="btn-secondary org-btn-pad" onclick="prevStep(2)"><i data-lucide="arrow-left" style="width: 16px; margin-right: 0.4rem;"></i> Back</button>
            <button type="button" class="btn-primary org-btn-pad" onclick="nextStep(2)">Next Step <i data-lucide="arrow-right" style="width: 16px; margin-left: 0.4rem;"></i></button>
          </div>
        </div>

        <div class="form-step" id="step-3">
          <div class="form-card">
          <h2 class="org-form-section-title" >Registration</h2>

          <div class="form-row">
            <div class="form-group">
              <label for="registration_open">Registration Opens</label>
              <input type="date" id="registration_open" name="registration_open" class="form-input" required value="<?= field('registration_open') ?>">
            </div>
            <div class="form-group">
              <label for="registration_close">Registration Closes</label>
              <input type="date" id="registration_close" name="registration_close" class="form-input" required value="<?= field('registration_close') ?>">
            </div>
          </div>

          <div class="form-group full-width" >
            <label>Visibility</label>
            <div class="radio-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
              <label class="radio-label">
                <input type="radio" name="visibility" value="PUBLIC" <?= ($form['visibility'] ?? '') === 'PUBLIC' ? 'checked' : '' ?>>
                <div class="radio-content">
                  <strong>Public</strong>
                  <span>Anyone can find and register. Registrations are accepted automatically.</span>
                </div>
              </label>
              <label class="radio-label">
                <input type="radio" name="visibility" value="INVITE_ONLY" <?= ($form['visibility'] ?? '') === 'INVITE_ONLY' ? 'checked' : '' ?>>
                <div class="radio-content">
                  <strong>Invite Only</strong>
                  <span>Registrations need your approval.</span>
                </div>
              </label>
            </div>
          </div>
            </div>
          </div>

          <div class="step-actions">
            <button type="button" class="btn-secondary org-btn-pad" onclick="prevStep(3)"><i data-lucide="arrow-left" style="width: 16px; margin-right: 0.4rem;"></i> Back</button>
            <button type="button" class="btn-primary org-btn-pad" onclick="submitForm()"><i data-lucide="check-circle" style="width: 16px; margin-right: 0.4rem;"></i> <?= $isEdit ? 'Save Changes' : 'Create Event' ?></button>
          </div>
        </div>
      </form>

    </main>
  </div>

  <script>
    lucide.createIcons();

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

    // Filter interest tags by name
    document.getElementById('tagFilter').addEventListener('input', (e) => {
      const q = e.target.value.trim().toLowerCase();
      document.querySelectorAll('#tagList .tag-chip').forEach(chip => {
        chip.style.display = chip.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });

    // Keep related date/time pickers consistent (the server re-checks all of this)
    const eventDate = document.getElementById('event_date');
    const regOpen = document.getElementById('registration_open');
    const regClose = document.getElementById('registration_close');
    <?php if (!$isEdit): ?>
    eventDate.min = new Date().toISOString().slice(0, 10);
    <?php endif; ?>
    function syncDateLimits() {
      regClose.max = eventDate.value || '';
      regClose.min = regOpen.value || '';
      regOpen.max = regClose.value || eventDate.value || '';
    }
    [eventDate, regOpen, regClose].forEach(el => el.addEventListener('change', syncDateLimits));
    syncDateLimits();

    // Multi-step form logic
    function nextStep(currentStep) {
      // Validate current step
      const stepEl = document.getElementById(`step-${currentStep}`);
      const inputs = stepEl.querySelectorAll('input[required], select[required], textarea[required]');
      let isValid = true;
      for (const input of inputs) {
        if (!input.checkValidity()) {
          input.reportValidity();
          isValid = false;
          break;
        }
      }
      if (!isValid) return;

      // Update UI
      document.getElementById(`step-${currentStep}`).classList.remove('active');
      document.getElementById(`step-${currentStep + 1}`).classList.add('active');
      
      document.getElementById(`indicator-${currentStep}`).classList.add('completed');
      document.getElementById(`line-${currentStep}`).classList.add('completed');
      
      document.getElementById(`indicator-${currentStep + 1}`).classList.add('active');
      
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function prevStep(currentStep) {
      document.getElementById(`step-${currentStep}`).classList.remove('active');
      document.getElementById(`step-${currentStep - 1}`).classList.add('active');
      
      document.getElementById(`indicator-${currentStep}`).classList.remove('active');
      document.getElementById(`indicator-${currentStep - 1}`).classList.remove('completed');
      document.getElementById(`line-${currentStep - 1}`).classList.remove('completed');
      
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function submitForm() {
      // Validate step 3
      const stepEl = document.getElementById('step-3');
      const inputs = stepEl.querySelectorAll('input[required], select[required], textarea[required]');
      for (const input of inputs) {
        if (!input.checkValidity()) {
          input.reportValidity();
          return;
        }
      }
      
      const form = document.getElementById('eventForm');
      if (form.checkValidity()) {
        form.submit();
      } else {
        form.reportValidity();
      }
    }
  </script>
</body>
</html>
