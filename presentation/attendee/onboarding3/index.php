<?php
require_once "../../../data/database.php";
require_once "../../../application/controllers/OnboardingController.php";

require_once __DIR__ . '/../includes/guard.php';

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



        <form action="" method="post" id="onboarding3Form">
        <div class="skills-interests-section">
            <div class="section-label">I'm looking for... (Select all that apply)</div>
            
            <div class="dropdown-container" id="goalsDropdownContainer">
              <div class="dropdown-header" id="goalsDropdownHeader">
                <span class="dropdown-header-placeholder">Select networking goals...</span>
              </div>
              <div class="dropdown-list" id="goalsDropdownList">
                <?php foreach ($allGoals as $goal): ?>
                    <label class="dropdown-item">
                        <input type="checkbox" name="goals[]" value="<?php echo htmlspecialchars($goal['goal_id']); ?>" data-name="<?php echo htmlspecialchars($goal['goal_name']); ?>">
                        <span><?php echo htmlspecialchars($goal['goal_name']); ?></span>
                    </label>
                <?php endforeach; ?>
              </div>
            </div>
            
            <div class="selected-tags" id="selectedGoalsTags"></div>
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
  document.addEventListener('DOMContentLoaded', () => {
      const header = document.getElementById('goalsDropdownHeader');
      const list = document.getElementById('goalsDropdownList');
      const checkboxes = list.querySelectorAll('input[type="checkbox"]');
      const tagsContainer = document.getElementById('selectedGoalsTags');
      
      // Toggle dropdown
      header.addEventListener('click', (e) => {
          if (e.target.closest('button')) return; // Ignore clicks on remove buttons
          list.classList.toggle('open');
      });
      
      // Close on outside click
      document.addEventListener('click', (e) => {
          if (!header.contains(e.target) && !list.contains(e.target)) {
              list.classList.remove('open');
          }
      });
      
      // Handle checkbox change
      checkboxes.forEach(cb => {
          cb.addEventListener('change', updateTags);
      });
      
      function updateTags() {
          tagsContainer.innerHTML = '';
          let count = 0;
          
          checkboxes.forEach(cb => {
              if (cb.checked) {
                  count++;
                  const name = cb.dataset.name;
                  const tag = document.createElement('div');
                  tag.className = 'tag-pill';
                  tag.innerHTML = `
                      ${name}
                      <button type="button" data-val="${cb.value}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                  `;
                  tagsContainer.appendChild(tag);
                  
                  tag.querySelector('button').addEventListener('click', (e) => {
                      e.stopPropagation();
                      cb.checked = false;
                      updateTags();
                  });
              }
          });
          
          const placeholder = header.querySelector('.dropdown-header-placeholder');
          if (placeholder) {
              placeholder.style.display = count > 0 ? 'none' : 'block';
          }
      }
  });
</script>
</body>
</html>
