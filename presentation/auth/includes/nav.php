<?php
$currentRole = $_GET['role'] ?? 'attendee';
$isOrganizer = $currentRole === 'organizer';
$portalName = $isOrganizer ? "Organizer Portal" : "Attendee Portal";
$switchRole = $isOrganizer ? "attendee" : "organizer";
$switchText = $isOrganizer ? "Switch to Attendee" : "Switch to Organizer";
$switchUrl = "?role=" . $switchRole;

// Some pages might need additional GET params kept, but for auth simple role toggle is enough
?>
<nav class="auth-nav">
  <div class="auth-nav-logo">
    <img src="../../images/logo.png" alt="EventDNA Logo" />
  </div>
  <div class="auth-nav-right">
    <span class="auth-nav-portal"><?php echo $portalName; ?></span>
    <a href="<?php echo $switchUrl; ?>" class="auth-nav-switch" id="navSwitchRole">
      <?php echo $switchText; ?> &rarr;
    </a>
  </div>
</nav>

<script>
  // In case the page relies on URL search params instead of PHP GET for dynamic updating,
  // we update the switch URL using JS to ensure it goes back to the current page path but with the new role.
  document.addEventListener('DOMContentLoaded', () => {
    const navSwitch = document.getElementById('navSwitchRole');
    if (navSwitch) {
      const urlParams = new URLSearchParams(window.location.search);
      const currentRole = urlParams.get('role') || 'attendee';
      const switchRole = currentRole === 'organizer' ? 'attendee' : 'organizer';
      
      const currentPath = window.location.pathname;
      navSwitch.href = currentPath + '?role=' + switchRole;
    }
  });
</script>
