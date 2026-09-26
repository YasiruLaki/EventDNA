<?php
require_once __DIR__ . "/includes/guard.php";
$activeNav = 'communities';
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Groups - EventDNA</title>
  <link rel="stylesheet" href="../attendee/dashboard/styles.css" />
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .group-row { display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid rgba(226,232,240,0.9); }
    .group-row:last-child { border-bottom:none; }
    .group-name { font-weight:600; color:var(--secondary); }
    .group-meta { color:var(--text-secondary); font-size:0.85rem; margin-top:0.15rem; }
    .empty-state { padding:3rem; text-align:center; color:var(--text-secondary); }
    .category-pills { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; }
    .pill { padding: 0.4rem 1rem; border-radius: 999px; cursor: pointer; font-size: 0.9rem; font-weight: 500; color: var(--text-secondary); background: transparent; transition: all 0.2s; }
    .pill.active { background: var(--primary); color: white; }
    .pill:hover:not(.active) { background: rgba(0,0,0,0.05); }
  </style>
</head>

<body>
  <?php include 'includes/nav.php'; ?>

  <div class="dashboard-shell">
    <main class="dashboard-content">
      <a href="dashboard.php#community" style="display:inline-flex; align-items:center; gap:0.5rem; text-decoration:none; color:var(--text-secondary); margin-bottom:1.5rem; font-size:0.95rem; font-weight:500;">
        <i data-lucide="arrow-left" style="width:18px;height:18px;"></i>
        Back to Dashboard
      </a>

      <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem;">
        <div>
          <h1 class="page-title" style="margin:0 0 0.5rem 0;">Manage Groups</h1>
          <p class="supporting-copy" style="margin:0;">Groups you own or belong to.</p>
        </div>
        <a href="create-group.html" class="btn-primary">+ Create Group</a>
      </div>

      <div class="category-pills">
        <span class="pill active" data-status="ACTIVE">Active Groups</span>
        <span class="pill" data-status="ARCHIVED">Archived Groups</span>
      </div>

      <div style="background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(226, 232, 240, 0.9); border-radius: 12px; overflow: hidden;" id="groupList">
        <div class="empty-state">Loading your groups…</div>
      </div>
    </main>
  </div>

  <script>
    lucide.createIcons();

    const groupList = document.getElementById('groupList');
    let currentStatus = 'ACTIVE';

    document.querySelectorAll('.pill').forEach(pill => {
      pill.addEventListener('click', () => {
        document.querySelectorAll('.pill').forEach(p => p.classList.remove('active'));
        pill.classList.add('active');
        currentStatus = pill.dataset.status;
        loadGroups();
      });
    });

    function loadGroups() {
      fetch(`../groups/list.php?mine=1&status=${currentStatus}`)
        .then(res => res.json())
        .then(result => {
          groupList.innerHTML = '';

          if (!result.success || result.data.length === 0) {
            groupList.innerHTML = '<div class="empty-state">You haven\'t created any groups yet.</div>';
            return;
          }

          result.data.forEach(group => {
            const row = document.createElement('div');
            row.className = 'group-row';
            const desc = group.description ? (group.description.length > 60 ? group.description.substring(0, 60) + '...' : group.description) : 'No description';
            
            let actionsHTML = '';
            if (currentStatus === 'ACTIVE') {
              actionsHTML = `
                <a href="update-group.html?group_id=${group.group_id}" class="btn-secondary" style="padding:0.4rem 1rem; font-size:0.85rem; font-weight:600;">Edit</a>
                <button type="button" class="btn-secondary archive-btn" data-id="${group.group_id}" style="padding:0.4rem 1rem; font-size:0.85rem; font-weight:600; color:var(--danger);">Archive</button>
              `;
            } else {
              actionsHTML = `<span style="font-size: 0.85rem; color: var(--text-tertiary);">Archived</span>`;
            }

            row.innerHTML = `
              <div>
                <div class="group-name">${group.name}</div>
                <div class="group-meta">${desc} <br> ${group.member_count} members · created ${new Date(group.created_at).toLocaleDateString()}</div>
              </div>
              <div style="display:flex; gap:0.75rem;">
                ${actionsHTML}
              </div>
            `;
            groupList.appendChild(row);
          });

          // Wire up Archive buttons
          document.querySelectorAll('.archive-btn').forEach(btn => {
            btn.addEventListener('click', () => {
              if (!confirm('Archive this group? Members will no longer see it in Discover Groups.')) return;

              const formData = new FormData();
              formData.append('group_id', btn.dataset.id);

              fetch('../groups/delete.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(result => {
                  if (result.success) {
                    loadGroups(); // refresh the list
                  } else {
                    alert(result.message);
                  }
                });
            });
          });
        })
        .catch(() => {
          groupList.innerHTML = '<div class="empty-state">Could not load groups.</div>';
        });
    }

    loadGroups();
  </script>
</body>

</html>