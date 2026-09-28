<?php

require_once __DIR__ . '/../includes/guard.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Matches — EventDNA</title>
<link rel="stylesheet" href="../../../globals.css" />
<link rel="stylesheet" href="../dashboard/styles.css" />
<link rel="stylesheet" href="styles.css">
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
          <a href="../community/index.php" class="nav-link">Communities</a>
          <a href="#" class="nav-link active">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <?= nav_notifications_html() ?>
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <?= nav_avatar_html($attendeeName) ?>
            <span class="nav-profile-name"><?= htmlspecialchars(explode(' ', trim($attendeeName))[0]) ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="nav-dropdown">
            <a href="../settings/index.php" class="dropdown-item">Profile</a>
            <a href="../../auth/logout/index.php" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

<div class="dashboard-shell">
<div class="dashboard-content">
  <div style="margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0;">
    <h1 class="page-title">Networking Hub</h1>
    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
      <span style="color: var(--text-secondary); font-size: 0.95rem; font-weight: 500;">AI Innovation Summit 2026</span>
      <span style="color: #cbd5e1;">&bull;</span>
      <span style="color: var(--text-secondary); font-size: 0.95rem; font-weight: 500;">Oct 24–26, 2026</span>
    </div>
  </div>
    </div>
  <div class="main-grid">
    <div class="main-col">

      <div class="section-title-row" style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom: 2rem;">
        <div style="display:flex; flex-direction:column; gap:0.5rem;">
          <h2 style="font-size: 1.8rem; margin:0;">My Matches</h2>
          <p style="color: var(--text-secondary); margin:0;">People matched with you for networking at this event.</p>
        </div>
        <div style="display:flex; align-items:center; gap:1rem; padding-bottom: 0.25rem;">
          <span style="font-size:0.85rem; color:var(--text-secondary);">Last updated: Just now</span>
          <button class="btn-secondary" style="padding: 0.4rem 0.6rem; display: flex; align-items: center; gap: 0.4rem;" title="Refresh Matches" onclick="window.location.reload();">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 14px; height: 14px;"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
            <span style="font-size: 0.85rem; font-weight: 500;">Refresh</span>
          </button>
        </div>
      </div>

      <div class="matches-grid">

        <div class="match-card">
          <div class="match-top">
            <div class="match-person">
              <div class="avatar"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" alt="Sarah Jenkins"></div>
              <div>
                <div class="match-name-row">
                  <span class="name">Sarah Jenkins</span>
                  <span style="font-size: 0.85rem; font-weight: 700; color: var(--primary); margin-left: 0.5rem;">84%</span>
                </div>
                <div class="match-role">Lead AI Researcher<br><span style="font-size: 0.85rem; color: var(--text-tertiary);">ABC Technologies</span></div>
              </div>
            </div>
          </div>
          <div class="match-tags" style="margin-top: 1rem;">
            <span class="tag">AI Research</span>
            <span class="tag">Robotics</span>
          </div>
          <p style="margin-top: 1rem; margin-bottom: 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
            Experienced AI researcher specializing in deep learning and computer vision. Passionate about building robust models for healthcare applications. Looking to connect with founders and fellow researchers to explore collaborative opportunities.
          </p>
          <div class="match-actions" style="margin-top: 1.5rem;">
            <a href="#" class="btn-secondary" style="flex:1; text-align:center;">View Profile</a>
            <a href="#" class="btn-primary" style="flex:1; text-align:center;">Connect</a>
          </div>
        </div>

        <div class="match-card">
          <div class="match-top">
            <div class="match-person">
              <div class="avatar"><img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" alt="Marcus Vance"></div>
              <div>
                <div class="match-name-row">
                  <span class="name">Marcus Vance</span>
                  <span style="font-size: 0.85rem; font-weight: 700; color: var(--primary); margin-left: 0.5rem;">88%</span>
                </div>
                <div class="match-role">Chief Data Scientist<br><span style="font-size: 0.85rem; color: var(--text-tertiary);">XYZ Technologies</span></div>
              </div>
            </div>
          </div>
          <div class="match-tags" style="margin-top: 1rem;">
            <span class="tag">Machine Learning</span>
            <span class="tag">Data</span>
          </div>
          <p style="margin-top: 1rem; margin-bottom: 0; font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
            Leading data science initiatives at XYZ Technologies. Always on the lookout for innovative ways to deploy scalable ML models in enterprise environments. I'm here to find ambitious engineers and discuss MLOps best practices.
          </p>
          <div class="match-actions" style="margin-top: 1.5rem;">
            <a href="#" class="btn-secondary" style="flex:1; text-align:center;">View Profile</a>
            <a href="#" class="btn-primary" style="flex:1; text-align:center;">Connect</a>
          </div>
        </div>

      </div>

      <div class="section-title-row" style="margin-top: 3rem; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.4rem;">My Connections</h2>
      </div>
      <div style="background: var(--surface-color); border: 1px solid var(--divider-color); border-radius: 4px; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--divider-color);">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="avatar" style="width:40px; height:40px;"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" alt="Sarah Jenkins"></div>
            <div>
              <div style="font-weight: 600; font-size: 0.95rem;">Sarah Jenkins</div>
              <div style="font-size: 0.85rem; color: var(--text-secondary);">Lead AI Researcher</div>
            </div>
          </div>
          <a href="#" class="btn-secondary">View</a>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="avatar" style="width:40px; height:40px;"><img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" alt="John Perera"></div>
            <div>
              <div style="font-weight: 600; font-size: 0.95rem;">John Perera</div>
              <div style="font-size: 0.85rem; color: var(--text-secondary);">Software Engineer</div>
            </div>
          </div>
          <a href="#" class="btn-secondary">View</a>
        </div>
      </div>

    </div>

    <!-- Sidebar -->
    <div class="side-col">

      <div class="qr-card" style="text-align:center;">
        <h3 style="font-size: 1.15rem; margin-bottom: 1.5rem;">Your Personal QR</h3>
        <div class="qr-box" style="margin: 0 auto 1.5rem auto; width: 160px; height: 160px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border: 1px solid #d1d5db; border-radius: var(--radius-md); overflow: hidden;">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=EventDNA-Demo-QR" alt="Your Personal QR" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <button class="btn-secondary w-full" style="justify-content: center;">View My QR</button>
      </div>

      <div class="side-card">
        <h4 style="margin-bottom: 1.25rem;">Connection Requests</h4>
        
        <div class="person-row" style="margin-bottom: 1rem;">
          <div class="avatar avatar-sm"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" alt="Sarah Jenkins"></div>
          <div class="person-row-info">
            <div class="person-row-name">Sarah Jenkins</div>
            <div class="person-row-role">AI Researcher</div>
          </div>
        </div>
        <div style="display: flex; gap: 0.5rem;">
          <button class="btn-primary" style="flex: 1;">Accept</button>
          <button class="btn-secondary" style="flex: 1;">Decline</button>
        </div>

      </div>

    </div>
  </div>
</div>
</div>

<div class="modal-overlay" id="profileModal">
  <div class="modal profile-modal">
    <button class="modal-close" id="closeProfileModal">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
    
    <div class="profile-header" style="text-align: center; margin-bottom: 2rem;">
      <div class="avatar" style="width:80px; height:80px; margin: 0 auto 1rem auto;"><img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80" style="width:100%; height:100%; object-fit:cover; border-radius:50%;" alt="Sarah Jenkins"></div>
      <div class="profile-title-col">
        <h2 style="font-size:1.4rem; font-weight:700; margin-bottom:0.2rem;">Sarah Jenkins</h2>
        <p style="color:var(--text-secondary); font-size:0.95rem;">Lead AI Researcher at ABC Technologies</p>
      </div>
    </div>
    
    <div class="modal-body profile-body">
      <div class="profile-section" style="margin-bottom:1.5rem;">
        <h3 style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">About</h3>
        <p style="font-size:0.9rem; color:var(--text-secondary); line-height:1.6;">Experienced AI researcher specializing in deep learning and computer vision. Passionate about building robust models for healthcare applications. Looking to connect with founders and fellow researchers to explore collaborative opportunities.</p>
      </div>

      <div class="profile-section" style="margin-bottom:1.5rem;">
        <h3 style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">Networking Goals</h3>
        <div class="match-tags">
          <span class="tag">Find Collaborators</span>
          <span class="tag">Mentorship</span>
          <span class="tag">Explore Startups</span>
        </div>
      </div>

      <div class="profile-section" style="margin-bottom:1.5rem;">
        <h3 style="font-size:1rem; font-weight:700; margin-bottom:0.5rem;">Skills &amp; Interests</h3>
        <div class="match-tags">
          <span class="tag">AI Research</span>
          <span class="tag">Robotics</span>
          <span class="tag">Medical Imaging</span>
          <span class="tag">Python</span>
        </div>
      </div>

      <div class="profile-actions" style="display:flex; gap:1rem; margin-top:2rem;">
        <button class="btn-primary" style="flex:1; justify-content:center;">Connect</button>
      </div>
    </div>
  </div>
</div>

<script>
  const viewBtns = document.querySelectorAll('.btn-secondary');
  const modal = document.getElementById('profileModal');
  const closeBtn = document.getElementById('closeProfileModal');

  viewBtns.forEach(btn => {
    if (btn.textContent.trim() === 'View Profile' || btn.textContent.trim() === 'View') {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      });
    }
  });

  closeBtn.addEventListener('click', () => {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  });

  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }
  });
</script>

</body>
</html>