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
        autoLinkInstagram();
    }

    function autoLinkInstagram() {
        const keyword = "Instagram";
        const link = "https://instagram.com";
        // Avoid linking inside these tags to prevent breaking layout or nested links
        const forbiddenParents = new Set(['A', 'BUTTON', 'INPUT', 'TEXTAREA', 'SELECT', 'SCRIPT', 'STYLE', 'CODE', 'PRE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6']);
        
        const walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function(node) {
                    // Skip if empty or doesn't contain keyword
                    if (!node.nodeValue || !node.nodeValue.includes(keyword)) {
                        return NodeFilter.FILTER_REJECT;
                    }
                    
                    // Check ancestors
                    let parent = node.parentNode;
                    while (parent && parent !== document.body) {
                        if (forbiddenParents.has(parent.tagName)) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        // Skip if already linked or explicitly excluded
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