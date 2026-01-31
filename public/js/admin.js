// Sidebar Toggle - Made globally accessible
window.toggleSidebar = function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebar) {
        sidebar.classList.toggle('open');
    }

    if (overlay) {
        overlay.classList.toggle('hidden');
    }

    // Toggle body scroll
    if (sidebar && sidebar.classList.contains('open')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
};

// Close sidebar when clicking outside on mobile
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('open')) {
            window.toggleSidebar();
        }
    }
});

// Theme Toggle - Made globally accessible
window.toggleAdminTheme = function () {
    document.documentElement.classList.add('theme-transition');

    const isDark = document.documentElement.classList.contains('dark');

    if (isDark) {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('admin_theme', 'light');
    } else {
        document.documentElement.classList.add('dark');
        localStorage.setItem('admin_theme', 'dark');
    }

    setTimeout(function () {
        document.documentElement.classList.remove('theme-transition');
    }, 300);
};

// Initialize theme toggle button
document.addEventListener('DOMContentLoaded', function () {
    const themeToggle = document.getElementById('adminThemeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', window.toggleAdminTheme);
    }
});

// Handle window resize - close sidebar on desktop
window.addEventListener('resize', function () {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (window.innerWidth >= 1024) {
        // On desktop, reset sidebar and overlay
        if (sidebar) {
            sidebar.classList.remove('open');
        }
        if (overlay) {
            overlay.classList.add('hidden');
        }
        document.body.style.overflow = '';
    }
});