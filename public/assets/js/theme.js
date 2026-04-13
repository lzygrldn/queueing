// Theme Switcher Logic
(function() {
    'use strict';

    // Check for saved theme preference or default to light
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);

    // Toggle theme function
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        updateToggleIcon(newTheme);
    }

    // Update toggle button icon
    function updateToggleIcon(theme) {
        const toggle = document.querySelector('.theme-toggle');
        if (toggle) {
            toggle.innerHTML = theme === 'light' ? '<i class="bi bi-moon-fill"></i>' : '<i class="bi bi-sun-fill"></i>';
            toggle.title = theme === 'light' ? 'Switch to Dark Mode' : 'Switch to Light Mode';
        }
    }

    // Initialize toggle icon
    updateToggleIcon(currentTheme);

    // Add click event to toggle button
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.querySelector('.theme-toggle');
        if (toggle) {
            toggle.addEventListener('click', toggleTheme);
        }
    });

    // Expose to global scope if needed
    window.ThemeSwitcher = {
        toggle: toggleTheme,
        getCurrent: function() {
            return document.documentElement.getAttribute('data-theme');
        }
    };
})();
