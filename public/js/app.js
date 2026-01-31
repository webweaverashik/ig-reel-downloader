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

        // Auto link Instagram text (only one random occurrence)
        autoLinkInstagram();
    }

    /**
     * Auto-link Instagram text
     * 
     * This function:
     * 1. Scans only visible text content (excluding scripts, styles, links, inputs, etc.)
     * 2. Randomly selects ONE occurrence of "Instagram" (case-insensitive)
     * 3. Converts only that selected occurrence into a clickable link
     * 4. Leaves all other occurrences unchanged
     * 5. Prevents duplicate linking if run multiple times
     */
    function autoLinkInstagram() {
        // Check if already executed to prevent duplicate linking
        if (document.body.hasAttribute('data-instagram-linked')) {
            return;
        }

        const keyword = /instagram/i;
        const link = "https://www.instagram.com/";

        // Tags to exclude from processing
        const forbiddenTags = new Set([
            'A', 'BUTTON', 'INPUT', 'TEXTAREA', 'SELECT',
            'SCRIPT', 'STYLE', 'CODE', 'PRE', 'NOSCRIPT',
            'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'SVG', 'IFRAME'
        ]);

        // Collect all valid text nodes containing "Instagram"
        const validTextNodes = [];

        const walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: function (node) {
                    // Skip empty or whitespace-only nodes
                    if (!node.nodeValue || !node.nodeValue.trim()) {
                        return NodeFilter.FILTER_REJECT;
                    }

                    // Check if text contains "Instagram" (case-insensitive)
                    if (!keyword.test(node.nodeValue)) {
                        return NodeFilter.FILTER_REJECT;
                    }

                    // Check if node is visible
                    let element = node.parentElement;
                    if (!element) {
                        return NodeFilter.FILTER_REJECT;
                    }

                    // Check if element or any parent is hidden
                    const style = window.getComputedStyle(element);
                    if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
                        return NodeFilter.FILTER_REJECT;
                    }

                    // Traverse up to check for forbidden parents
                    let parent = element;
                    while (parent && parent !== document.body) {
                        // Check forbidden tags
                        if (forbiddenTags.has(parent.tagName)) {
                            return NodeFilter.FILTER_REJECT;
                        }

                        // Check if already inside a link
                        if (parent.tagName === 'A' || parent.closest('a')) {
                            return NodeFilter.FILTER_REJECT;
                        }

                        // Check for no-autolink class
                        if (parent.classList && parent.classList.contains('no-autolink')) {
                            return NodeFilter.FILTER_REJECT;
                        }

                        // Check if already linked
                        if (parent.hasAttribute && parent.hasAttribute('data-instagram-autolinked')) {
                            return NodeFilter.FILTER_REJECT;
                        }

                        parent = parent.parentNode;
                    }

                    return NodeFilter.FILTER_ACCEPT;
                }
            }
        );

        // Collect all valid nodes
        let currentNode;
        while ((currentNode = walker.nextNode())) {
            // Find all occurrences of "Instagram" in this text node
            const text = currentNode.nodeValue;
            const regex = /instagram/gi;
            let match;

            while ((match = regex.exec(text)) !== null) {
                validTextNodes.push({
                    node: currentNode,
                    index: match.index,
                    matchedText: match[0]
                });
            }
        }

        // If no valid occurrences found, exit
        if (validTextNodes.length === 0) {
            document.body.setAttribute('data-instagram-linked', 'true');
            return;
        }

        // Randomly select one occurrence
        const randomIndex = Math.floor(Math.random() * validTextNodes.length);
        const selected = validTextNodes[randomIndex];

        // Process only the selected text node
        const node = selected.node;
        const text = node.nodeValue;
        const matchIndex = selected.index;
        const matchedText = selected.matchedText;

        // Create document fragment with the linked text
        const fragment = document.createDocumentFragment();

        // Text before the match
        if (matchIndex > 0) {
            fragment.appendChild(document.createTextNode(text.substring(0, matchIndex)));
        }

        // The linked "Instagram" text
        const anchor = document.createElement('a');
        anchor.href = link;
        anchor.target = '_blank';
        anchor.rel = 'noopener noreferrer';
        anchor.textContent = matchedText;
        anchor.className = 'text-violet-600 dark:text-violet-400 hover:underline no-autolink';
        anchor.setAttribute('data-instagram-autolinked', 'true');
        fragment.appendChild(anchor);

        // Text after the match
        const afterIndex = matchIndex + matchedText.length;
        if (afterIndex < text.length) {
            fragment.appendChild(document.createTextNode(text.substring(afterIndex)));
        }

        // Replace the original text node with the fragment
        if (node.parentNode) {
            node.parentNode.replaceChild(fragment, node);
        }

        // Mark body to prevent duplicate execution
        document.body.setAttribute('data-instagram-linked', 'true');
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