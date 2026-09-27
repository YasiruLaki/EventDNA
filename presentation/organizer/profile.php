<?php
require_once __DIR__ . "/includes/guard.php";
require_once "../../application/controllers/OnboardingController.php";

$error = "";
$success = "";
$controller = new OnboardingController($conn);

$profile = $controller->getProfile($_SESSION['user_id']);

$defaultName = $profile['full_name'] ?? $_SESSION['full_name'] ?? '';
$defaultOrganization = $profile['organization'] ?? '';
$defaultRole = $profile['job_title'] ?? '';
$defaultIndustry = $profile['field'] ?? '';
$defaultBio = $profile['bio'] ?? '';
$defaultLinkedin = $profile['linkedin_url'] ?? '';
$defaultOtherSocial = $profile['other_social_url'] ?? '';
$defaultPhoto = $profile['profile_photo'] ?? '';

$userSkills = $profile && !empty($profile['skills']) ? array_keys($profile['skills']) : [];
$userInterests = $profile && !empty($profile['interests']) ? array_keys($profile['interests']) : [];
$userGoals = $profile && !empty($profile['goals']) ? array_keys($profile['goals']) : [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['fullName'] ?? $defaultName);
    $role = trim($_POST['role'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    $skills = $_POST['skills'] ?? [];
    $interests = $_POST['interests'] ?? [];
    $goals = $_POST['goals'] ?? [];
    
    if (!is_array($skills)) $skills = [$skills];
    if (!is_array($interests)) $interests = [$interests];
    if (!is_array($goals)) $goals = [$goals];
    
    $linkedinUrl = trim($_POST['linkedinUrl'] ?? '');
    $otherSocialUrl = trim($_POST['otherSocialUrl'] ?? '');

    $photoFile = $_FILES['profile_photo'] ?? null;

    $res1 = $controller->processStep1($_SESSION['user_id'], $fullName, $role, $organization, $industry, $bio, $linkedinUrl, $otherSocialUrl, $photoFile);
    
    if ($res1['success']) {
        $controller->processStep2($_SESSION['user_id'], $skills, $interests);
        $controller->processStep3($_SESSION['user_id'], $goals);
        $success = "Profile updated successfully!";
        // Refresh profile data
        $profile = $controller->getProfile($_SESSION['user_id']);
        $defaultName = $profile['full_name'] ?? '';
        $defaultOrganization = $profile['organization'] ?? '';
        $defaultRole = $profile['job_title'] ?? '';
        $defaultIndustry = $profile['field'] ?? '';
        $defaultBio = $profile['bio'] ?? '';
        $defaultLinkedin = $profile['linkedin_url'] ?? '';
        $defaultOtherSocial = $profile['other_social_url'] ?? '';
        $defaultPhoto = $profile['profile_photo'] ?? '';
        $userSkills = $profile && !empty($profile['skills']) ? array_keys($profile['skills']) : [];
        $userInterests = $profile && !empty($profile['interests']) ? array_keys($profile['interests']) : [];
        $userGoals = $profile && !empty($profile['goals']) ? array_keys($profile['goals']) : [];
    } else {
        $error = $res1['message'];
    }
}

$allSkills = $controller->getSkills();
$allInterests = $controller->getInterests();
$allGoals = $controller->getNetworkingGoals();

$activeNav = ''; // No active nav link for profile
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Edit Profile - EventDNA</title>
    <link rel="stylesheet" href="../../globals.css" />
    <link rel="stylesheet" href="../attendee/dashboard/styles.css" />
    <link rel="stylesheet" href="organizer.css" />
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
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--secondary);
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
      .avatar-preview-box {
        width: 100px;
        height: 100px;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        margin-bottom: 1rem;
      }
      .step-actions {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f1f5f9;
      }
      @media (max-width: 640px) {
        .form-card { grid-template-columns: 1fr; }
      }
    </style>
</head>
<body class="org-page-wrapper">
    <?php include 'includes/nav.php'; ?>

    <div class="dashboard-shell">
        <main class="dashboard-content org-form-container">
            <section class="org-hero-header">
                <h1 class="page-title">Edit Profile</h1>
                <p class="supporting-copy">Update your organizer profile details and tags.</p>
            </section>

            <?php if ($success): ?>
                <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: var(--success); padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 600;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div style="background: rgba(220, 38, 38, 0.06); border: 1px solid rgba(220, 38, 38, 0.25); color: var(--danger); padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 600;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-card">
                    <h2 class="org-form-section-title">Basic Information</h2>

                    <div class="form-group full-width">
                        <label for="fullName">Full Name <span style="color:var(--error)">*</span></label>
                        <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($defaultName); ?>" class="form-input" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="profile_photo">Profile Photo / Logo</label>
                        <?php if ($defaultPhoto): ?>
                            <img src="<?php echo htmlspecialchars('/eventDNA/' . ltrim($defaultPhoto, '/')); ?>" class="avatar-preview-box" alt="Current Photo">
                        <?php endif; ?>
                        <input type="file" id="profile_photo" name="profile_photo" class="form-input" accept="image/jpeg,image/png,image/webp">
                        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.5rem;">JPG, PNG or WebP, up to 5 MB. Leave empty to keep the current photo.</p>
                    </div>

                    <div class="form-group">
                        <label for="organization">Organization Name <span style="color:var(--error)">*</span></label>
                        <input type="text" id="organization" name="organization" value="<?php echo htmlspecialchars($defaultOrganization); ?>" placeholder="e.g. AI Innovation Lanka" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="role">Job Title / Role <span style="color:var(--error)">*</span></label>
                        <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($defaultRole); ?>" placeholder="e.g. Event Manager" class="form-input" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="industry">Field / Industry <span style="color:var(--error)">*</span></label>
                        <select id="industry" name="industry" class="form-select" required>
                            <option value="" disabled <?php echo empty($defaultIndustry) ? 'selected' : ''; ?>>Select your field</option>
                            <option value="medicine" <?php echo $defaultIndustry === 'medicine' ? 'selected' : ''; ?>>Medicine & Healthcare</option>
                            <option value="technology" <?php echo $defaultIndustry === 'technology' ? 'selected' : ''; ?>>Technology</option>
                            <option value="business" <?php echo $defaultIndustry === 'business' ? 'selected' : ''; ?>>Business</option>
                            <option value="engineering" <?php echo $defaultIndustry === 'engineering' ? 'selected' : ''; ?>>Engineering</option>
                            <option value="education" <?php echo $defaultIndustry === 'education' ? 'selected' : ''; ?>>Education</option>
                            <option value="research" <?php echo $defaultIndustry === 'research' ? 'selected' : ''; ?>>Research</option>
                            <option value="design" <?php echo $defaultIndustry === 'design' ? 'selected' : ''; ?>>Design</option>
                            <option value="finance" <?php echo $defaultIndustry === 'finance' ? 'selected' : ''; ?>>Finance</option>
                            <option value="other" <?php echo $defaultIndustry === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="linkedinUrl">LinkedIn URL (Optional)</label>
                        <input type="url" id="linkedinUrl" name="linkedinUrl" value="<?php echo htmlspecialchars($defaultLinkedin); ?>" placeholder="https://linkedin.com/in/username" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="otherSocialUrl">Other Social Link (Optional)</label>
                        <input type="url" id="otherSocialUrl" name="otherSocialUrl" value="<?php echo htmlspecialchars($defaultOtherSocial); ?>" placeholder="e.g. GitHub, Website" class="form-input">
                    </div>

                    <div class="form-group full-width">
                        <label for="bio">Bio / About <span style="color:var(--error)">*</span></label>
                        <textarea id="bio" name="bio" class="form-textarea" rows="4" maxlength="300" placeholder="Short organizer description..." required><?php echo htmlspecialchars($defaultBio); ?></textarea>
                    </div>
                </div>

                <div class="form-card">
                    <h2 class="org-form-section-title">Tags & Interests</h2>
                    
                    <div class="form-group full-width">
                        <label>Skills <span style="color:var(--error)">*</span></label>
                        <input type="text" id="skillsSearch" class="form-input" placeholder="Search Skills..." style="margin-bottom: 0.75rem; padding: 0.6rem 1rem;">
                        <div class="tag-list" id="skillsList">
                            <?php foreach ($allSkills as $skill): ?>
                                <label class="tag-chip">
                                    <input type="checkbox" name="skills[]" value="<?php echo (int)$skill['skill_id']; ?>" <?php echo in_array($skill['skill_id'], $userSkills) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($skill['skill_name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group full-width" style="margin-top: 1rem;">
                        <label>Interests <span style="color:var(--error)">*</span></label>
                        <input type="text" id="interestsSearch" class="form-input" placeholder="Search Interests..." style="margin-bottom: 0.75rem; padding: 0.6rem 1rem;">
                        <div class="tag-list" id="interestsList">
                            <?php foreach ($allInterests as $interest): ?>
                                <label class="tag-chip">
                                    <input type="checkbox" name="interests[]" value="<?php echo (int)$interest['interest_id']; ?>" <?php echo in_array($interest['interest_id'], $userInterests) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($interest['interest_name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-group full-width" style="margin-top: 1rem;">
                        <label>Networking Goals <span style="color:var(--error)">*</span></label>
                        <input type="text" id="goalsSearch" class="form-input" placeholder="Search Goals..." style="margin-bottom: 0.75rem; padding: 0.6rem 1rem;">
                        <div class="tag-list" id="goalsList">
                            <?php foreach ($allGoals as $goal): ?>
                                <label class="tag-chip">
                                    <input type="checkbox" name="goals[]" value="<?php echo (int)$goal['goal_id']; ?>" <?php echo in_array($goal['goal_id'], $userGoals) ? 'checked' : ''; ?>>
                                    <?php echo htmlspecialchars($goal['goal_name']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="step-actions">
                        <button type="submit" class="btn-primary" style="padding: 0.85rem 2rem; font-size: 1rem;">
                            Save Profile Changes
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>
    
    <script>
      lucide.createIcons();
      
      function setupTagSearch(inputId, listId) {
          const input = document.getElementById(inputId);
          const list = document.getElementById(listId);
          if (input && list) {
              input.addEventListener('input', (e) => {
                  const term = e.target.value.toLowerCase().trim();
                  list.querySelectorAll('.tag-chip').forEach(chip => {
                      const text = chip.textContent.toLowerCase();
                      chip.style.display = (term === '' || text.includes(term)) ? '' : 'none';
                  });
              });
          }
      }

      setupTagSearch('skillsSearch', 'skillsList');
      setupTagSearch('interestsSearch', 'interestsList');
      setupTagSearch('goalsSearch', 'goalsList');
    </script>
</body>
</html>
