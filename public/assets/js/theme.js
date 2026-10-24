
(function() {
    'use strict';

    var THEME_STORAGE_KEY = 'nexus_theme_preference';
    var LEGACY_STORAGE_KEY = 'smcc_theme_preference';
    var THEME_DARK = 'dark';
    var THEME_LIGHT = 'light';

    function getStoredTheme() {
        try {
            var stored = localStorage.getItem(THEME_STORAGE_KEY) || localStorage.getItem(LEGACY_STORAGE_KEY);
            if (stored === THEME_DARK || stored === THEME_LIGHT) {
                return stored;
            }
        } catch (e) {
            console.warn('localStorage read error:', e);
        }

        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return THEME_DARK;
        }

        return THEME_LIGHT;
    }

    function applyTheme(theme) {
        var root = document.documentElement;
        if (theme === THEME_DARK) {
            root.setAttribute('data-bs-theme', 'dark');
            root.classList.add('dark-theme');
            root.classList.remove('light-theme');
        } else {
            root.setAttribute('data-bs-theme', 'light');
            root.classList.remove('dark-theme');
            root.classList.add('light-theme');
        }

        updateToggleButtons(theme);
    }

    function updateToggleButtons(activeTheme) {
        var current = activeTheme || (document.documentElement.getAttribute('data-bs-theme') === THEME_DARK ? THEME_DARK : THEME_LIGHT);
        var buttons = document.querySelectorAll('.theme-toggle-btn');

        buttons.forEach(function(btn) {
            var icon = btn.querySelector('.theme-toggle-icon');
            var moonSvg = btn.querySelector('.theme-icon-moon');
            var sunSvg = btn.querySelector('.theme-icon-sun');

            if (current === THEME_DARK) {
                if (icon) icon.className = 'theme-toggle-icon bi bi-sun-fill';
                if (moonSvg) moonSvg.classList.add('d-none');
                if (sunSvg) sunSvg.classList.remove('d-none');
                btn.setAttribute('title', 'Switch to Light Mode');
                btn.setAttribute('aria-label', 'Switch to Light Mode');
            } else {
                if (icon) icon.className = 'theme-toggle-icon bi bi-moon-fill';
                if (moonSvg) moonSvg.classList.remove('d-none');
                if (sunSvg) sunSvg.classList.add('d-none');
                btn.setAttribute('title', 'Switch to Dark Mode');
                btn.setAttribute('aria-label', 'Switch to Dark Mode');
            }
        });

        var switches = document.querySelectorAll('.theme-toggle-switch, #themeSwitchCheckbox');
        switches.forEach(function(sw) {
            sw.checked = (current === THEME_DARK);
        });

        var menuMoon = document.querySelectorAll('.fb-menu-theme-item .theme-icon-moon');
        var menuSun = document.querySelectorAll('.fb-menu-theme-item .theme-icon-sun');
        if (current === THEME_DARK) {
            menuMoon.forEach(function(el) { el.classList.add('d-none'); });
            menuSun.forEach(function(el) { el.classList.remove('d-none'); });
        } else {
            menuMoon.forEach(function(el) { el.classList.remove('d-none'); });
            menuSun.forEach(function(el) { el.classList.add('d-none'); });
        }
    }

    window.toggleTheme = function() {
        var currentTheme = document.documentElement.getAttribute('data-bs-theme') === THEME_DARK ? THEME_DARK : THEME_LIGHT;
        var newTheme = (currentTheme === THEME_DARK) ? THEME_LIGHT : THEME_DARK;

        try {
            localStorage.setItem(THEME_STORAGE_KEY, newTheme);
        } catch (e) {
            console.warn('localStorage write error:', e);
        }

        applyTheme(newTheme);
    };

    window.updateThemeToggleButtons = updateToggleButtons;

    var initialTheme = getStoredTheme();
    applyTheme(initialTheme);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            updateToggleButtons();
        });
    } else {
        updateToggleButtons();
    }
})();
