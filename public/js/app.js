(function () {
    'use strict';

    // Theme toggle functionality
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

        // Update sidebar background after theme change
        setTimeout(() => {
            updateSidebarBackground();
            document.documentElement.classList.remove('theme-transition');
        }, 50);
    }

    // Update sidebar background based on theme
    function updateSidebarBackground() {
        const sidebar = document.getElementById('mobileMenuSidebar');
        if (sidebar) {
            const isDark = document.documentElement.classList.contains('dark');
            sidebar.style.backgroundColor = isDark ? '#111827' : '#ffffff';
        }
    }

    // Mobile menu open function
    function openMobileMenu() {
        const mobileMenu = document.getElementById('mobileMenu');
        const overlay = document.getElementById('mobileMenuOverlay');
        const sidebar = document.getElementById('mobileMenuSidebar');

        // Update background color based on current theme
        updateSidebarBackground();

        if (mobileMenu) {
            mobileMenu.classList.remove('pointer-events-none');
            mobileMenu.classList.add('pointer-events-auto');
        }

        if (overlay) {
            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100', 'pointer-events-auto');
        }

        if (sidebar) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
        }

        document.body.style.overflow = 'hidden';
    }

    // Mobile menu close function
    function closeMobileMenu() {
        const mobileMenu = document.getElementById('mobileMenu');
        const overlay = document.getElementById('mobileMenuOverlay');
        const sidebar = document.getElementById('mobileMenuSidebar');

        if (sidebar) {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
        }

        if (overlay) {
            overlay.classList.add('opacity-0', 'pointer-events-none');
            overlay.classList.remove('opacity-100', 'pointer-events-auto');
        }

        // Delay hiding the container to allow animation to complete
        setTimeout(function () {
            if (mobileMenu) {
                mobileMenu.classList.add('pointer-events-none');
                mobileMenu.classList.remove('pointer-events-auto');
            }
        }, 300);

        document.body.style.overflow = '';
    }

    // Initialize all functionality
    function init() {
        // Set initial sidebar background color
        updateSidebarBackground();

        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        // Mobile menu
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenuClose = document.getElementById('mobileMenuClose');
        const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openMobileMenu();
            });
        }

        if (mobileMenuClose) {
            mobileMenuClose.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeMobileMenu();
            });
        }

        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', function (e) {
                e.preventDefault();
                closeMobileMenu();
            });
        }

        // Close menu on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
            }
        });

        // Initialize scroll to top button
        initScrollToTop();

        // Auto link Instagram text
        autoLinkInstagram();
    }

    // Auto-link Instagram text
    function autoLinkInstagram() {
        const keyword = "Instagram";
        const link = "https://instagram.com";
        const forbiddenParents = new Set(['A', 'BUTTON', 'INPUT', 'TEXTAREA', 'SELECT', 'SCRIPT', 'STYLE', 'CODE', 'PRE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6']);

        const walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function (node) {
                    if (!node.nodeValue || !node.nodeValue.includes(keyword)) {
                        return NodeFilter.FILTER_REJECT;
                    }

                    let parent = node.parentNode;
                    while (parent && parent !== document.body) {
                        if (forbiddenParents.has(parent.tagName)) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        if (parent.classList && (parent.classList.contains('no-autolink') || parent.closest('a'))) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        parent = parent.parentNode;
                    }

                    return NodeFilter.FILTER_ACCEPT;
                }
            },
            false
        );

        const nodes = [];
        let currentNode;
        while (currentNode = walker.nextNode()) {
            nodes.push(currentNode);
        }

        nodes.forEach(node => {
            const fragment = document.createDocumentFragment();
            const parts = node.nodeValue.split(keyword);

            parts.forEach((part, index) => {
                if (part) {
                    fragment.appendChild(document.createTextNode(part));
                }

                if (index < parts.length - 1) {
                    const a = document.createElement('a');
                    a.href = link;
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                    a.textContent = keyword;
                    a.className = 'hover:underline no-autolink inline-block';
                    fragment.appendChild(a);
                }
            });

            if (node.parentNode) {
                node.parentNode.replaceChild(fragment, node);
            }
        });
    }

    // Scroll to top button functionality
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
                window.requestAnimationFrame(function () {
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

            setTimeout(function () {
                scrollToTopBtn.classList.remove('scroll-top-ripple');
            }, 300);
        }

        window.addEventListener('scroll', handleScroll, { passive: true });
        scrollToTopBtn.addEventListener('click', scrollToTop);

        // Initial check
        toggleScrollButton();
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();