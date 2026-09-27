<?php
session_start();
require_once __DIR__ . '/../../attendee/includes/avatar.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login/index.php");
    exit;
}
$attendeeName = $_SESSION['full_name'] ?? 'Attendee';
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
          <a href="../community/community-hub/index.php" class="nav-link">Communities</a>
          <a href="#" class="nav-link active">Connections</a>
        </div>
      </div>
      <div class="nav-right">
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
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
      <span style="background: var(--primary-tint); color: var(--primary); font-size: 0.75rem; font-weight: 700; padding: 0.3rem 0.6rem; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.05em;">Networking Hub</span>
      <span style="color: #64748b; font-size: 0.85rem; font-weight: 500;">Oct 24–26, 2026</span>
    </div>
    <h1 style="font-size: 1.75rem; font-weight: 700; color: #0f172a; margin: 0; letter-spacing: -0.02em;">AI Innovation Summit 2026</h1>
  </div>
  <div class="main-grid">
    <div class="main-col">

      <div class="section-title-row" style="display:flex; flex-direction:column; gap:0.5rem; margin-bottom: 2rem;">
        <h2 style="font-size: 1.8rem; margin:0;">My Matches</h2>
        <p style="color: var(--text-secondary); margin:0;">People matched with you for networking at this event.</p>
      </div>

      <div class="matches-grid">

        <div class="match-card">
          <div class="match-top">
            <div class="match-person">
              <div class="avatar avatar-1">SJ</div>
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
          <div class="match-quote" style="margin-top: 1rem;">
            <span style="font-weight:600; color:var(--text-primary); display:block; margin-bottom:0.25rem; font-size:0.85rem;">Why this match:</span>
            You both share interests in AI research and robotics.
          </div>
          <div class="match-actions" style="margin-top: 1.5rem;">
            <a href="#" class="btn-secondary" style="flex:1; text-align:center;">View Profile</a>
            <a href="#" class="btn-primary" style="flex:1; text-align:center;">Connect</a>
          </div>
        </div>

        <div class="match-card">
          <div class="match-top">
            <div class="match-person">
              <div class="avatar avatar-2">MV</div>
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
          <div class="match-quote" style="margin-top: 1rem;">
            <span style="font-weight:600; color:var(--text-primary); display:block; margin-bottom:0.25rem; font-size:0.85rem;">Why this match:</span>
            Shared networking goals and interest in enterprise ML deployment.
          </div>
          <div class="match-actions" style="margin-top: 1.5rem;">
            <a href="#" class="btn-secondary" style="flex:1; text-align:center;">View Profile</a>
            <a href="#" class="btn-primary" style="flex:1; text-align:center;">Connect</a>
          </div>
        </div>

      </div>

      <div class="section-title-row" style="margin-top: 3rem; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.4rem;">My Connections</h2>
      </div>
      <div style="background: var(--surface-color); border: 1px solid var(--divider-color); border-radius: var(--radius-lg); overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--divider-color);">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="avatar avatar-1" style="width:40px; height:40px;">SJ</div>
            <div>
              <div style="font-weight: 600; font-size: 0.95rem;">Sarah Jenkins</div>
              <div style="font-size: 0.85rem; color: var(--text-secondary);">Lead AI Researcher</div>
            </div>
          </div>
          <a href="#" class="btn-secondary">View</a>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 1.5rem;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="avatar" style="width:40px; height:40px; background: #e2e8f0; color: #475569;">JP</div>
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
        <div class="qr-box" style="margin: 0 auto 1.5rem auto; width: 160px; height: 160px; display: flex; align-items: center; justify-content: center; background: #ffffff; border: 2px dashed rgba(255, 255, 255, 0.4); border-radius: var(--radius-md);">
          <span style="font-weight: 700; color: #94a3b8; font-size: 1.2rem; letter-spacing: 0.1em;">QR</span>
        </div>
        <button class="btn-secondary w-full" style="justify-content: center;">View My QR</button>
      </div>

      <div class="side-card">
        <h4 style="margin-bottom: 1.25rem;">Connection Requests</h4>
        
        <div class="person-row" style="margin-bottom: 1rem;">
          <div class="avatar-sm avatar-1">SJ</div>
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
      <div class="avatar avatar-1" style="width:80px; height:80px; font-size:2rem; margin: 0 auto 1rem auto;">SJ</div>
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
        <button class="btn-secondary" style="flex:1; justify-content:center;">Message</button>
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