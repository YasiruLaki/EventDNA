<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Attendee Dashboard - EventDNA</title>
  <link rel="stylesheet" href="./styles.css" />
</head>
<body>
  <nav class="top-nav">
    <div class="nav-container">
      <div class="nav-left">
        <a href="#" class="nav-logo">
          <img src="../../images/logo.png" alt="EventDNA" class="nav-logo-img">
        </a>
        <div class="nav-links">
          <a href="../../events/ExploreEvents/index.php" class="nav-link">Find Events</a>
          <a href="../../events/myEvents/index.php" class="nav-link">My Events</a>
          <a href="../community/community-hub/index.html" class="nav-link">Communities</a>
          <a href="../myConnections/index.html" class="nav-link">Connections</a>
        </div>
      </div>
      <div class="nav-right">
        <div class="nav-profile-menu">
          <button class="nav-profile-btn" aria-label="Profile Menu">
            <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80" alt="Profile" class="nav-avatar" />
            <span class="nav-profile-name">Yasiru</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="chevron"><path d="m6 9 6 6 6-6"/></svg>
          </button>
          <div class="nav-dropdown">
            <a href="../onboarding/index.html" class="dropdown-item">Profile</a>
            <a href="../settings/index.html" class="dropdown-item">Settings</a>
            <a href="#" class="dropdown-item text-danger">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <div class="dashboard-shell">
    <main class="dashboard-content">
      <section class="hero-row hero-header">
        <div class="hero-copy">
          <h1>Good Evening, Yasiru!</h1>
          <p class="supporting-copy">
            Your profile is almost complete. Complete it to get better event and networking recommendations.
          </p>
        </div>
        <div class="hero-actions">
          <a class="btn-secondary hero-btn hero-btn-soft" href="../onboarding/index.html">Complete Profile</a>
          <a class="btn-primary hero-btn" href="../../events/ExploreEvents/index.php">Explore Events</a>
        </div>
      </section>

      <section class="stats-grid" aria-label="Overview stats">
        <article class="stat-card accent-violet">
          <span class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 3v3M17 3v3M4 9h16M6 6h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </span>
          <div>
            <strong>2</strong>
            <p>Upcoming Events</p>
          </div>
        </article>

        <article class="stat-card accent-blue">
          <span class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0Zm4 10a8 8 0 1 0-16 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </span>
          <div>
            <strong>124</strong>
            <p>Connections</p>
          </div>
        </article>

        <article class="stat-card accent-red">
          <span class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 7h10v10H7z" stroke="currentColor" stroke-width="1.8"/><path d="M5 5h4m6 0h4M5 19h4m6 0h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          </span>
          <div>
            <strong>5</strong>
            <p>Pending Requests</p>
          </div>
        </article>

        <article class="stat-card accent-gray">
          <span class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5h16v10H7l-3 3V5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M8 9h8M8 12h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
          </span>
          <div>
            <strong>12</strong>
            <p>Groups</p>
          </div>
        </article>
      </section>

      <section class="main-grid">
        <div class="left-column">
          <article class="feature-card">
              <div class="feature-cover">
                <img
                  src="https://orlandosydney.com/wp-content/uploads/2023/08/Business-Networking-Photo-Example-for-Professionals-at-the-ICC-Sydney-Convention-Centre.-Photography.-By-orlandosydney.com-OS1_7380.jpg"
                  alt="Featured event cover showing a packed conference audience"
                />
                <span class="date-tag feature-date">Oct 24 - 26</span>
              </div>
            <div class="feature-badges">
              <span class="pill pill-primary">Starts in 16 Days</span>
              <span class="pill">&check; Registered</span>
            </div>
            <div class="feature-copy">
              <h2>Global Tech Innovators Summit 2026</h2>
              <div class="feature-meta">
                <span>Oct 24 - 26, 2026</span>
                <span>Moscone Center, San Francisco</span>
              </div>
            </div>
            <div class="feature-actions">
              <a class="btn-primary" href="../../events/eventView/index.php">View Event Details</a>
              <a class="btn-secondary" href="../../events/registrationModel/index.html">View Event Pass</a>
            </div>
          </article>

          <article class="locked-card">
            <div class="locked-icon">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 10V8a5 5 0 1 1 10 0v2M6 10h12v10H6V10Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
              <h3>Networking Locked</h3>
              <p>Check in at the event to unlock your personalized connections.</p>
            </div>
            <a class="btn-secondary" href="../onboarding/index.html">Complete Profile</a>
          </article>

          <section class="section-row">
            <div class="section-head">
              <h3>Recommended for You</h3>
              <a href="../../events/ExploreEvents/index.php">View All &rarr;</a>
            </div>

            <div class="recommendation-row">
              <article class="event-card">
                <div class="event-image image-1">
                  <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80" alt="Design systems workshop audience" />
                  <span class="date-tag">Oct 12</span>
                </div>
                <div class="event-body">
                  <span class="event-chip">Workshop</span>
                  <h4>Design Systems Masterclass</h4>
                  <p>Online</p>
                </div>
              </article>

              <article class="event-card">
                <div class="event-image image-2">
                  <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1200&q=80" alt="Founders meetup networking event" />
                  <span class="date-tag">Nov 05</span>
                </div>
                <div class="event-body">
                  <span class="event-chip">Mixer</span>
                  <h4>Bay Area Founders Meetup</h4>
                  <p>San Francisco, CA</p>
                </div>
              </article>

              <article class="event-card">
                <div class="event-image image-3">
                  <img src="https://images.unsplash.com/photo-1515187029135-18ee286d815b?auto=format&fit=crop&w=1200&q=80" alt="Conference stage with speakers" />
                  <span class="date-tag">Nov 21</span>
                </div>
                <div class="event-body">
                  <span class="event-chip">Conference</span>
                  <h4>Future of Product Summit</h4>
                  <p>Seattle, WA</p>
                </div>
              </article>
            </div>
          </section>

          <section class="quick-actions-section">
            <div class="section-head compact">
              <h3>Quick Actions</h3>
            </div>

            <div class="quick-actions-grid">
              <a class="quick-action" href="../../events/ExploreEvents/index.php">
                <span class="quick-action-icon">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </span>
                <span>Find Events</span>
              </a>
              <a class="quick-action" href="../../events/myEvents/index.php">
                <span class="quick-action-icon">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 3v3M17 3v3M4 9h16M6 6h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span>My Events</span>
              </a>
              <a class="quick-action" href="#">
                <span class="quick-action-icon">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span>Check In</span>
              </a>
              <a class="quick-action" href="../../events/myEvents/index.php">
                <span class="quick-action-icon">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v18M4.5 8.5h15M4.5 15.5h15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </span>
                <span>My Matches</span>
              </a>
              <a class="quick-action" href="../community/community-hub/index.html">
                <span class="quick-action-icon">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 11a3 3 0 1 0-6 0 3 3 0 0 0 6 0Zm18 0a3 3 0 1 0-6 0 3 3 0 0 0 6 0ZM16 21a4 4 0 0 0-8 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <span>Community</span>
              </a>
              <a class="quick-action" href="#">
                <span class="quick-action-icon">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </span>
                <span>My QR</span>
              </a>
            </div>
          </section>
        </div>

        <aside class="right-column">
          <article class="side-card profile-strength-card">
            <div class="card-header-row">
              <div class="progress-ring" aria-hidden="true">
                <span>82%</span>
              </div>
              <div>
                <h3>Profile Strength</h3>
                <p>Almost ready for networking</p>
              </div>
            </div>

            <ul class="checklist">
              <li class="done">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="check-icon"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Basic Information
              </li>
              <li class="done">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="check-icon"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Professional Details
              </li>
              <li class="done">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="check-icon"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Skills
              </li>
              <li class="done">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="check-icon"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Interests
              </li>
              <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="circle-icon"><circle cx="12" cy="12" r="10"></circle></svg>
                Profile Photo
              </li>
              <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="circle-icon"><circle cx="12" cy="12" r="10"></circle></svg>
                Professional Bio
              </li>
              <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="circle-icon"><circle cx="12" cy="12" r="10"></circle></svg>
                Networking Goals
              </li>
            </ul>
          </article>

          <article class="side-card requests-card">
            <div class="section-head compact">
              <h3>Connection Requests</h3>
              <span class="badge">5</span>
            </div>

            <div class="request-list">
              <div class="request-item">
                <div class="avatar-sm">
                  <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=200&q=80" alt="Sarah Jenkins" />
                </div>
                <div>
                  <strong>Sarah Jenkins</strong>
                  <p>Shared interest: Technology</p>
                </div>
              </div>
              <div class="request-item">
                <div class="avatar-sm">
                  <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80" alt="David Chen" />
                </div>
                <div>
                  <strong>David Chen</strong>
                  <p>Shared skill: React</p>
                </div>
              </div>
            </div>

            <a class="inline-link" href="../community/community-hub/index.html">View all requests</a>
          </article>

          <article class="side-card alerts-card">
            <h3>Recent Notifications</h3>
            <div class="alert-list">
              <div class="alert-item">
                <span class="alert-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </span>
                <div>
                  <strong>Venue update</strong>
                  <p>Room changed for "Design Ops" track.</p>
                </div>
              </div>
              <div class="alert-item">
                <span class="alert-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </span>
                <div>
                  <strong>Community update</strong>
                  <p>New discussion in "AI Founders".</p>
                </div>
              </div>
            </div>
          </article>
        </aside>
      </section>
    </main>
  </div>
</body>
</html>