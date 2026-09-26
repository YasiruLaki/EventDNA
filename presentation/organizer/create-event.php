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

// Values to show in the form: submitted values after an error, otherwise the saved event (edit) or defaults
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
    .form-card {
      background: rgba(255, 255, 255, 0.95);
      border: 1px solid rgba(226, 232, 240, 0.9);
      border-radius: 12px;
      padding: 2.5rem;
      margin-bottom: 2rem;
    }
    .form-group {
      margin-bottom: 1.5rem;
    }
    .form-group label {
      display: block;
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--secondary);
    }
    .form-input, .form-textarea, .form-select {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid var(--border-color);
      border-radius: 8px;
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
      box-shadow: 0 0 0 3px rgba(79, 16, 255, 0.1);
    }
    .form-row {
      display: flex;
      gap: 1.5rem;
    }
    .form-row > * {
      flex: 1;
    }
    .radio-group {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }
    .radio-label {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1rem;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      cursor: pointer;
      background: #f8fafc;
      transition: all 0.2s;
    }
    .radio-label:has(input:checked) {
      border-color: var(--primary);
      background: var(--primary-tint);
    }
    .radio-label input {
      accent-color: var(--primary);
      width: 18px;
      height: 18px;
    }
    .radio-content strong {
      display: block;
      font-size: 0.95rem;
      color: var(--secondary);
    }
    .radio-content span {
      font-size: 0.85rem;
      color: var(--text-secondary);
    }
    .hint {
      font-size: 0.8rem;
      color: var(--text-secondary);
      margin-top: 0.4rem;
    }
    .tag-list {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      max-height: 220px;
      overflow-y: auto;
      padding: 0.25rem;
    }
    .tag-chip {
      display: inline-flex;
      align-items: center;
      padding: 0.4rem 0.8rem;
      border: 1px solid var(--border-color);
      border-radius: 999px;
      font-size: 0.85rem;
      cursor: pointer;
      background: #f8fafc;
      color: var(--secondary);
      user-select: none;
    }
    .tag-chip input {
      display: none;
    }
    .tag-chip:has(input:checked) {
      border-color: var(--primary);
      background: var(--primary-tint);
      color: var(--primary);
      font-weight: 600;
    }
    .error-box {
      background: rgba(220, 38, 38, 0.06);
      border: 1px solid rgba(220, 38, 38, 0.25);
      color: var(--danger);
      border-radius: 8px;
      padding: 1rem 1.25rem;
      margin-bottom: 2rem;
      font-size: 0.9rem;
    }
    .error-box ul {
      margin: 0.5rem 0 0 1.25rem;
      padding: 0;
    }
    .cover-preview {
      width: 100%;
      max-height: 200px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 0.75rem;
    }
    @media (max-width: 640px) {
      .form-row { flex-direction: column; gap: 0; }
      .form-card { padding: 1.5rem; }
    }
  </style>
</head>
<body style="background-color: #f9f9f9; min-height: 100vh;">
<?php include __DIR__ . "/includes/nav.php"; ?>

  <div class="dashboard-shell">
    <main class="dashboard-content" style="max-width: 800px; margin: 0 auto; padding-bottom: 4rem;">

      <div style="margin-bottom: 2rem;">
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

      <form method="post" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>

        <div class="form-card">
          <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--secondary); margin: 0 0 1.5rem 0; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">Basic Information</h2>

          <div class="form-group">
            <label for="name">Event Name</label>
            <input type="text" id="name" name="name" class="form-input" maxlength="200" required
              placeholder="e.g. AI Innovation Summit 2026" value="<?= field('name') ?>">
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-textarea" rows="4"
              placeholder="Briefly describe what this event is about..."><?= field('description') ?></textarea>
          </div>

          <div class="form-group">
            <label for="cover_photo">Cover Photo</label>
            <?php if ($isEdit && !empty($event['cover_photo'])): ?>
              <img src="../../<?= h($event['cover_photo']) ?>" alt="Current cover photo" class="cover-preview">
            <?php endif; ?>
            <input type="file" id="cover_photo" name="cover_photo" class="form-input" accept="image/jpeg,image/png,image/webp">
            <p class="hint">JPG, PNG or WebP, up to 5 MB.<?= $isEdit ? ' Leave empty to keep the current photo.' : '' ?></p>
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
        </div>

        <div class="form-card">
          <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--secondary); margin: 0 0 1.5rem 0; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">Location & Details</h2>

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

          <div class="form-group" style="margin-bottom: 0;">
            <label>Interest Tags <span style="font-weight: 400; color: var(--text-secondary);">(up to <?= EventController::MAX_INTERESTS ?>, used for matching)</span></label>
            <input type="text" id="tagFilter" class="form-input" placeholder="Filter tags..." style="margin-bottom: 0.75rem;">
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
        </div>

        <div class="form-card">
          <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--secondary); margin: 0 0 1.5rem 0; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">Registration</h2>

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

          <div class="form-group" style="margin-bottom: 0;">
            <label>Visibility</label>
            <div class="radio-group">
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

        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
          <a href="<?= $isEdit ? 'event-details.php?id=' . $eventId : 'dashboard.php' ?>" class="btn-secondary" style="padding: 0.75rem 1.5rem; text-decoration: none;">Cancel</a>
          <button type="submit" class="btn-primary" style="padding: 0.75rem 1.5rem;"><?= $isEdit ? 'Save Changes' : 'Create Event' ?></button>
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
  </script>
</body>
</html>
