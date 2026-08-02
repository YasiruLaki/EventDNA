
(function () {
  "use strict";

  var ED = window.ED = {};

  /* ------------------------------------------------------------------ *
   * 1. ICONS
   * ------------------------------------------------------------------ */
  var P = {
    dna:        '<path d="M4 20c0-8 16-8 16-16"/><path d="M4 4c0 8 16 8 16 16"/><path d="M8.5 6.5h4"/><path d="M11.5 17.5h4"/>',
    dashboard:  '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>',
    calendar:   '<rect x="3" y="4.5" width="18" height="16" rx="2.5"/><path d="M8 3v3M16 3v3M3 10h18"/>',
    pin:        '<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.6"/>',
    users:      '<path d="M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20"/><circle cx="9.5" cy="7.5" r="3.5"/><path d="M21 20v-1.5a4 4 0 0 0-3-3.87"/><path d="M16.5 4.2a3.5 3.5 0 0 1 0 6.6"/>',
    usercheck:  '<path d="M14 20v-1.5a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4V20"/><circle cx="8" cy="7.5" r="3.5"/><path d="m16 12 2.2 2.2L22.5 10"/>',
    qr:         '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><path d="M14 14h3v3h-3zM20 14h1M14 20h1M18 18h3v3h-3z"/>',
    scan:       '<path d="M3 8V5.5A2.5 2.5 0 0 1 5.5 3H8M16 3h2.5A2.5 2.5 0 0 1 21 5.5V8M21 16v2.5A2.5 2.5 0 0 1 18.5 21H16M8 21H5.5A2.5 2.5 0 0 1 3 18.5V16"/><path d="M3 12h18"/>',
    chart:      '<path d="M3 20h18"/><rect x="5" y="11" width="3.5" height="6" rx="1"/><rect x="10.5" y="7" width="3.5" height="10" rx="1"/><rect x="16" y="13" width="3.5" height="4" rx="1"/>',
    trending:   '<path d="m3 16 5.5-5.5 3.5 3.5L20 6"/><path d="M15 6h5v5"/>',
    download:   '<path d="M12 3v12"/><path d="m7.5 10.5 4.5 4.5 4.5-4.5"/><path d="M4 20h16"/>',
    upload:     '<path d="M12 16V4"/><path d="m7.5 8.5 4.5-4.5 4.5 4.5"/><path d="M4 20h16"/>',
    plus:       '<path d="M12 5v14M5 12h14"/>',
    search:     '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
    filter:     '<path d="M4 6h16M7 12h10M10 18h4"/>',
    bell:       '<path d="M18 9A6 6 0 1 0 6 9c0 5-2 6-2 6h16s-2-1-2-6"/><path d="M10.5 20a2 2 0 0 0 3 0"/>',
    chevdown:   '<path d="m6 9 6 6 6-6"/>',
    chevright:  '<path d="m9 6 6 6-6 6"/>',
    chevleft:   '<path d="m15 6-6 6 6 6"/>',
    arrowright: '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
    arrowup:    '<path d="M12 19V5"/><path d="m6 11 6-6 6 6"/>',
    arrowdown:  '<path d="M12 5v14"/><path d="m6 13 6 6 6-6"/>',
    more:       '<circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/>',
    edit:       '<path d="M4 20h4l10-10a2.5 2.5 0 0 0-3.5-3.5L4.5 16.5V20Z"/><path d="m13.5 7 3.5 3.5"/>',
    trash:      '<path d="M4 7h16"/><path d="M9 7V4.5h6V7"/><path d="M6 7l1 13h10l1-13"/>',
    eye:        '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>',
    mail:       '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.5 7 8.5 6 8.5-6"/>',
    check:      '<path d="m5 13 4.5 4.5L19 7"/>',
    checkcirc:  '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 5-5.5"/>',
    xcirc:      '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/>',
    x:          '<path d="M6 6l12 12M18 6 6 18"/>',
    clock:      '<circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3 2"/>',
    ticket:     '<path d="M4 8.5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2 2.2 2.2 0 0 0 0 4.4V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3.1a2.2 2.2 0 0 0 0-4.4Z"/><path d="M13 6.5v11"/>',
    file:       '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h4"/>',
    sheet:      '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M4 9h16M4 15h16M10 3v18"/>',
    printer:    '<path d="M7 8V3h10v5"/><rect x="4" y="8" width="16" height="8" rx="2"/><path d="M7 16h10v5H7z"/>',
    link:       '<path d="M10 13.5a4 4 0 0 0 5.7 0l2.8-2.8a4 4 0 1 0-5.7-5.7L11.5 6.3"/><path d="M14 10.5a4 4 0 0 0-5.7 0l-2.8 2.8a4 4 0 1 0 5.7 5.7l1.3-1.3"/>',
    settings:   '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 0 1-4 0v-.2A1.6 1.6 0 0 0 7.5 19l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3.6 13H3a2 2 0 0 1 0-4h.2a1.6 1.6 0 0 0 1.1-2.7L4.2 6a2 2 0 0 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 9.8 2.2V2a2 2 0 0 1 4 0v.2a1.6 1.6 0 0 0 2.7 1.1l.1-.1A2 2 0 0 1 19.4 6l-.1.1a1.6 1.6 0 0 0 1.1 2.7H21a2 2 0 0 1 0 4h-.2a1.6 1.6 0 0 0-1.4 1Z"/>',
    logout:     '<path d="M9 21H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h3"/><path d="M16 8l4 4-4 4"/><path d="M20 12H9"/>',
    alert:      '<path d="M10.3 4.3 2.8 17a2 2 0 0 0 1.7 3h15a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 16.5h.01"/>',
    info:       '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 7.8h.01"/>',
    refresh:    '<path d="M20 11A8 8 0 0 0 6.3 6.3L4 8.5"/><path d="M4 4v4.5h4.5"/><path d="M4 13a8 8 0 0 0 13.7 4.7L20 15.5"/><path d="M20 20v-4.5h-4.5"/>',
    sliders:    '<path d="M5 20v-6M5 10V4M12 20v-9M12 7V4M19 20v-4M19 12V4"/><path d="M2.5 14h5M9.5 7h5M16.5 16h5"/>',
    image:      '<rect x="3" y="4" width="18" height="16" rx="2.5"/><circle cx="8.5" cy="9.5" r="1.6"/><path d="m4 18 5-5 4 4 3-2.5 4 3.5"/>',
    globe:      '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 2.5 15.4 0 18M12 3c-2.5 2.6-2.5 15.4 0 18"/>',
    lock:       '<rect x="4.5" y="10.5" width="15" height="10.5" rx="2.5"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/>',
    send:       '<path d="M21 3 3 10.5l7 2.5 2.5 7Z"/><path d="M21 3 10 14"/>',
    activity:   '<path d="M3 12h4l2.5-6 4 12 2.5-6h5"/>',
    building:   '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h6v6H9z"/>',
    inbox:      '<path d="M3 12h5l1.5 3h5L16 12h5"/><path d="M5.5 4.5h13l2.5 7.5v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6Z"/>',
    share:      '<circle cx="18" cy="5.5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="18.5" r="2.5"/><path d="m8.2 10.8 7.6-4M8.2 13.2l7.6 4"/>',
    star:       '<path d="m12 4 2.4 5 5.6.7-4 3.9 1 5.4-5-2.7-5 2.7 1-5.4-4-3.9 5.6-.7Z"/>',
    grip:       '<circle cx="9" cy="7" r="1.4"/><circle cx="15" cy="7" r="1.4"/><circle cx="9" cy="12" r="1.4"/><circle cx="15" cy="12" r="1.4"/><circle cx="9" cy="17" r="1.4"/><circle cx="15" cy="17" r="1.4"/>',
    play:       '<path d="M7 4.5 19 12 7 19.5Z"/>'
  };

  ED.icon = function (name, size, cls) {
    var d = P[name] || P.info, s = size || 20;
    return '<svg class="' + (cls || '') + '" width="' + s + '" height="' + s +
      '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" ' +
      'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + d + '</svg>';
  };
  function ico(n, s) { return ED.icon(n, s); }

  /* ------------------------------------------------------------------ *
   * 2. APP SHELL
   * ------------------------------------------------------------------ */
  var NAV = [
    { key: 'dashboard', label: 'Dashboard', href: 'dashboard.html' },
    { key: 'events',    label: 'Events',    href: 'dashboard.html#events' },
    { key: 'analytics', label: 'Analytics', href: 'analytics.html' },
    { key: 'reports',   label: 'Reports',   href: 'export-reports.html' }
  ];

  var EVENT_TABS = [
    { key: 'overview',   label: 'Overview',   href: 'event-details.html' },
    { key: 'attendees',  label: 'Attendees',  href: 'manage-attendees.html' },
    { key: 'attendance', label: 'Attendance', href: 'attendance-view.html' },
    { key: 'analytics',  label: 'Analytics',  href: 'analytics.html' },
    { key: 'reports',    label: 'Reports',    href: 'export-reports.html' },
    { key: 'settings',   label: 'Settings',   href: 'edit-event.html' }
  ];

  function renderTopbar(host) {
    var active = document.body.getAttribute('data-nav') || '';
    var links = NAV.map(function (n) {
      return '<a class="nav__link' + (n.key === active ? ' is-active' : '') + '" href="' + n.href + '">' + n.label + '</a>';
    }).join('');

    host.innerHTML =
      '<div class="topbar__inner">' +
        '<a class="brand" href="dashboard.html">Event<span>DNA</span></a>' +
        '<nav class="nav" aria-label="Organizer sections">' + links + '</nav>' +
        '<div class="topbar__right">' +
          '<a class="topbar__link" href="dashboard.html">Hanan</a>' +
          '<a class="btn btn--primary btn--sm" href="create-event.html">Create event</a>' +
          '<span class="avatar" title="Hanan Mohamed · Organizer">HM</span>' +
        '</div>' +
      '</div>';
  }

  /* Dark gradient hero + centred tab strip for event-scoped pages */
  function renderEventHero(host) {
    var active = host.getAttribute('data-eventhero');
    var ev = window.ED_EVENT || {};

    var tabs = EVENT_TABS.map(function (t) {
      return '<a class="nav__link' + (t.key === active ? ' is-active' : '') + '" href="' + t.href + '">' + t.label + '</a>';
    }).join('');

    host.innerHTML =
      '<section class="hero">' +
        '<div class="hero__inner">' +
          '<div class="hero__crumb"><a href="dashboard.html">Dashboard</a> &nbsp;›&nbsp; <a href="dashboard.html#events">Events</a> &nbsp;›&nbsp; ' + (ev.title || '') + '</div>' +
          '<span class="hero__chip">' + (ev.category || 'Event') + '</span>' +
          '<h1>' + (ev.title || 'Event') + '</h1>' +
          '<p class="hero__lead">' + (ev.tagline || '') + '</p>' +
          '<div class="hero__actions">' +
            '<a class="btn btn--white" href="attendance-view.html">Open check-in desk</a>' +
            '<a class="btn btn--outline-white" href="edit-event.html">Edit event</a>' +
          '</div>' +
        '</div>' +
      '</section>' +
      '<div class="shell">' +
        '<div class="factbar">' +
          fact('calendar', 'Date',     ev.dates || '') +
          fact('clock',    'Time',     ev.time || '') +
          fact('pin',      'Location', ev.venue || '') +
          fact('users',    'Capacity', (ev.registered || 0) + ' / ' + (ev.capacity || 0) + ' registered') +
        '</div>' +
      '</div>' +
      '<div class="shell" style="padding-top:36px">' +
        '<nav class="nav" style="justify-content:center;flex-wrap:wrap" aria-label="Event sections">' + tabs + '</nav>' +
      '</div>';
  }

  function fact(icon, label, value) {
    return '<div class="fact"><span class="tile tile--sm">' + ico(icon, 20) + '</span>' +
      '<div><div class="fact__label">' + label + '</div><div class="fact__value">' + value + '</div></div></div>';
  }

  /* ------------------------------------------------------------------ *
   * 3. INTERACTIONS
   * ------------------------------------------------------------------ */
  ED.toast = function (msg, icon) {
    var host = document.querySelector('.toast-host');
    if (!host) { host = document.createElement('div'); host.className = 'toast-host'; document.body.appendChild(host); }
    var el = document.createElement('div');
    el.className = 'toast';
    el.innerHTML = ico(icon || 'checkcirc', 20) + '<span>' + msg + '</span>';
    host.appendChild(el);
    setTimeout(function () { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; }, 2500);
    setTimeout(function () { el.remove(); }, 2900);
  };

  function initToastButtons() {
    document.addEventListener('click', function (e) {
      var b = e.target.closest('[data-toast]');
      if (b) { e.preventDefault(); ED.toast(b.getAttribute('data-toast'), b.getAttribute('data-toast-icon')); }
    });
  }

  function initModals() {
    document.addEventListener('click', function (e) {
      var open = e.target.closest('[data-modal-open]');
      if (open) { e.preventDefault(); var m = document.getElementById(open.getAttribute('data-modal-open')); if (m) m.hidden = false; return; }
      var close = e.target.closest('[data-modal-close]');
      if (close) { e.preventDefault(); var s = close.closest('.modal-scrim'); if (s) s.hidden = true; return; }
      if (e.target.classList && e.target.classList.contains('modal-scrim')) e.target.hidden = true;
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') document.querySelectorAll('.modal-scrim').forEach(function (s) { s.hidden = true; });
    });
  }

  /* Segmented controls — [data-segment] with buttons carrying data-value */
  function initSegments() {
    document.querySelectorAll('[data-segment]').forEach(function (seg) {
      seg.addEventListener('click', function (e) {
        var b = e.target.closest('button');
        if (!b) return;
        seg.querySelectorAll('button').forEach(function (x) { x.classList.remove('is-active'); });
        b.classList.add('is-active');
        var target = seg.getAttribute('data-segment-target');
        if (target) {
          document.querySelectorAll('[data-segment-panel="' + target + '"]').forEach(function (p) {
            p.hidden = p.getAttribute('data-value') !== b.getAttribute('data-value');
          });
        }
      });
    });
  }

  function initOptionCards() {
    document.querySelectorAll('[data-optiongroup]').forEach(function (group) {
      function sync() {
        group.querySelectorAll('.optioncard').forEach(function (c) {
          var r = c.querySelector('input');
          c.classList.toggle('is-selected', !!(r && r.checked));
        });
      }
      group.addEventListener('change', sync); sync();
    });
    document.querySelectorAll('[data-featuregroup]').forEach(function (group) {
      group.addEventListener('click', function (e) {
        var card = e.target.closest('.feature');
        if (!card) return;
        group.querySelectorAll('.feature').forEach(function (c) { c.classList.remove('feature--sel'); });
        card.classList.add('feature--sel');
      });
    });
  }

  /* Tables: search, filter, sort, selection */
  function initTables() {
    document.querySelectorAll('[data-table-search]').forEach(function (input) {
      var table = document.getElementById(input.getAttribute('data-table-search'));
      if (!table) return;
      input.addEventListener('input', function () {
        var q = input.value.toLowerCase().trim(), shown = 0;
        table.querySelectorAll('tbody tr').forEach(function (tr) {
          var hit = tr.textContent.toLowerCase().indexOf(q) > -1;
          tr.hidden = !hit; if (hit) shown++;
        });
        var out = document.querySelector('[data-table-count="' + table.id + '"]');
        if (out) out.textContent = shown;
      });
    });

    document.querySelectorAll('[data-table-filter]').forEach(function (sel) {
      var table = document.getElementById(sel.getAttribute('data-table-filter'));
      if (!table) return;
      sel.addEventListener('change', function () {
        var v = sel.value, key = sel.getAttribute('data-filter-key') || 'status', shown = 0;
        table.querySelectorAll('tbody tr').forEach(function (tr) {
          var hit = (v === 'all') || tr.getAttribute('data-' + key) === v;
          tr.hidden = !hit; if (hit) shown++;
        });
        var out = document.querySelector('[data-table-count="' + table.id + '"]');
        if (out) out.textContent = shown;
      });
    });

    document.querySelectorAll('table[data-sortable] th.th-sort').forEach(function (th) {
      th.addEventListener('click', function () {
        var table = th.closest('table');
        var idx = Array.prototype.indexOf.call(th.parentElement.children, th);
        var asc = th.getAttribute('data-dir') !== 'asc';
        table.querySelectorAll('th.th-sort').forEach(function (o) { o.removeAttribute('data-dir'); });
        th.setAttribute('data-dir', asc ? 'asc' : 'desc');
        var body = table.querySelector('tbody');
        var rows = Array.prototype.slice.call(body.querySelectorAll('tr'));
        rows.sort(function (a, b) {
          var x = cellVal(a, idx), y = cellVal(b, idx);
          if (typeof x === 'number' && typeof y === 'number') return asc ? x - y : y - x;
          return asc ? String(x).localeCompare(String(y)) : String(y).localeCompare(String(x));
        });
        rows.forEach(function (r) { body.appendChild(r); });
      });
    });
    function cellVal(tr, i) {
      var td = tr.children[i];
      if (!td) return '';
      var raw = td.getAttribute('data-sort') || td.textContent.trim();
      var num = parseFloat(String(raw).replace(/[^0-9.\-]/g, ''));
      return (raw !== '' && !isNaN(num) && /^[^a-zA-Z]*$/.test(String(raw).replace(/[%,\s]/g, ''))) ? num : raw.toLowerCase();
    }

    document.querySelectorAll('[data-select-all]').forEach(function (master) {
      var table = document.getElementById(master.getAttribute('data-select-all'));
      if (!table) return;
      var bar = document.querySelector('[data-bulkbar="' + table.id + '"]');
      function refresh() {
        var boxes = table.querySelectorAll('tbody input[type=checkbox]');
        var n = 0; boxes.forEach(function (b) { if (b.checked) n++; });
        if (bar) {
          bar.hidden = n === 0;
          var lbl = bar.querySelector('[data-bulk-count]');
          if (lbl) lbl.textContent = n;
        }
        master.indeterminate = n > 0 && n < boxes.length;
        master.checked = n > 0 && n === boxes.length;
      }
      master.addEventListener('change', function () {
        table.querySelectorAll('tbody input[type=checkbox]').forEach(function (b) { b.checked = master.checked; });
        refresh();
      });
      table.addEventListener('change', function (e) { if (e.target.type === 'checkbox') refresh(); });
      refresh();
    });
  }

  /* Charts + counters animate from data-* attributes */
  function initCharts() {
    document.querySelectorAll('[data-bar-h]').forEach(function (el) {
      el.style.height = '0%';
      requestAnimationFrame(function () {
        el.style.transition = 'height .7s cubic-bezier(.2,.8,.2,1)';
        el.style.height = el.getAttribute('data-bar-h') + '%';
      });
    });
    document.querySelectorAll('[data-fill-w]').forEach(function (el) {
      el.style.width = '0%';
      requestAnimationFrame(function () { el.style.width = el.getAttribute('data-fill-w') + '%'; });
    });
    document.querySelectorAll('[data-ring]').forEach(function (el) {
      var pct = parseFloat(el.getAttribute('data-ring'));
      var r = parseFloat(el.getAttribute('r'));
      var c = 2 * Math.PI * r;
      el.setAttribute('stroke-dasharray', c.toFixed(1));
      el.setAttribute('stroke-dashoffset', c.toFixed(1));
      requestAnimationFrame(function () { el.setAttribute('stroke-dashoffset', (c * (1 - pct / 100)).toFixed(1)); });
    });
  }

  function initCounters() {
    document.querySelectorAll('[data-count]').forEach(function (el) {
      var end = parseFloat(el.getAttribute('data-count'));
      var suffix = el.getAttribute('data-count-suffix') || '';
      var dec = parseInt(el.getAttribute('data-count-dec') || '0', 10);
      var t0 = null, dur = 800;
      function step(ts) {
        if (!t0) t0 = ts;
        var p = Math.min(1, (ts - t0) / dur);
        var v = end * (1 - Math.pow(1 - p, 3));
        el.textContent = (dec ? v.toFixed(dec) : Math.round(v).toLocaleString()) + suffix;
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    });
  }

  /* Real client-side CSV export */
  ED.exportCSV = function (tableId, filename) {
    var table = document.getElementById(tableId);
    if (!table) return;
    var rows = [];
    table.querySelectorAll('tr').forEach(function (tr) {
      if (tr.hidden) return;
      var cells = [];
      tr.querySelectorAll('th,td').forEach(function (c) {
        if (c.hasAttribute('data-csv-skip')) return;
        var t = (c.getAttribute('data-csv') || c.textContent).replace(/\s+/g, ' ').trim();
        cells.push('"' + t.replace(/"/g, '""') + '"');
      });
      if (cells.length) rows.push(cells.join(','));
    });
    var blob = new Blob(['﻿' + rows.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = filename || 'eventdna-export.csv';
    document.body.appendChild(a); a.click(); a.remove();
    ED.toast('CSV downloaded', 'download');
  };

  function initExport() {
    document.addEventListener('click', function (e) {
      var b = e.target.closest('[data-export-csv]');
      if (b) { e.preventDefault(); ED.exportCSV(b.getAttribute('data-export-csv'), b.getAttribute('data-filename')); }
      var p = e.target.closest('[data-print]');
      if (p) { e.preventDefault(); window.print(); }
    });
  }

  function initCharCount() {
    document.querySelectorAll('[data-charcount]').forEach(function (el) {
      var out = document.querySelector('[data-charcount-for="' + el.id + '"]');
      if (!out) return;
      var max = el.getAttribute('maxlength') || 500;
      function sync() { out.textContent = el.value.length + ' / ' + max; }
      el.addEventListener('input', sync); sync();
    });
  }

  /* ------------------------------------------------------------------ *
   * 4. BOOT
   * ------------------------------------------------------------------ */
  function boot() {
    document.querySelectorAll('[data-topbar]').forEach(renderTopbar);
    document.querySelectorAll('[data-eventhero]').forEach(renderEventHero);
    document.querySelectorAll('[data-icon]').forEach(function (el) {
      el.innerHTML = ico(el.getAttribute('data-icon'), parseInt(el.getAttribute('data-icon-size') || '20', 10));
    });
    initToastButtons(); initModals(); initSegments(); initOptionCards();
    initTables(); initCharts(); initCounters(); initExport(); initCharCount();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
  else boot();
})();
