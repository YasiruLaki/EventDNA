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

$defaultName = $_SESSION['full_name'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['fullName'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $organization = trim($_POST['organization'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    $result = $controller->processStep1($_SESSION['user_id'], $fullName, $role, $organization, $industry, $bio);
    
    if ($result['success']) {
        header("Location: ../onboarding2/index.php");
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Onboarding - EventDNA</title>
	<link rel="stylesheet" href="./styles.css" />
</head>
	<body>
		<div class="auth-layout">
			<div class="auth-panel">
				<div class="register-card onboarding-card">
					<div class="progress-bar-container">
						<div class="progress-bar-segment active"></div>
						<div class="progress-bar-segment"></div>
						<div class="progress-bar-segment"></div>
					</div>
					<div class="step-indicator">
						1 of 3 &mdash; About You
					</div>

					<h2 class="onboarding-title">Tell us about yourself</h2>
					<p class="subtitle onboarding-subtitle">
						Build your professional EventDNA profile so people can understand who you are and what you're looking to connect around.
					</p>

                    <div class="photo-uploader" aria-label="Profile photo upload">
                        <div class="avatar-ring">
                            <div class="avatar-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>
                        <div class="photo-desc-container">
                            <button type="button" class="upload-link">Upload photo</button>
                            <p class="photo-desc">
                                A clear professional photo helps others recognize you at events.
                            </p>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <p style="color: var(--error); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>

                    <form action="" method="post">
                        <div class="form-grid">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($defaultName); ?>" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label for="role">Role / Job Title</label>
                            <input type="text" id="role" name="role" placeholder="e.g. Medical Student, Software Engineer" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="organization">Organization / University</label>
                            <input type="text" id="organization" name="organization" placeholder="e.g. University of Colombo" class="form-input">
                        </div>

                        <div class="form-group">
                            <label for="industry">Field / Industry</label>
                            <select id="industry" name="industry" class="form-select">
                                <option value="" disabled selected>Select your field</option>
                                <option value="medicine">Medicine & Healthcare</option>
                                <option value="technology">Technology</option>
                                <option value="business">Business</option>
                                <option value="engineering">Engineering</option>
                                <option value="education">Education</option>
                                <option value="research">Research</option>
                                <option value="design">Design</option>
                                <option value="finance">Finance</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group bio-group">
                        <label for="bio">Professional Bio</label>
                        <div class="textarea-wrapper">
                            <textarea id="bio" name="bio" rows="5" maxlength="300" placeholder="Tell people about your experience, interests, and what you're looking to connect around."></textarea>
                            <span class="counter">0 / 300</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary onboarding-submit" style="width: 100%; border: none; cursor: pointer; font-size: 1rem; padding: 0.875rem;">
                        Continue &rarr;
                    </button>
                    </form>

					<div class="footer-links onboarding-footer-links">
						<a href="../onboarding2/index.php" class="skip-link">I'll complete this later</a>
					</div>
				</div>
			</div>
		</div>

	<script>
		const bio = document.getElementById('bio');
		const counter = document.querySelector('.counter');

		if (bio && counter) {
			const updateCounter = () => {
				counter.textContent = `${bio.value.length} / 300`;
			};

			bio.addEventListener('input', updateCounter);
			updateCounter();
		}
	</script>
</body>
</html>
