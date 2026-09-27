<?php
session_start();
require_once "../../../data/database.php";
require_once "../../../application/controllers/OnboardingController.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}

$error = "";
$controller = new OnboardingController($conn);

$profile = $controller->getProfile($_SESSION['user_id']);

$defaultName = $profile['full_name'] ?? $_SESSION['full_name'] ?? '';
$defaultOrganization = $profile['organization'] ?? '';
$defaultRole = $profile['job_title'] ?? '';
$defaultIndustry = $profile['field'] ?? '';
$defaultBio = $profile['bio'] ?? '';
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
    
    $photoFile = $_FILES['profile_photo'] ?? null;

    $res1 = $controller->processStep1($_SESSION['user_id'], $fullName, $role, $organization, $industry, $bio, $photoFile);
    
    if ($res1['success']) {
        $controller->processStep2($_SESSION['user_id'], $skills, $interests);
        $controller->processStep3($_SESSION['user_id'], $goals);
        header("Location: ../dashboard.php");
        exit;
    } else {
        $error = $res1['message'];
    }
}

$allSkills = $controller->getSkills();
$allInterests = $controller->getInterests();
$allGoals = $controller->getNetworkingGoals();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Complete Organizer Profile - EventDNA</title>
    <link rel="stylesheet" href="../../../globals.css" />
	<link rel="stylesheet" href="../../attendee/onboarding/styles.css" />
	<link rel="stylesheet" href="../../attendee/onboarding2/styles.css" />
    <style>
        .chip-list {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            padding: 0.75rem;
            border-radius: 8px;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <div class="auth-layout" style="padding: 2rem 1rem;">
        <div class="auth-panel">
            <div class="register-card onboarding-card" style="max-width: 700px; padding: 2.5rem; margin: 0 auto; width: 100%;">
                <h2 class="onboarding-title">Complete Organizer Profile</h2>
                <p class="subtitle onboarding-subtitle" style="margin-bottom: 2rem;">
                    Set up your organizer profile to build trust with attendees and better manage your events.
                </p>

                <?php if ($error): ?>
                    <p style="color: var(--error); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form action="" method="post" enctype="multipart/form-data" id="onboardingForm">
                    <input type="hidden" name="fullName" value="<?php echo htmlspecialchars($defaultName); ?>">
                    
                    <div class="photo-uploader" aria-label="Profile photo upload" style="margin-bottom: 2rem;">
                        <button type="button" class="avatar-ring" id="avatarRing" aria-label="Choose profile photo">
                            <div class="avatar-placeholder" id="avatarPlaceholder" <?php echo $defaultPhoto ? 'hidden' : ''; ?>>
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <img class="avatar-preview" id="avatarPreview" src="<?php echo $defaultPhoto ? htmlspecialchars('/eventDNA/' . $defaultPhoto) : ''; ?>" alt="Selected profile photo" <?php echo $defaultPhoto ? '' : 'hidden'; ?>>
                        </button>
                        <div class="photo-desc-container">
                            <button type="button" class="upload-link" id="uploadLink" style="background:none; border:none; color:var(--primary); font-weight:600; cursor:pointer;">Upload photo / logo</button>
                            <p class="photo-desc" id="photoDesc" style="color:var(--text-secondary); font-size:0.85rem; margin-top:0.25rem;">
                                A logo or photo helps attendees recognize your events. (Optional)
                            </p>
                        </div>
                        <input type="file" id="profilePhoto" name="profile_photo" accept="image/jpeg,image/png,image/webp" hidden>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="organization">Organization Name <span style="color:var(--error)">*</span></label>
                            <input type="text" id="organization" name="organization" value="<?php echo htmlspecialchars($defaultOrganization); ?>" placeholder="e.g. AI Innovation Lanka" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label for="role">Job Title / Role <span style="color:var(--error)">*</span></label>
                            <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($defaultRole); ?>" placeholder="e.g. Event Manager" class="form-input" required>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label for="industry">Field / Industry <span style="color:var(--error)">*</span></label>
                            <select id="industry" name="industry" class="form-select" required style="width:100%; padding:0.6rem 1rem; border:1px solid var(--border-color); border-radius:8px; font-family:inherit;">
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
                    </div>

                    <div class="form-group bio-group" style="margin-top: 1rem;">
                        <label for="bio">Bio / About <span style="color:var(--error)">*</span></label>
                        <div class="textarea-wrapper" style="position:relative;">
                            <textarea id="bio" name="bio" rows="4" maxlength="300" placeholder="Short organizer description..." required style="width:100%; padding:1rem; border:1px solid var(--border-color); border-radius:8px; font-family:inherit; resize:vertical;"><?php echo htmlspecialchars($defaultBio); ?></textarea>
                            <span class="counter" style="position:absolute; bottom:0.75rem; right:1rem; font-size:0.75rem; color:var(--text-tertiary);">0 / 300</span>
                        </div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--divider-color); margin: 2.5rem 0 1.5rem;" />

                    <h3 style="font-size: 1.1rem; margin-bottom: 0.25rem; color: var(--text-primary);">Skills, Interests & Goals</h3>
                    <p class="subtitle onboarding-subtitle" style="margin-bottom: 1.5rem; font-size: 0.85rem;">Select relevant tags to help us categorize your profile.</p>

                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search Skills, Interests & Goals...">
                    </div>

                    <div class="skills-interests-section">
                        <div class="section-label">Skills <span style="color:var(--error)">*</span></div>
                        <div class="chip-list" id="skillsList">
                        <?php foreach ($allSkills as $skill): ?>
                            <button type="button" class="chip <?php echo in_array($skill['skill_id'], $userSkills) ? 'selected' : ''; ?>" data-id="<?php echo htmlspecialchars($skill['skill_id']); ?>" data-type="skill">
                                <?php echo htmlspecialchars($skill['skill_name']); ?>
                            </button>
                        <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="skills-interests-section">
                        <div class="section-label">Interests <span style="color:var(--error)">*</span></div>
                        <div class="chip-list" id="interestsList">
                        <?php foreach ($allInterests as $interest): ?>
                            <button type="button" class="chip <?php echo in_array($interest['interest_id'], $userInterests) ? 'selected' : ''; ?>" data-id="<?php echo htmlspecialchars($interest['interest_id']); ?>" data-type="interest">
                                <?php echo htmlspecialchars($interest['interest_name']); ?>
                            </button>
                        <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="skills-interests-section">
                        <div class="section-label">Networking Goals <span style="color:var(--error)">*</span></div>
                        <div class="chip-list" id="goalsList">
                        <?php foreach ($allGoals as $goal): ?>
                            <button type="button" class="chip <?php echo in_array($goal['goal_id'], $userGoals) ? 'selected' : ''; ?>" data-id="<?php echo htmlspecialchars($goal['goal_id']); ?>" data-type="goal">
                                <?php echo htmlspecialchars($goal['goal_name']); ?>
                            </button>
                        <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary onboarding-submit" style="width: 100%; border: none; cursor: pointer; font-size: 1rem; padding: 0.875rem; border-radius:8px; margin-top:2rem;">
                        Complete Organizer Profile &rarr;
                    </button>
                </form>

            </div>
        </div>
    </div>

    <script>
        // Bio counter
        const bio = document.getElementById('bio');
        const counter = document.querySelector('.counter');
        if (bio && counter) {
            const updateCounter = () => { counter.textContent = `${bio.value.length} / 300`; };
            bio.addEventListener('input', updateCounter);
            updateCounter();
        }

        // Photo upload
        const photoInput = document.getElementById('profilePhoto');
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarPlaceholder = document.getElementById('avatarPlaceholder');
        const uploadLink = document.getElementById('uploadLink');
        const photoDesc = document.getElementById('photoDesc');
        const maxPhotoBytes = 5 * 1024 * 1024;
        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        const openPicker = () => photoInput.click();
        document.getElementById('avatarRing').addEventListener('click', openPicker);
        uploadLink.addEventListener('click', openPicker);

        photoInput.addEventListener('change', () => {
            const file = photoInput.files[0];
            if (!file) return;

            if (!allowedTypes.includes(file.type) || file.size > maxPhotoBytes) {
                photoInput.value = '';
                avatarPreview.hidden = true;
                avatarPlaceholder.hidden = false;
                uploadLink.textContent = 'Upload logo or photo';
                photoDesc.textContent = 'Please choose a JPG, PNG or WebP image of 5 MB or smaller.';
                photoDesc.style.color = 'var(--error)';
                return;
            }

            if (avatarPreview.src) URL.revokeObjectURL(avatarPreview.src);
            avatarPreview.src = URL.createObjectURL(file);
            avatarPreview.hidden = false;
            avatarPlaceholder.hidden = true;
            uploadLink.textContent = 'Change logo/photo';
            photoDesc.textContent = file.name;
            photoDesc.style.color = '';
        });

        // Chips search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const term = e.target.value.toLowerCase().trim();
                
                document.querySelectorAll('.chip').forEach(chip => {
                    const text = chip.textContent.toLowerCase();
                    
                    if (term === '') {
                        chip.style.display = '';
                    } else {
                        if (text.includes(term)) {
                            chip.style.display = '';
                        } else {
                            chip.style.display = 'none';
                        }
                    }
                });
            });
        }

        // Chips selection
        document.querySelectorAll('.chip').forEach(chip => {
            chip.addEventListener('click', () => {
                chip.classList.toggle('selected');
            });
        });

        // Form submission
        document.getElementById('onboardingForm').addEventListener('submit', function(e) {
            let hasSkills = false;
            let hasInterests = false;
            let hasGoals = false;

            // Remove existing hidden inputs to avoid duplication on re-submission
            this.querySelectorAll('input[name="skills[]"], input[name="interests[]"], input[name="goals[]"]').forEach(el => el.remove());

            document.querySelectorAll('.chip.selected').forEach(chip => {
                const input = document.createElement('input');
                input.type = 'hidden';
                if (chip.dataset.type === 'skill') {
                    input.name = 'skills[]';
                    hasSkills = true;
                } else if (chip.dataset.type === 'interest') {
                    input.name = 'interests[]';
                    hasInterests = true;
                } else if (chip.dataset.type === 'goal') {
                    input.name = 'goals[]';
                    hasGoals = true;
                }
                input.value = chip.dataset.id;
                this.appendChild(input);
            });

            if (!hasSkills || !hasInterests || !hasGoals) {
                e.preventDefault();
                alert('Please select at least one skill, one interest, and one goal.');
                return false;
            }
        });
    </script>
</body>
</html>
