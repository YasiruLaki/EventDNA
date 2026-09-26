<?php
require_once __DIR__ . "/includes/guard.php";
$activeNav = 'communities';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Group - EventDNA</title>
  <link rel="stylesheet" href="../attendee/dashboard/styles.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    /* Small additions just for this form — everything else reuses your existing styles.css variables */
    .form-card { max-width: 640px; }
    .form-group { margin-bottom: 1.5rem; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--secondary); }
    .form-group input[type="text"],
    .form-group textarea {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-family: inherit;
      font-size: 0.95rem;
      background: #f8fafc;
      color: var(--secondary);
      box-sizing: border-box;
    }
    .form-group textarea { min-height: 120px; resize: vertical; }
    .field-error { font-size: 0.85rem; color: var(--danger); margin-top: 0.35rem; display: none; }

    .tag-picker { display: flex; flex-wrap: wrap; gap: 0.6rem; }
    .tag-pill {
      padding: 0.45rem 1rem;
      border-radius: 999px;
      border: 1px solid var(--border-color);
      background: #f8fafc;
      color: var(--secondary);
      cursor: pointer;
      font-size: 0.85rem;
      font-weight: 500;
    }
    .tag-pill.selected { background: var(--primary); color: #fff; border-color: var(--primary); }

    .form-actions { display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem; }
    .submit-error { color: var(--danger); font-size: 0.9rem; display: none; }

    .success-card { max-width: 480px; margin: 3rem auto; text-align: center; display: none; }
  </style>
</head>

<body>
  <?php include 'includes/nav.php'; ?>

  <div class="dashboard-shell">
    <main class="dashboard-content">

      <!-- ===== FORM (visible by default) ===== -->
      <div id="createGroupView">
        <a href="dashboard.php#community" style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; color:var(--text-secondary); margin-bottom:1.5rem; font-size:0.95rem; font-weight:500;">
          <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
          Back to Dashboard
        </a>

        <h1 class="page-title" style="margin-bottom:0.25rem;">Create Group</h1>
        <p class="supporting-copy" style="margin-bottom:2rem;">Start a community around your event's audience.</p>

        <article class="stat-card form-card" style="padding: 2rem;">
          <form id="createGroupForm">

            <div class="form-group">
              <label for="groupName">Group Name</label>
              <input type="text" id="groupName" name="name" maxlength="200" placeholder="e.g. AI & Robotics Community" required>
              <div class="field-error" id="nameError">Group name is required.</div>
            </div>

            <div class="form-group">
              <label for="groupDescription">Description</label>
              <textarea id="groupDescription" name="description" maxlength="2000" placeholder="What is this group about?"></textarea>
            </div>

            <div class="form-group">
              <label>Interest Tags</label>
              <div class="tag-picker" id="tagPicker">
                <span style="color:var(--text-secondary); font-size:0.85rem;">Loading interests…</span>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary" id="submitBtn">Create Group</button>
              <span class="submit-error" id="submitError"></span>
            </div>

          </form>
        </article>
      </div>

      <!-- ===== SUCCESS STATE (hidden until creation succeeds) ===== -->
      <article id="successView" class="stat-card success-card" style="padding: 2.5rem;">
        <h2 style="color: var(--secondary);">Group Created Successfully</h2>
        <p style="color: var(--text-secondary);">You are now the owner of this group.</p>
        <a href="manage-groups.html" id="viewGroupBtn" class="btn-primary" style="display:inline-block; margin-top:1rem;">Manage Groups</a>
      </article>

    </main>
  </div>

  <script>
    lucide.createIcons();

    const tagPicker    = document.getElementById('tagPicker');
    const form         = document.getElementById('createGroupForm');
    const nameInput    = document.getElementById('groupName');
    const nameError    = document.getElementById('nameError');
    const submitBtn    = document.getElementById('submitBtn');
    const submitError  = document.getElementById('submitError');
    const createView   = document.getElementById('createGroupView');
    const successView  = document.getElementById('successView');
    const viewGroupBtn = document.getElementById('viewGroupBtn');

    const selectedInterestIds = new Set();

    // Load the interest vocabulary and render it as clickable pills
    // NOTE: "../groups/..." — because groups/ sits next to organizer/, auth/ and attendee/
    fetch('../groups/interests-list.php')
      .then(res => res.json())
      .then(result => {
        tagPicker.innerHTML = '';

        if (!result.success || result.data.length === 0) {
          tagPicker.innerHTML = '<span style="color:var(--text-secondary); font-size:0.85rem;">No interests available.</span>';
          return;
        }

        result.data.forEach(interest => {
          const pill = document.createElement('button');
          pill.type = 'button';
          pill.className = 'tag-pill';
          pill.textContent = interest.interest_name;

          pill.addEventListener('click', () => {
            const id = interest.interest_id;
            if (selectedInterestIds.has(id)) {
              selectedInterestIds.delete(id);
              pill.classList.remove('selected');
            } else {
              selectedInterestIds.add(id);
              pill.classList.add('selected');
            }
          });

          tagPicker.appendChild(pill);
        });
      })
      .catch(() => {
        tagPicker.innerHTML = '<span style="color:var(--text-secondary); font-size:0.85rem;">Could not load interests.</span>';
      });

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      nameError.style.display = 'none';
      submitError.style.display = 'none';

      if (nameInput.value.trim() === '') {
        nameError.style.display = 'block';
        return;
      }

      const formData = new FormData();
      formData.append('name', nameInput.value.trim());
      formData.append('description', document.getElementById('groupDescription').value.trim());
      selectedInterestIds.forEach(id => formData.append('interests[]', id));

      submitBtn.disabled = true;
      submitBtn.textContent = 'Creating…';

      fetch('../groups/create.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(result => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Group';

        if (!result.success) {
          submitError.textContent = result.message;
          submitError.style.display = 'inline';
          return;
        }

        createView.style.display = 'none';
        successView.style.display = 'block';
      })
      .catch(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Group';
        submitError.textContent = 'The group could not be created. Please try again.';
        submitError.style.display = 'inline';
      });
    });
  </script>
</body>

</html>