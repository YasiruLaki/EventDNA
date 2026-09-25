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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selectedSkills = $_POST['skills'] ?? [];
    $selectedInterests = $_POST['interests'] ?? [];
    
    if (!is_array($selectedSkills)) $selectedSkills = [$selectedSkills];
    if (!is_array($selectedInterests)) $selectedInterests = [$selectedInterests];

    $result = $controller->processStep2($_SESSION['user_id'], $selectedSkills, $selectedInterests);
    
    if ($result['success']) {
        header("Location: ../onboarding3/index.php");
        exit;
    } else {
        $error = $result['message'];
    }
}

$allSkills = $controller->getSkills();
$allInterests = $controller->getInterests();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Onboarding Step 2 — Skills & Interests</title>
<link rel="stylesheet" href="./styles.css">
</head>
<body>
  <div class="auth-layout">
    <div class="auth-panel">
      <div class="register-card onboarding-card">
        <div class="progress-bar-container">
          <div class="progress-bar-segment active"></div>
          <div class="progress-bar-segment active"></div>
          <div class="progress-bar-segment"></div>
        </div>
        <div class="step-indicator">
          2 of 3 &mdash; Skills &amp; Interests
        </div>

        <h2 class="onboarding-title">Skills &amp; Interests</h2>
        <p class="subtitle onboarding-subtitle">
          These help us recommend relevant events and meaningful connections.
        </p>

        <?php if ($error): ?>
            <p style="color: var(--error); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="search-box">
          <input type="text" id="searchInput" placeholder="Search Skills and Interests...">
        </div>

        <form action="" method="post" id="onboarding2Form">
        <div class="skills-interests-section">
            <div class="section-label">Skills</div>
            <div class="chip-list" id="skillsList">
              <?php foreach ($allSkills as $skill): ?>
                  <button type="button" class="chip" data-id="<?php echo htmlspecialchars($skill['skill_id']); ?>" data-type="skill">
                      <?php echo htmlspecialchars($skill['skill_name']); ?>
                  </button>
              <?php endforeach; ?>
            </div>
        </div>

        <div class="skills-interests-section">
            <div class="section-label">Interests</div>
            <div class="chip-list" id="interestsList">
              <?php foreach ($allInterests as $interest): ?>
                  <button type="button" class="chip" data-id="<?php echo htmlspecialchars($interest['interest_id']); ?>" data-type="interest">
                      <?php echo htmlspecialchars($interest['interest_name']); ?>
                  </button>
              <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn-primary onboarding-submit" style="width: 100%; border: none; cursor: pointer; font-size: 1rem; padding: 0.875rem;">
          Continue &rarr;
        </button>
        </form>

        <div class="footer-links onboarding-footer-links">
          <a href="../onboarding3/index.php" class="skip-link">I'll complete this later</a>
        </div>
      </div>
    </div>
  </div>

<script>
  document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      chip.classList.toggle('selected');
    });
  });

  const searchInput = document.getElementById('searchInput');
  searchInput.addEventListener('input', (e) => {
      const term = e.target.value.toLowerCase();
      document.querySelectorAll('.chip').forEach(chip => {
          const text = chip.textContent.toLowerCase();
          if (text.includes(term)) {
              chip.style.display = '';
          } else {
              chip.style.display = 'none';
          }
      });
  });

  document.getElementById('onboarding2Form').addEventListener('submit', function(e) {
      document.querySelectorAll('.chip.selected').forEach(chip => {
          const input = document.createElement('input');
          input.type = 'hidden';
          if (chip.dataset.type === 'skill') {
              input.name = 'skills[]';
          } else {
              input.name = 'interests[]';
          }
          input.value = chip.dataset.id;
          this.appendChild(input);
      });
  });
</script>
</body>
</html>