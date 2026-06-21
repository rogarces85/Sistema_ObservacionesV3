/* Sistema de Observaciones REM - theme.js
 * Interacciones de shell (sidebar, theme, search, year dropdown, FAB)
 * compatibles con Tabler 1.4. No muta datos REM.
 */
(function () {
  'use strict';

  const html = document.documentElement;
  const body = document.body;
  const THEME_KEY = 'rem.theme';
  const SIDEBAR_KEY = 'rem.sidebar';
  const THEME_MAX_AGE = 60 * 60 * 24 * 365;

  function isValidTheme(theme) {
    return theme === 'light' || theme === 'dark';
  }

  function getCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.$?*|{}()[\]\\/+^]/g, '\\$&') + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
  }

  function setThemeCookie(theme) {
    document.cookie = THEME_KEY + '=' + encodeURIComponent(theme) + '; path=/; max-age=' + THEME_MAX_AGE + '; samesite=lax';
  }

  function getStoredTheme() {
    const cookieTheme = getCookie(THEME_KEY);
    if (isValidTheme(cookieTheme)) return cookieTheme;

    try {
      const localTheme = localStorage.getItem(THEME_KEY);
      if (isValidTheme(localTheme)) return localTheme;
    } catch (e) { /* localStorage may be unavailable */ }

    return 'light';
  }

  /* === Theme toggle (light/dark) === */
  function applyTheme(theme) {
    if (!isValidTheme(theme)) theme = 'light';
    const previousTheme = html.getAttribute('data-bs-theme') || 'light';
    html.setAttribute('data-bs-theme', theme);
    setThemeCookie(theme);
    try {
      localStorage.setItem(THEME_KEY, theme);
    } catch (e) { /* localStorage may be unavailable */ }
    const icon = document.querySelector('#themeToggle i');
    if (icon) {
      icon.className = theme === 'dark' ? 'ti ti-sun' : 'ti ti-moon';
    }
    if (previousTheme !== theme) {
      window.dispatchEvent(new CustomEvent('rem:theme-changed', { detail: { theme } }));
    }
  }

  function initTheme() {
    applyTheme(getStoredTheme());

    const toggle = document.getElementById('themeToggle');
    if (toggle) {
      toggle.addEventListener('click', function () {
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(next);
      });
    }

    const fab = document.getElementById('themeToggleFab');
    if (fab) {
      fab.addEventListener('click', function () {
        const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        applyTheme(next);
      });
    }
  }

  /* === Sidebar mini-variant (desktop) === */
  function applySidebar(state) {
    const page = document.querySelector('.page');
    if (!page) return;
    if (state === 'mini') {
      page.setAttribute('data-sidebar', 'mini');
    } else {
      page.setAttribute('data-sidebar', 'full');
    }
    try {
      localStorage.setItem(SIDEBAR_KEY, state);
    } catch (e) { /* ignore */ }
  }

  function initSidebar() {
    let stored = null;
    try {
      stored = localStorage.getItem(SIDEBAR_KEY);
    } catch (e) { /* ignore */ }
    if (stored === 'mini' || stored === 'full') {
      applySidebar(stored);
    }

    // Mobile toggle
    const mobileToggle = document.querySelector('.sidebar-toggle');
    if (mobileToggle) {
      mobileToggle.addEventListener('click', function () {
        body.classList.toggle('sidebar-mobile-open');
      });
    }

    // Hover to mini-variant on desktop
    const sidebar = document.getElementById('sidebarMain');
    if (sidebar && window.matchMedia('(min-width: 992px)').matches) {
      let leaveTimer = null;
      sidebar.addEventListener('mouseenter', function () {
        if (leaveTimer) clearTimeout(leaveTimer);
        const current = document.querySelector('.page')?.getAttribute('data-sidebar');
        if (current === 'mini') {
          sidebar.style.transition = 'width var(--rem-transition-base)';
          sidebar.style.width = 'var(--tblr-sidebar-width)';
        }
      });
      sidebar.addEventListener('mouseleave', function () {
        leaveTimer = setTimeout(function () {
          const current = document.querySelector('.page')?.getAttribute('data-sidebar');
          if (current === 'mini' && sidebar) {
            sidebar.style.width = '';
          }
        }, 200);
      });
    }
  }

  /* === Global search filter === */
  function initGlobalSearch() {
    const input = document.getElementById('globalSearchInput');
    const results = document.getElementById('globalSearchResults');
    if (!input || !results) return;

    const items = Array.from(results.querySelectorAll('.search-result-item'));
    input.addEventListener('input', function () {
      const q = input.value.toLowerCase().trim();
      items.forEach(function (item) {
        const text = item.textContent.toLowerCase();
        item.style.display = (q === '' || text.indexOf(q) !== -1) ? '' : 'none';
      });
    });
  }

  /* === Year dropdown === */
  // Exposed globally so header.php inline onclick works without jQuery.
  window.changeYearViaDropdown = function (el, event) {
    event.preventDefault();
    const year = el.getAttribute('data-year');
    if (!year) return false;
    if (typeof window.changeYear === 'function') {
      return window.changeYear(year);
    }
    window.location.href = '?page=' + (new URLSearchParams(window.location.search).get('page') || 'dashboard') + '&year=' + encodeURIComponent(year);
    return false;
  };

  /* === Mobile body class for sidebar overlay === */
  function initMobileOverlay() {
    document.addEventListener('click', function (event) {
      const toggle = event.target.closest('.sidebar-toggle');
      if (toggle) {
        body.classList.toggle('sidebar-mobile-open');
        const page = document.querySelector('.page');
        if (page) {
          page.setAttribute('data-sidebar', body.classList.contains('sidebar-mobile-open') ? 'mobile-open' : 'full');
        }
      } else if (!event.target.closest('.navbar-vertical') && !event.target.closest('.sidebar-toggle')) {
        if (body.classList.contains('sidebar-mobile-open')) {
          body.classList.remove('sidebar-mobile-open');
          const page = document.querySelector('.page');
          if (page) page.setAttribute('data-sidebar', 'full');
        }
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initTheme();
    initSidebar();
    initGlobalSearch();
    initMobileOverlay();
    initScrollFab();
  });

  function initScrollFab() {
    const fab = document.getElementById('themeToggleFab');
    if (!fab) return;
    const threshold = 240;
    let lastShown = false;
    function update() {
      const shouldShow = window.scrollY > threshold;
      if (shouldShow !== lastShown) {
        fab.classList.toggle('is-visible', shouldShow);
        lastShown = shouldShow;
      }
    }
    window.addEventListener('scroll', update, { passive: true });
    update();
  }
})();
