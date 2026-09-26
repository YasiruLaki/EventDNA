<?php
$activeNav = $activeNav ?? '';
$organizerName = $organizerName ?? 'Organizer';
?>
<style>
  .organizer-nav {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(226, 232, 240, 0.8);
    position: sticky;
    top: 0;
    z-index: 1000;
  }
  .organizer-nav .nav-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1.5rem;
    height: 72px;
  }
  .organizer-nav .nav-left {
    display: flex;
    align-items: center;
    gap: 2.5rem;
    height: 100%;
  }
  .organizer-nav .nav-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
  }
  .organizer-nav .nav-logo-img {
    height: 32px;
    width: auto;
  }
  .organizer-nav .org-badge {
    background: var(--primary-tint, #f3f0ff);
    color: var(--primary, #4f10ff);
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    letter-spacing: 0.5px;
  }
  .organizer-nav .nav-links {
    display: flex;
    gap: 1.5rem;
    height: 100%;
  }
  .organizer-nav .nav-link {
    display: flex;
    align-items: center;
    color: var(--text-secondary, #64748b);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    height: 100%;
    border-bottom: 2px solid transparent;
    transition: color 0.2s ease, border-color 0.2s ease;
  }
  .organizer-nav .nav-link:hover {
    color: var(--text-primary, #0f172a);
  }
  .organizer-nav .nav-link.active {
    color: var(--primary, #4f10ff);
    border-bottom-color: var(--primary, #4f10ff);
    font-weight: 600;
  }
  
  .organizer-nav .nav-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
  }
  
  .organizer-nav .nav-btn-primary {
    background: var(--primary, #4f10ff);
    color: white;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    padding: 0.5rem 1.25rem;
    border-radius: 8px;
    transition: background-color 0.2s;
    box-shadow: 0 4px 6px -1px rgba(79, 16, 255, 0.2);
  }
  .organizer-nav .nav-btn-primary:hover {
    background: var(--primary-hover, #3b00d6);
  }

  .organizer-nav .nav-profile-menu {
    position: relative;
  }
  .organizer-nav .nav-profile-btn {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: background-color 0.2s;
  }
  .organizer-nav .nav-profile-btn:hover {
    background-color: var(--tertiary, #f1f5f9);
  }
  .organizer-nav .nav-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-tint, #f3f0ff);
    color: var(--primary, #4f10ff);
    font-weight: 600;
    font-size: 0.9rem;
  }
  .organizer-nav .nav-profile-name {
    color: var(--text-primary, #0f172a);
    font-size: 0.95rem;
    font-weight: 500;
  }
  .organizer-nav .nav-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 8px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    min-width: 180px;
    display: none;
    flex-direction: column;
    overflow: hidden;
  }
  .organizer-nav .nav-dropdown.show {
    display: flex;
  }
  .organizer-nav .dropdown-item {
    padding: 0.75rem 1rem;
    color: var(--text-primary, #0f172a);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: background-color 0.2s;
    display: flex;
    align-items: center;
    gap: 0.6rem;
  }
  .organizer-nav .dropdown-item:hover {
    background: var(--tertiary, #f1f5f9);
  }
  .organizer-nav .dropdown-item.text-danger {
    color: var(--error, #dc2626);
  }
  .organizer-nav .dropdown-item.text-danger:hover {
    background-color: var(--error-tint, #fef2f2);
  }
</style>

<nav class="organizer-nav">
  <div class="nav-container">
    <div class="nav-left">
      <a href="dashboard.php" class="nav-logo">
        <img src="../images/logo.png" alt="EventDNA" class="nav-logo-img">
        <span class="org-badge">Organizer</span>
      </a>
      <div class="nav-links">
        <a href="dashboard.php" class="nav-link<?= $activeNav === 'dashboard' ? ' active' : '' ?>">Dashboard</a>
        <a href="dashboard.php#events" class="nav-link<?= $activeNav === 'events' ? ' active' : '' ?>">My Events</a>
        <a href="manage-groups.php" class="nav-link<?= $activeNav === 'communities' ? ' active' : '' ?>">Communities</a>
      </div>
    </div>
    <div class="nav-right">
      <a href="create-event.php" class="nav-btn-primary">
        <i data-lucide="plus" style="width:16px;height:16px;display:inline-block;vertical-align:-3px;margin-right:4px;"></i>
        Create Event
      </a>
      <div class="nav-profile-menu">
        <button class="nav-profile-btn" aria-label="Profile Menu" id="orgProfileBtn">
          <span class="nav-avatar"><?= h(mb_strtoupper(mb_substr($organizerName, 0, 1))) ?></span>
          <span class="nav-profile-name"><?= h($organizerName) ?></span>
          <i data-lucide="chevron-down" style="width:16px;height:16px;color:var(--text-secondary);"></i>
        </button>
        <div class="nav-dropdown" id="orgProfileDropdown">
          <a href="../auth/logout.php" class="dropdown-item text-danger">
            <i data-lucide="log-out" style="width:16px;height:16px;"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const orgProfileBtn = document.getElementById('orgProfileBtn');
    const orgProfileDropdown = document.getElementById('orgProfileDropdown');
    
    if (orgProfileBtn && orgProfileDropdown) {
      orgProfileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        orgProfileDropdown.classList.toggle('show');
      });
      document.addEventListener('click', () => {
        orgProfileDropdown.classList.remove('show');
      });
    }
    
    // Initialize Lucide icons if available
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });
</script>
