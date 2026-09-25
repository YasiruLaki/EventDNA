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
    $selectedGoals = $_POST['goals'] ?? [];
    
    if (!is_array($selectedGoals)) $selectedGoals = [$selectedGoals];

    $result = $controller->processStep3($_SESSION['user_id'], $selectedGoals);
    
    if ($result['success']) {
        header("Location: ../dashboard/index.php");
        exit;
    } else {
        $error = $result['message'];
    }
}

$allGoals = $controller->getNetworkingGoals();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Onboarding Step 3 — Networking Goals</title>
<link rel="stylesheet" href="./styles.css">
</head>
<body>
  <div class="auth-layout">
    <div class="auth-panel">
      <div class="register-card onboarding-card">
        <div class="progress-bar-container">
          <div class="progress-bar-segment active"></div>
          <div class="progress-bar-segment active"></div>
          <div class="progress-bar-segment active"></div>
        </div>
        <div class="step-indicator">
          3 of 3 &mdash; Networking Goals
        </div>

        <h2 class="onboarding-title">Networking Goals</h2>
        <p class="subtitle onboarding-subtitle">
          Select what you're looking for to help us match you with the right people.
        </p>

        <?php if ($error): ?>
            <p style="color: var(--error); margin-bottom: 1rem; font-size: 0.9rem;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="search-box" style="margin-bottom: 1rem;">
          <input type="text" id="searchInput" placeholder="Search Goals..." style="width: 100%; padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--surface-color); color: var(--text-primary); outline: none;">
        </div>

        <form action="" method="post" id="onboarding3Form">
        <div class="skills-interests-section">
            <div class="section-label">I'm looking for... (Select all that apply)</div>
            <div class="chip-list" id="goalsList">
              <?php foreach ($allGoals as $goal): ?>
                  <button type="button" class="chip" data-id="<?php echo htmlspecialchars($goal['goal_id']); ?>">
                    <?php echo htmlspecialchars($goal['goal_name']); ?>
                    <svg class="chip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  </button>
              <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn-primary onboarding-submit" style="width: 100%; border: none; cursor: pointer; font-size: 1rem; padding: 0.875rem;">
          Complete Setup &rarr;
        </button>
        </form>

        <div class="footer-links onboarding-footer-links">
          <a href="../dashboard/index.php" class="skip-link">I'll complete this later</a>
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
  if (searchInput) {
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
  }

  document.getElementById('onboarding3Form').addEventListener('submit', function(e) {
      document.querySelectorAll('.chip.selected').forEach(chip => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'goals[]';
          input.value = chip.dataset.id;
          this.appendChild(input);
      });
  });
</script>
</body>
</html>
