<?php require_once "includes/guard.php"; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Moderation Details - EventDNA Admin</title>
  <link rel="stylesheet" href="../../globals.css" />
  <link rel="stylesheet" href="./styles.css" />
</head>
<body>
  <div class="admin-layout">
    <aside class="admin-sidebar">
      <div class="admin-logo">
        <img src="../images/logo.png" alt="EventDNA" />
        <span style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--primary); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 0.25rem;">Admin</span>
      </div>
      <nav class="admin-nav">
        <a href="dashboard.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg> Dashboard</a>
        <a href="users.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg> Users</a>
        <a href="events.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg> Events</a>
        <a href="moderation.php" class="admin-nav-item active"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg> Moderation</a>
        <a href="analytics.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg> Analytics</a>
      </nav>
      <div class="admin-footer">
        <a href="logout.php" class="admin-nav-item" style="color: var(--danger);"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg> Log Out</a>
      </div>
    </aside>

    <main class="admin-content">
      <a href="moderation.php" class="back-link"><i data-lucide="arrow-left" style="width: 18px;"></i> Back to Moderation</a>
      
      <div class="detail-card">
        <div class="report-header">
          <h1>Reported Content</h1>
        </div>
        
        <div class="data-grid">
          <div class="data-block">
            <label>Author</label>
            <p>John Doe</p>
          </div>
          <div class="data-block">
            <label>Group</label>
            <p>AI Founders</p>
          </div>
          <div class="data-block">
            <label>Reported For</label>
            <p style="color: #d97706; font-weight: 700;">Spam</p>
          </div>
          <div class="data-block">
            <label>Reported</label>
            <p>24 Sep 2026</p>
          </div>
        </div>
        
        <div class="content-box">
          <label>Content</label>
          <p>"Looking for attendees to buy my crypto coin before the summit ends! Guaranteed 10x ROI!!!"</p>
        </div>
        
        <div class="actions-bar">
          <button class="btn btn-outline">Keep Content</button>
          <button class="btn btn-danger" id="removeBtn">Remove Content</button>
        </div>
      </div>
    </main>
  </div>

  <!-- Remove Modal -->
  <div id="removeModal" class="modal-overlay">
    <div class="modal-content">
      <h3>Remove Content</h3>
      <p>Are you sure you want to remove this content? The author will be notified.</p>
      <label style="display: block; font-size: 0.85rem; color: var(--secondary); font-weight: 700; margin-bottom: 0.5rem;">Reason for removal</label>
      <textarea placeholder="e.g. Violation of community spam guidelines..."></textarea>
      <div style="display: flex; gap: 1rem; justify-content: flex-end;">
        <button id="cancelModalBtn" class="btn btn-outline" style="padding: 0.75rem 1.5rem;">Cancel</button>
        <button class="btn btn-danger" style="padding: 0.75rem 1.5rem;" onclick="return initCustomConfirm(this, 'Are you sure you want to remove this item?', event);">Remove</button>
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();
    const removeBtn = document.getElementById('removeBtn');
    const removeModal = document.getElementById('removeModal');
    const cancelBtn = document.getElementById('cancelModalBtn');

    if(removeBtn && removeModal && cancelBtn) {
      removeBtn.addEventListener('click', () => removeModal.style.display = 'flex');
      cancelBtn.addEventListener('click', () => removeModal.style.display = 'none');
      removeModal.addEventListener('click', (e) => {
        if(e.target === removeModal) removeModal.style.display = 'none';
      });
    }
  </script>
<!-- Custom Confirm Modal Setup -->
<style>
.custom-confirm-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);
    display: none; align-items: center; justify-content: center; z-index: 999999;
}
.custom-confirm-modal {
    background: #fff; padding: 2rem; border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    max-width: 400px; width: 90%; text-align: center;
    animation: confirmPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes confirmPop { from { transform: scale(0.95) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
.custom-confirm-modal h3 { margin: 0 0 1rem; color: #0f172a; font-size: 1.25rem; font-weight: 800; font-family: 'Inter', sans-serif; }
.custom-confirm-modal p { color: #64748b; margin-bottom: 2rem; font-size: 0.95rem; line-height: 1.5; font-family: 'Inter', sans-serif; }
.custom-confirm-actions { display: flex; gap: 1rem; justify-content: center; }
.custom-confirm-btn {
    padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; font-family: 'Inter', sans-serif; transition: all 0.2s;
}
.custom-confirm-cancel { background: #f1f5f9; color: #475569; }
.custom-confirm-cancel:hover { background: #e2e8f0; }
.custom-confirm-danger { background: #ef4444; color: #fff; }
.custom-confirm-danger:hover { background: #dc2626; transform: translateY(-1px); }
</style>
<div class="custom-confirm-overlay" id="customConfirmOverlay">
    <div class="custom-confirm-modal">
        <h3 id="customConfirmTitle">Are you sure?</h3>
        <p id="customConfirmMessage">This action cannot be undone.</p>
        <div class="custom-confirm-actions">
            <button class="custom-confirm-btn custom-confirm-cancel" id="customConfirmCancel">Cancel</button>
            <button class="custom-confirm-btn custom-confirm-danger" id="customConfirmOk">Yes, I'm sure</button>
        </div>
    </div>
</div>
<script>
let pendingConfirmAction = null;
function initCustomConfirm(el, message, e) {
    if (e) e.preventDefault();
    document.getElementById('customConfirmMessage').innerText = message;
    
    // Auto title based on message
    let title = "Are you sure?";
    if(message.toLowerCase().includes('delete')) title = "Confirm Deletion";
    else if(message.toLowerCase().includes('remove')) title = "Confirm Removal";
    document.getElementById('customConfirmTitle').innerText = title;

    document.getElementById('customConfirmOverlay').style.display = 'flex';
    
    pendingConfirmAction = () => {
        if (el.tagName === 'FORM') {
            // Remove the onsubmit handler so it doesn't trigger again, then submit
            el.onsubmit = null;
            el.submit();
        } else if (el.tagName === 'BUTTON' || el.tagName === 'A') {
            if(el.form) {
                el.form.onsubmit = null;
                el.form.submit();
            } else {
                // If there's an href, navigate
                if(el.href && el.href !== '#' && !el.href.startsWith('javascript:')) {
                    window.location.href = el.href;
                }
            }
        }
    };
    return false;
}
document.getElementById('customConfirmCancel').addEventListener('click', () => {
    document.getElementById('customConfirmOverlay').style.display = 'none';
    pendingConfirmAction = null;
});
document.getElementById('customConfirmOk').addEventListener('click', () => {
    document.getElementById('customConfirmOverlay').style.display = 'none';
    if(pendingConfirmAction) pendingConfirmAction();
});
</script>
</body>
</html>
