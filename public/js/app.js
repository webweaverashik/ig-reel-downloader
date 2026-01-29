(function() {
    'use strict';

    function toggleTheme() {
        document.documentElement.classList.add('theme-transition');

        const isDark = document.documentElement.classList.contains('dark');

        if (isDark) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }

        setTimeout(() => {
            document.documentElement.classList.remove('theme-transition');
        }, 300);
    }

    function openMobileMenu() {
        document.getElementById('mobileMenu').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        document.getElementById('mobileMenu').classList.remove('open');
        document.body.style.overflow = '';
    }

    function init() {
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenuClose = document.getElementById('mobileMenuClose');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

        if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMobileMenu);
        if (mobileMenuClose) mobileMenuClose.addEventListener('click', closeMobileMenu);
        if (mobileMenuOverlay) mobileMenuOverlay.addEventListener('click', closeMobileMenu);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });

        initScrollToTop();
    }

    function initScrollToTop() {
        const scrollToTopBtn = document.getElementById('scrollToTop');
        if (!scrollToTopBtn) return;

        let isScrolling = false;

        function toggleScrollButton() {
            const scrollY = window.scrollY || window.pageYOffset;
            const showThreshold = 300;

            if (scrollY > showThreshold) {
                scrollToTopBtn.classList.add('scroll-top-visible');
            } else {
                scrollToTopBtn.classList.remove('scroll-top-visible');
            }
        }

        function handleScroll() {
            if (!isScrolling) {
                window.requestAnimationFrame(function() {
                    toggleScrollButton();
                    isScrolling = false;
                });
                isScrolling = true;
            }
        }

        function scrollToTop() {
            scrollToTopBtn.classList.add('scroll-top-ripple');
            
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            setTimeout(function() {
                scrollToTopBtn.classList.remove('scroll-top-ripple');
            }, 300);
        }

        window.addEventListener('scroll', handleScroll, { passive: true });
        scrollToTopBtn.addEventListener('click', scrollToTop);

        toggleScrollButton();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();