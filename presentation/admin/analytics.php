<?php require_once "includes/guard.php"; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Analytics - EventDNA Admin</title>
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
        <a href="moderation.php" class="admin-nav-item"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg> Moderation</a>
        <a href="analytics.php" class="admin-nav-item active"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg> Analytics</a>
      </nav>
      <div class="admin-footer">
        <a href="logout.php" class="admin-nav-item" style="color: var(--danger);"><svg style="width: 18px;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg> Log Out</a>
      </div>
    </aside>

    <main class="admin-content">
      <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
          <h1 class="page-title">Platform Analytics</h1>
          <p class="supporting-copy" style="margin-bottom: 0;">Comprehensive overview of platform performance.</p>
        </div>
        
        <div style="display: flex; gap: 1rem; align-items: center;">
          <select class="filter-select" style="padding: 0.6rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; font-weight: 500; outline: none; cursor: pointer;">
            <option>Last 7 Days</option>
            <option>Last 30 Days</option>
            <option selected>This Year</option>
            <option>All Time</option>
          </select>
          <button class="btn-primary" style="padding: 0.6rem 1.25rem; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            Download Report
          </button>
        </div>
      </div>

      <div class="stats-grid">
        <article class="stat-card">
          <h2 class="stat-value">1,248</h2>
          <p class="stat-label">Total Users</p>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; font-weight: 600; color: #16a34a;">+12.5% vs last period</div>
        </article>
        <article class="stat-card">
          <h2 class="stat-value">86</h2>
          <p class="stat-label">Active Events</p>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; font-weight: 600; color: #16a34a;">+5.2% vs last period</div>
        </article>
        <article class="stat-card">
          <h2 class="stat-value">4,921</h2>
          <p class="stat-label">Total Registrations</p>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; font-weight: 600; color: #16a34a;">+18.1% vs last period</div>
        </article>
        <article class="stat-card">
          <h2 class="stat-value">3,842</h2>
          <p class="stat-label">Event Check-ins</p>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; font-weight: 600; color: #16a34a;">+22.4% vs last period</div>
        </article>
        <article class="stat-card">
          <h2 class="stat-value">324</h2>
          <p class="stat-label">New Connections</p>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">+0.0% vs last period</div>
        </article>
        <article class="stat-card">
          <h2 class="stat-value">42</h2>
          <p class="stat-label">Active Communities</p>
          <div style="margin-top: 0.5rem; font-size: 0.8rem; font-weight: 600; color: #d97706;">-2.1% vs last period</div>
        </article>
      </div>

      <div class="charts-grid">
        <div class="chart-card">
          <h3 style="margin-bottom: 2rem;">User Growth</h3>
          <div class="custom-chart-wrapper" style="width: 100%; aspect-ratio: 16/9;">
            <!-- Fully Scalable Native SVG Line Chart -->
            <svg viewBox="0 0 800 400" style="width: 100%; height: 100%; overflow: visible; font-family: 'Inter', sans-serif;">
              <defs>
                <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="rgba(79, 16, 255, 0.4)" />
                  <stop offset="100%" stop-color="rgba(79, 16, 255, 0)" />
                </linearGradient>
              </defs>

              <!-- Grid Lines -->
              <line x1="50" y1="50" x2="780" y2="50" stroke="#f1f5f9" stroke-width="2" />
              <line x1="50" y1="125" x2="780" y2="125" stroke="#f1f5f9" stroke-width="2" />
              <line x1="50" y1="200" x2="780" y2="200" stroke="#f1f5f9" stroke-width="2" />
              <line x1="50" y1="275" x2="780" y2="275" stroke="#f1f5f9" stroke-width="2" />
              <line x1="50" y1="350" x2="780" y2="350" stroke="#e2e8f0" stroke-width="2" />

              <!-- Y-Axis Labels -->
              <text x="40" y="55" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">1000</text>
              <text x="40" y="130" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">750</text>
              <text x="40" y="205" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">500</text>
              <text x="40" y="280" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">250</text>
              <text x="40" y="355" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">0</text>

              <!-- Line Chart Area -->
              <!-- Points correspond to: 150, 230, 380, 290, 410, 590, 680, 820 out of max 1000 -->
              <!-- Y coordinates = 350 - (value / 1000 * 300) -->
              <!-- X coordinates = evenly spaced between 90 and 740 (dist: ~92.8) -->
              <polygon points="90,350 90,305 182.8,281 275.6,236 368.4,263 461.2,227 554,173 646.8,146 739.6,104 739.6,350" fill="url(#areaGradient)" />
              
              <!-- Line -->
              <polyline points="90,305 182.8,281 275.6,236 368.4,263 461.2,227 554,173 646.8,146 739.6,104" fill="none" stroke="#4f10ff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />

              <!-- Interactive Points -->
              <circle cx="90" cy="305" r="6" fill="#fff" stroke="#4f10ff" stroke-width="3" cursor="pointer"><title>Jan: 150 New Users</title></circle>
              <circle cx="182.8" cy="281" r="6" fill="#fff" stroke="#4f10ff" stroke-width="3" cursor="pointer"><title>Feb: 230 New Users</title></circle>
              <circle cx="275.6" cy="236" r="6" fill="#fff" stroke="#4f10ff" stroke-width="3" cursor="pointer"><title>Mar: 380 New Users</title></circle>
              <circle cx="368.4" cy="263" r="6" fill="#fff" stroke="#4f10ff" stroke-width="3" cursor="pointer"><title>Apr: 290 New Users</title></circle>
              <circle cx="461.2" cy="227" r="6" fill="#fff" stroke="#4f10ff" stroke-width="3" cursor="pointer"><title>May: 410 New Users</title></circle>
              <circle cx="554" cy="173" r="6" fill="#fff" stroke="#4f10ff" stroke-width="3" cursor="pointer"><title>Jun: 590 New Users</title></circle>
              <circle cx="646.8" cy="146" r="6" fill="#fff" stroke="#4f10ff" stroke-width="3" cursor="pointer"><title>Jul: 680 New Users</title></circle>
              <circle cx="739.6" cy="104" r="6" fill="#fff" stroke="#4f10ff" stroke-width="3" cursor="pointer"><title>Aug: 820 New Users</title></circle>

              <!-- X-Axis Labels -->
              <text x="90" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">Jan</text>
              <text x="182.8" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">Feb</text>
              <text x="275.6" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">Mar</text>
              <text x="368.4" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">Apr</text>
              <text x="461.2" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">May</text>
              <text x="554" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">Jun</text>
              <text x="646.8" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">Jul</text>
              <text x="739.6" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">Aug</text>
            </svg>
          </div>
        </div>
        
        <div class="chart-card">
          <h3 style="margin-bottom: 2rem;">Event Activity</h3>
          <div class="custom-chart-wrapper" style="width: 100%; aspect-ratio: 16/9;">
             <!-- Fully Scalable Native SVG Bar Chart -->
             <svg viewBox="0 0 800 400" style="width: 100%; height: 100%; overflow: visible; font-family: 'Inter', sans-serif;">
              <!-- Grid Lines -->
              <line x1="50" y1="50" x2="780" y2="50" stroke="#f1f5f9" stroke-width="2" />
              <line x1="50" y1="125" x2="780" y2="125" stroke="#f1f5f9" stroke-width="2" />
              <line x1="50" y1="200" x2="780" y2="200" stroke="#f1f5f9" stroke-width="2" />
              <line x1="50" y1="275" x2="780" y2="275" stroke="#f1f5f9" stroke-width="2" />
              <line x1="50" y1="350" x2="780" y2="350" stroke="#e2e8f0" stroke-width="2" />

              <!-- Y-Axis Labels -->
              <text x="40" y="55" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">50</text>
              <text x="40" y="130" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">37</text>
              <text x="40" y="205" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">25</text>
              <text x="40" y="280" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">12</text>
              <text x="40" y="355" fill="#64748b" font-size="12" font-weight="600" text-anchor="end">0</text>

              <!-- Bars (Width = 40) -->
              <!-- Values: 12, 19, 15, 25, 22, 30, 28, 40 (Max 50) -->
              <!-- Height = value / 50 * 300, Y = 350 - Height -->
              <rect x="70" y="278" width="40" height="72" fill="#4f10ff" rx="4" cursor="pointer"><title>Jan: 12 Events</title></rect>
              <rect x="162.8" y="236" width="40" height="114" fill="#4f10ff" rx="4" cursor="pointer"><title>Feb: 19 Events</title></rect>
              <rect x="255.6" y="260" width="40" height="90" fill="#4f10ff" rx="4" cursor="pointer"><title>Mar: 15 Events</title></rect>
              <rect x="348.4" y="200" width="40" height="150" fill="#4f10ff" rx="4" cursor="pointer"><title>Apr: 25 Events</title></rect>
              <rect x="441.2" y="218" width="40" height="132" fill="#4f10ff" rx="4" cursor="pointer"><title>May: 22 Events</title></rect>
              <rect x="534" y="170" width="40" height="180" fill="#4f10ff" rx="4" cursor="pointer"><title>Jun: 30 Events</title></rect>
              <rect x="626.8" y="182" width="40" height="168" fill="#4f10ff" rx="4" cursor="pointer"><title>Jul: 28 Events</title></rect>
              <rect x="719.6" y="110" width="40" height="240" fill="#4f10ff" rx="4" cursor="pointer"><title>Aug: 40 Events</title></rect>

              <!-- X-Axis Labels -->
              <text x="90" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">Jan</text>
              <text x="182.8" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle">Feb</text>
              <text x="255.6" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle" transform="translate(20,0)">Mar</text>
              <text x="348.4" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle" transform="translate(20,0)">Apr</text>
              <text x="441.2" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle" transform="translate(20,0)">May</text>
              <text x="534" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle" transform="translate(20,0)">Jun</text>
              <text x="626.8" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle" transform="translate(20,0)">Jul</text>
              <text x="719.6" y="380" fill="#64748b" font-size="13" font-weight="600" text-anchor="middle" transform="translate(20,0)">Aug</text>
            </svg>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>
