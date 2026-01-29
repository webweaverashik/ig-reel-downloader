// Sidebar Toggle
function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebarOverlay');
      sidebar.classList.toggle('open');
      overlay.classList.toggle('hidden');
      document.body.classList.toggle('overflow-hidden');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('open')) {
                  toggleSidebar();
            }
      }
});

// Theme Toggle
function toggleAdminTheme() {
      document.documentElement.classList.add('theme-transition');

      const isDark = document.documentElement.classList.contains('dark');

      if (isDark) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('admin_theme', 'light');
      } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('admin_theme', 'dark');
      }

      setTimeout(() => {
            document.documentElement.classList.remove('theme-transition');
      }, 300);
}

// Initialize theme toggle button
document.addEventListener('DOMContentLoaded', function () {
      const themeToggle = document.getElementById('adminThemeToggle');
      if (themeToggle) {
            themeToggle.addEventListener('click', toggleAdminTheme);
      }
});