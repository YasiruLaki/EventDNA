<?php
require_once __DIR__ . "/../includes/guard.php";
require_once __DIR__ . "/../../../data/database.php";
require_once __DIR__ . "/../../../application/controllers/OnboardingController.php";
require_once __DIR__ . "/../../../application/controllers/AuthController.php";
require_once __DIR__ . "/../../../application/controllers/SettingsController.php";

$controller = new OnboardingController($conn);
$settingsController = new SettingsController($conn);
$tabs = ['account', 'profile', 'notifications', 'privacy', 'security'];

// Each form posts back here, then redirects (so refresh doesn't resubmit) with a flash message.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $tab = 'account';

    if (!csrf_valid()) {
        $result = ["success" => false, "message" => "Your session expired. Please try again."];
    } elseif ($action === 'account') {
        $fullName = trim($_POST['full_name'] ?? '');
        $result = $controller->updateAccount($attendeeId, $fullName);
        if ($result['success']) {
            $_SESSION['full_name'] = $fullName;
        }
    } elseif ($action === 'profile') {
        $tab = 'profile';
        $result = $controller->updateProfile($attendeeId, [
            'job_title' => trim($_POST['job_title'] ?? ''),
            'organization' => trim($_POST['organization'] ?? ''),
            'field' => trim($_POST['field'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'skills' => $_POST['skills'] ?? [],
            'interests' => $_POST['interests'] ?? [],
            'goals' => $_POST['goals'] ?? [],
        ], $_FILES['profile_photo'] ?? null);
    } elseif ($action === 'remove_photo') {
        $tab = 'profile';
        $result = $controller->removePhoto($attendeeId);
    } elseif ($action === 'password') {
        $tab = 'security';
        $result = (new AuthController($conn))->changePassword(
            $attendeeId,
            $_POST['current_password'] ?? '',
            $_POST['new_password'] ?? '',
            $_POST['confirm_password'] ?? ''
        );
    } elseif ($action === 'notifications') {
        $tab = 'notifications';
        $result = $settingsController->updateNotifications($attendeeId, $_POST['notify'] ?? []);
    } elseif ($action === 'privacy') {
        $tab = 'privacy';
        $result = $settingsController->updatePrivacy($attendeeId, $_POST['profile_visibility'] ?? '');
    } else {
        $result = ["success" => false, "message" => "Unknown action."];
    }

    $_SESSION['settings_flash'] = ['tab' => $tab, 'success' => $result['success'], 'message' => $result['message']];
    header("Location: index.php?tab=" . $tab);
    exit;
}

$flash = $_SESSION['settings_flash'] ?? null;
unset($_SESSION['settings_flash']);

$activeTab = in_array($_GET['tab'] ?? '', $tabs, true) ? $_GET['tab'] : 'account';

$profile = $controller->getProfile($attendeeId);
if (!$profile) {
    header("Location: ../../auth/logout/index.php");
    exit;
}

$completion = $controller->getProfileCompletion($profile);
$firstName = explode(' ', trim($profile['full_name']))[0];
$initial = strtoupper(substr($firstName, 0, 1));
$photoUrl = $profile['profile_photo'] ? '../../../' . $profile['profile_photo'] : null;

$settings = $settingsController->getSettings($attendeeId);

$allSkills = $controller->getSkills();
$allInterests = $controller->getInterests();
$allGoals = $controller->getNetworkingGoals();

function tab_class($tab, $activeTab) {
    return $tab === $activeTab ? ' active' : '';
}

function flash_message($tab, $flash) {
    if (!$flash || $flash['tab'] !== $tab) {
        return '';
    }
    $class = $flash['success'] ? 'settings-alert success' : 'settings-alert error';
    return '<div class="' . $class . '" role="status">' . h($flash['message']) . '</div>';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Settings - EventDNA</title>
  <link rel="stylesheet" href="../dashboard/styles.css" />
  <link rel="stylesheet" href="./styles.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="../dashboard/index.php" class="nav-logo">
          <img src="../../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="../../events/ExploreEvents/index.php" class="nav-link">Find Events</a>
          <a href="../../events/myEvents/index.php" class="nav-link">My Events</a>
          <a href="../community/community-hub/index.php" class="nav-link">Communities</a>
          <a href="../myConnections/index.php" class="nav-link">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <?php if ($photoUrl): ?>
            <img src="<?= h($photoUrl) ?>" alt="Profile" class="nav-avatar" />
            <?php else: ?>
            <span class="nav-avatar" style="display: inline-flex; align-items: center; justify-content: center; background: var(--primary); color: #fff; font-weight: 700;"><?= h($initial) ?></span>
            <?php endif; ?>
            <span class="nav-profile-name"><?= h($firstName) ?></span>
            <i data-lucide="chevron-down" style="width: 16px; height: 16px;"></i>
          </button>
          <div class="nav-dropdown">
            <a href="../settings/index.php" class="dropdown-item">Profile</a>
            <a href="../../auth/logout/index.php" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="settings-shell">
    <main class="settings-container">
      
      <div class="settings-header">
        <h1>Settings</h1>
        <p class="settings-subtitle">Manage your account, profile, privacy, and notification preferences.</p>
      </div>

      <div class="settings-layout">
        <!-- Sidebar Navigation -->
        <aside class="settings-sidebar">
          <div class="sidebar-widget profile-completion">
            <div class="widget-header">
              <span>Profile Completion</span>
              <strong><?= $completion ?>%</strong>
            </div>
            <div class="progress-track"><div class="progress-fill" style="width: <?= $completion ?>%"></div></div>
            <p style="margin-bottom: 1rem;">Complete your profile for better networking recommendations.</p>
            <a href="../onboarding/index.php" class="btn-primary w-full" style="text-align: center; font-size: 0.85rem; padding: 0.6rem; text-decoration: none; display: block;">Complete Profile &rarr;</a>
          </div>

          <nav class="settings-nav">
            <button class="nav-item<?= tab_class('account', $activeTab) ?>" data-target="account">
              <i data-lucide="user"></i> Account
            </button>
            <button class="nav-item<?= tab_class('profile', $activeTab) ?>" data-target="profile">
              <i data-lucide="layout-template"></i> Profile
            </button>
            <button class="nav-item<?= tab_class('notifications', $activeTab) ?>" data-target="notifications">
              <i data-lucide="bell"></i> Notifications
            </button>
            <button class="nav-item<?= tab_class('privacy', $activeTab) ?>" data-target="privacy">
              <i data-lucide="lock"></i> Privacy
            </button>
            <button class="nav-item<?= tab_class('security', $activeTab) ?>" data-target="security">
              <i data-lucide="shield"></i> Security
            </button>
            
            <div style="border-top: 1px solid rgba(226, 232, 240, 0.9); margin: 1rem 0;"></div>
            
            <a href="../../auth/logout/index.php" class="nav-item text-danger" style="text-decoration: none; display: flex; align-items: center; gap: 0.75rem;">
              <i data-lucide="log-out"></i> Log Out
            </a>
          </nav>
        </aside>

        <!-- Content Area -->
        <div class="settings-content">
          
          <!-- 1. Account Settings -->
          <section id="account" class="settings-tab<?= tab_class('account', $activeTab) ?>">
            <h2>Account</h2>
            <p class="tab-desc">Manage basic account information.</p>

            <form class="settings-card" method="post" action="index.php">
              <?= flash_message('account', $flash) ?>
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="account">
              <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="full_name" class="form-input" value="<?= h($profile['full_name']) ?>" maxlength="150" required>
              </div>
              <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" class="form-input" value="<?= h($profile['email']) ?>" readonly style="background: rgba(15,23,42,0.02); color: var(--text-secondary); cursor: not-allowed;">
              </div>
              
              <div class="info-row" style="margin-top: 1.5rem;">
                <span class="info-label">Account Type</span>
                <span class="info-value"><?= h(ucfirst(strtolower($profile['role_name'] ?? 'Attendee'))) ?></span>
              </div>

              <div class="card-actions" style="margin-top: 2rem;">
                <button type="submit" class="btn-primary" id="accountSaveBtn" style="display: none;">Save Changes</button>
              </div>
            </form>
          </section>

          <!-- 2. Profile Settings -->
          <section id="profile" class="settings-tab<?= tab_class('profile', $activeTab) ?>">
            <h2>Profile</h2>
            <p class="tab-desc">Manage the information used to build your EventDNA profile.</p>

            <!-- Separate form so "Remove" can sit inside the profile form without nesting forms -->
            <form id="removePhotoForm" method="post" action="index.php">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="remove_photo">
            </form>

            <form class="settings-card" id="profileForm" method="post" action="index.php" enctype="multipart/form-data">
              <?= flash_message('profile', $flash) ?>
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="profile">

              <div class="photo-section" style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="avatar-large" style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                  <img id="avatarPreview" src="<?= h($photoUrl ?? '') ?>" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover;" <?= $photoUrl ? '' : 'hidden' ?>>
                  <div id="avatarInitial" class="avatar-initial" <?= $photoUrl ? 'hidden' : '' ?>><?= h($initial) ?></div>
                </div>
                <div class="photo-actions" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                  <button type="button" class="btn-secondary" id="changePhotoBtn">Change Photo</button>
                  <?php if ($photoUrl): ?>
                  <button type="submit" form="removePhotoForm" class="btn-text text-danger" id="removePhotoBtn" style="background: none; border: none; cursor: pointer; color: var(--danger); font-weight: 600; font-size: 0.95rem;">Remove</button>
                  <?php endif; ?>
                  <span id="photoHint" class="photo-hint">JPG, PNG or WebP, up to 5 MB.</span>
                </div>
                <input type="file" id="profilePhoto" name="profile_photo" accept="image/jpeg,image/png,image/webp" hidden>
              </div>

              <div class="form-group">
                <label for="role">Role / Job title</label>
                <input type="text" id="role" name="job_title" class="form-input" value="<?= h($profile['job_title']) ?>" maxlength="150">
              </div>

              <div class="form-group">
                <label for="organization">Organization / University</label>
                <input type="text" id="organization" name="organization" class="form-input" value="<?= h($profile['organization']) ?>" maxlength="200">
              </div>

              <div class="form-group">
                <label for="field">Field / Industry</label>
                <select id="field" name="field" class="form-input">
                  <option value="" <?= empty($profile['field']) ? 'selected' : '' ?>>Select your field</option>
                  <?php foreach (OnboardingController::INDUSTRIES as $key => $label): ?>
                  <option value="<?= h($key) ?>" <?= $profile['field'] === $key ? 'selected' : '' ?>><?= h($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="bio">Professional Bio</label>
                <textarea id="bio" name="bio" class="form-textarea" rows="4" maxlength="300"><?= h($profile['bio']) ?></textarea>
              </div>

              <?php
              $chipGroups = [
                  ['label' => 'Skills', 'name' => 'skills', 'options' => $allSkills, 'id' => 'skill_id', 'text' => 'skill_name'],
                  ['label' => 'Interests', 'name' => 'interests', 'options' => $allInterests, 'id' => 'interest_id', 'text' => 'interest_name'],
                  ['label' => 'Networking Goals', 'name' => 'goals', 'options' => $allGoals, 'id' => 'goal_id', 'text' => 'goal_name'],
              ];
              foreach ($chipGroups as $group): ?>
              <div class="form-group">
                <label><?= h($group['label']) ?></label>
                <div class="chip-list">
                  <?php foreach ($group['options'] as $option): $optionId = (int)$option[$group['id']]; ?>
                  <label class="chip">
                    <input type="checkbox" name="<?= $group['name'] ?>[]" value="<?= $optionId ?>" <?= isset($profile[$group['name']][$optionId]) ? 'checked' : '' ?>>
                    <span><?= h($option[$group['text']]) ?></span>
                  </label>
                  <?php endforeach; ?>
                </div>
              </div>
              <?php endforeach; ?>

              <div class="card-actions" style="margin-top: 2rem;">
                <button type="submit" class="btn-primary" id="profileSaveBtn" style="display: none;">Save Changes</button>
              </div>
            </form>
          </section>

          <!-- 3. Notifications Settings -->
          <section id="notifications" class="settings-tab<?= tab_class('notifications', $activeTab) ?>">
            <h2>Notifications</h2>
            <p class="tab-desc">Notification Preferences</p>

            <?php
            // column => [title, description, row style]
            $notificationRows = [
                'notify_in_app' => ['In-app notifications', '', 'margin-bottom: 1.5rem;'],
                'notify_email' => ['Email notifications', '', 'margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(226, 232, 240, 0.9);'],
                'notify_event_updates' => ['Event Updates', "Receive updates about events you've registered for.", 'margin-bottom: 1rem;'],
                'notify_connection_requests' => ['Connection Requests', 'Receive notifications when someone sends you a connection request.', 'margin-bottom: 1rem;'],
                'notify_connection_updates' => ['Connection Updates', 'Receive notifications when a request is accepted.', 'margin-bottom: 1rem;'],
                'notify_community_activity' => ['Community Activity', 'Receive relevant updates from your groups.', ''],
            ];
            ?>
            <form class="settings-card" id="notificationsForm" method="post" action="index.php">
              <?= flash_message('notifications', $flash) ?>
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="notifications">

              <?php foreach ($notificationRows as $column => [$title, $desc, $style]): ?>
              <div class="toggle-row"<?= $style ? ' style="' . $style . '"' : '' ?>>
                <div class="toggle-info">
                  <strong><?= h($title) ?></strong>
                  <?php if ($desc): ?><span><?= h($desc) ?></span><?php endif; ?>
                </div>
                <label class="toggle-switch">
                  <input type="checkbox" name="notify[]" value="<?= $column ?>" aria-label="<?= h($title) ?>"<?= $settings[$column] ? ' checked' : '' ?>>
                  <span class="slider"></span>
                </label>
              </div>
              <?php endforeach; ?>

              <div class="card-actions" style="margin-top: 2rem;">
                <button type="submit" class="btn-primary" id="notificationsSaveBtn" style="display: none;">Save Changes</button>
              </div>
            </form>
          </section>

          <!-- 4. Privacy Settings -->
          <section id="privacy" class="settings-tab<?= tab_class('privacy', $activeTab) ?>">
            <h2>Privacy</h2>
            <p class="tab-desc">Control what others can see about you.</p>

            <form class="settings-card" id="privacyForm" method="post" action="index.php">
              <?= flash_message('privacy', $flash) ?>
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="privacy">

              <h3>Profile Visibility</h3>
              <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem;">Who can view your public profile information?</p>
              <div class="radio-group" style="margin-bottom: 2rem;">
                <label class="radio-label">
                  <input type="radio" name="profile_visibility" value="MEMBERS"<?= $settings['profile_visibility'] === 'MEMBERS' ? ' checked' : '' ?>>
                  <div class="radio-content">
                    <strong>EventDNA members</strong>
                  </div>
                </label>
                <label class="radio-label">
                  <input type="radio" name="profile_visibility" value="CONNECTIONS"<?= $settings['profile_visibility'] === 'CONNECTIONS' ? ' checked' : '' ?>>
                  <div class="radio-content">
                    <strong>People you've connected with</strong>
                  </div>
                </label>
              </div>

              <h3>Personal QR</h3>
              <p style="color: var(--text-secondary); font-size: 0.9rem;">Your Personal QR only shares your public profile information.</p>

              <div class="card-actions" style="margin-top: 2rem;">
                <button type="submit" class="btn-primary" id="privacySaveBtn" style="display: none;">Save Changes</button>
              </div>
            </form>
          </section>

          <!-- 5. Security Settings -->
          <section id="security" class="settings-tab<?= tab_class('security', $activeTab) ?>">
            <h2>Security</h2>
            <p class="tab-desc">Keep your account safe.</p>
            <form class="settings-card" method="post" action="index.php">
              <?= flash_message('security', $flash) ?>
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="password">
              <h3>Change Password</h3>
              <div class="form-group"><input type="password" name="current_password" class="form-input" placeholder="Current password" autocomplete="current-password" required></div>
              <div class="form-group"><input type="password" name="new_password" class="form-input" placeholder="New password (at least 8 characters)" autocomplete="new-password" minlength="8" required></div>
              <div class="form-group"><input type="password" name="confirm_password" class="form-input" placeholder="Confirm new password" autocomplete="new-password" minlength="8" required></div>
              <button type="submit" class="btn-primary" style="margin-top: 1rem;">Change Password</button>
            </form>
          </section>

        </div>
      </div>
    </main>
  </div>

  <script>
    lucide.createIcons();

    const navItems = document.querySelectorAll('.nav-item[data-target]');
    const tabs = document.querySelectorAll('.settings-tab');

    navItems.forEach(item => {
      item.addEventListener('click', () => {
        navItems.forEach(nav => nav.classList.remove('active'));
        tabs.forEach(tab => tab.classList.remove('active'));
        
        item.classList.add('active');
        const targetId = item.getAttribute('data-target');
        document.getElementById(targetId).classList.add('active');
        history.replaceState(null, '', '?tab=' + targetId);

        if(window.innerWidth < 1024) {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }
      });
    });

    // Show a form's Save button once something in it changes
    const showSaveOnChange = (formSelector, saveBtnId) => {
      const form = document.querySelector(formSelector);
      const saveBtn = document.getElementById(saveBtnId);
      if (!form || !saveBtn) return;

      const show = () => { saveBtn.style.display = 'inline-flex'; };
      form.addEventListener('input', show);
      form.addEventListener('change', show);
    };

    showSaveOnChange('#account form', 'accountSaveBtn');
    showSaveOnChange('#profileForm', 'profileSaveBtn');
    showSaveOnChange('#notificationsForm', 'notificationsSaveBtn');
    showSaveOnChange('#privacyForm', 'privacySaveBtn');

    // Profile photo: pick, preview, and saved with the profile form
    const photoInput = document.getElementById('profilePhoto');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarInitial = document.getElementById('avatarInitial');
    const photoHint = document.getElementById('photoHint');
    const maxPhotoBytes = 5 * 1024 * 1024;
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    document.getElementById('changePhotoBtn').addEventListener('click', () => photoInput.click());

    photoInput.addEventListener('change', () => {
      const file = photoInput.files[0];
      if (!file) return;

      if (!allowedTypes.includes(file.type) || file.size > maxPhotoBytes) {
        photoInput.value = '';
        photoHint.textContent = 'Please choose a JPG, PNG or WebP image of 5 MB or smaller.';
        photoHint.classList.add('error');
        return;
      }

      avatarPreview.src = URL.createObjectURL(file);
      avatarPreview.hidden = false;
      avatarInitial.hidden = true;
      photoHint.textContent = file.name + ' — click Save Changes to apply.';
      photoHint.classList.remove('error');
    });

    const removePhotoBtn = document.getElementById('removePhotoBtn');
    if (removePhotoBtn) {
      removePhotoBtn.addEventListener('click', (e) => {
        if (!confirm('Remove your profile photo?')) e.preventDefault();
      });
    }
  </script>
</body>
</html>
