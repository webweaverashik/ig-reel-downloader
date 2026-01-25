/**
 * Instagram Downloader - Pure JavaScript
 * Phase 1: Core Downloader Functionality
 * 
 * Features:
 * - Instagram URL validation
 * - Async fetch with progress handling
 * - Preview rendering (Reels, Videos, Photos, Carousel, Stories)
 * - Download trigger logic
 * - Error handling
 * - FAQ accordion
 */

(function () {
    'use strict';

    // ============================================
    // CONFIGURATION
    // ============================================
    const CONFIG = {
        fetchEndpoint: '/instagram-downloader/fetch',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        urlPatterns: [
            /^https?:\/\/(www\.)?instagram\.com\/p\/[\w-]+\/?/,
            /^https?:\/\/(www\.)?instagram\.com\/reel\/[\w-]+\/?/,
            /^https?:\/\/(www\.)?instagram\.com\/reels\/[\w-]+\/?/,
            /^https?:\/\/(www\.)?instagram\.com\/tv\/[\w-]+\/?/,
            /^https?:\/\/(www\.)?instagram\.com\/stories\/[\w.]+\/\d+\/?/
        ]
    };

    // ============================================
    // DOM ELEMENTS
    // ============================================
    const elements = {
        form: document.getElementById('downloadForm'),
        urlInput: document.getElementById('urlInput'),
        downloadBtn: document.getElementById('downloadBtn'),
        btnText: document.getElementById('btnText'),
        btnLoader: document.getElementById('btnLoader'),
        btnIcon: document.getElementById('btnIcon'),
        errorMessage: document.getElementById('errorMessage'),
        errorText: document.getElementById('errorText'),
        successMessage: document.getElementById('successMessage'),
        successText: document.getElementById('successText'),
        previewSection: document.getElementById('previewSection'),
        loadingSection: document.getElementById('loadingSection'),
        mediaGrid: document.getElementById('mediaGrid'),
        profileInitial: document.getElementById('profileInitial'),
        profileImage: document.getElementById('profileImage'),
        username: document.getElementById('username'),
        contentType: document.getElementById('contentType'),
        mediaCount: document.getElementById('mediaCount'),
        caption: document.getElementById('caption'),
        downloadAllBtn: document.getElementById('downloadAllBtn')
    };

    // ============================================
    // URL VALIDATION
    // ============================================
    function isValidInstagramUrl(url) {
        if (!url || typeof url !== 'string') return false;
        url = url.trim();
        return CONFIG.urlPatterns.some(pattern => pattern.test(url));
    }

    // ============================================
    // UI STATE MANAGEMENT
    // ============================================
    function showError(message) {
        if (elements.errorText) {
            elements.errorText.textContent = message;
        }
        if (elements.errorMessage) {
            elements.errorMessage.classList.remove('hidden');
        }
        if (elements.successMessage) {
            elements.successMessage.classList.add('hidden');
        }
        if (elements.previewSection) {
            elements.previewSection.classList.add('hidden');
        }
        if (elements.loadingSection) {
            elements.loadingSection.classList.add('hidden');
        }
    }

    function showSuccess(message) {
        if (elements.successText) {
            elements.successText.textContent = message;
        }
        if (elements.successMessage) {
            elements.successMessage.classList.remove('hidden');
        }
        if (elements.errorMessage) {
            elements.errorMessage.classList.add('hidden');
        }
    }

    function hideMessages() {
        if (elements.errorMessage) {
            elements.errorMessage.classList.add('hidden');
        }
        if (elements.successMessage) {
            elements.successMessage.classList.add('hidden');
        }
    }

    function setLoading(loading) {
        if (loading) {
            if (elements.btnText) elements.btnText.textContent = 'Processing...';
            if (elements.btnLoader) elements.btnLoader.classList.remove('hidden');
            if (elements.btnIcon) elements.btnIcon.classList.add('hidden');
            if (elements.downloadBtn) elements.downloadBtn.disabled = true;
            if (elements.urlInput) elements.urlInput.disabled = true;
            if (elements.loadingSection) elements.loadingSection.classList.remove('hidden');
            if (elements.previewSection) elements.previewSection.classList.add('hidden');
        } else {
            if (elements.btnText) elements.btnText.textContent = 'Download';
            if (elements.btnLoader) elements.btnLoader.classList.add('hidden');
            if (elements.btnIcon) elements.btnIcon.classList.remove('hidden');
            if (elements.downloadBtn) elements.downloadBtn.disabled = false;
            if (elements.urlInput) elements.urlInput.disabled = false;
            if (elements.loadingSection) elements.loadingSection.classList.add('hidden');
        }
    }

    // ============================================
    // PREVIEW RENDERING
    // ============================================
    function renderPreview(data) {
        // Update profile section
        const headerThumb = (typeof data.thumbnail === 'string' && /^https?:\/\//i.test(data.thumbnail)) ? data.thumbnail : '';

        if (headerThumb && elements.profileImage) {
            elements.profileImage.src = headerThumb;
            elements.profileImage.classList.remove('hidden');
            if (elements.profileInitial) elements.profileInitial.classList.add('hidden');
        } else {
            if (elements.profileInitial) {
                elements.profileInitial.textContent = (data.username || 'U').charAt(0).toUpperCase();
                elements.profileInitial.classList.remove('hidden');
            }
            if (elements.profileImage) elements.profileImage.classList.add('hidden');
        }

        // Update metadata
        if (elements.username) {
            elements.username.textContent = '@' + (data.username || 'instagram_user');
        }
        if (elements.contentType) {
            elements.contentType.textContent = capitalizeFirst(data.type || 'Post');
        }
        if (elements.mediaCount) {
            elements.mediaCount.textContent = formatMediaCount(data.items?.length || 1);
        }
        if (elements.caption) {
            elements.caption.textContent = data.caption || '';
        }

        // Filter items based on type
        let items = Array.isArray(data.items) ? data.items : [];
        const pageType = (data.type || '').toLowerCase();

        // For reels/videos, filter to show only video items
        if (pageType === 'reel' || pageType === 'video' || pageType === 'tv') {
            const videoItems = items.filter(it =>
                (it.type === 'video') ||
                ((it.format || '').toLowerCase() === 'mp4') ||
                ((it.format || '').toLowerCase() === 'webm')
            );
            // Only use filtered list if we found videos
            if (videoItems.length > 0) {
                items = videoItems;
            }
        }

        renderMediaGrid(items);

        // Update download all button
        if (data.download_all_url && elements.downloadAllBtn) {
            elements.downloadAllBtn.href = data.download_all_url;
        }

        // Show preview section
        if (elements.previewSection) {
            elements.previewSection.classList.remove('hidden');
        }

        // Scroll to preview
        setTimeout(() => {
            if (elements.previewSection) {
                elements.previewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 100);
    }

    function renderMediaGrid(items) {
        if (!elements.mediaGrid) return;

        elements.mediaGrid.innerHTML = '';

        if (!items || items.length === 0) {
            elements.mediaGrid.innerHTML = '<p class="text-gray-500 dark:text-gray-400 col-span-2 text-center py-8">No media items found.</p>';
            return;
        }

        items.forEach((item, index) => {
            const mediaCard = createMediaCard(item, index);
            elements.mediaGrid.appendChild(mediaCard);
        });
    }

    function createMediaCard(item, index) {
        const card = document.createElement('div');
        card.className = 'relative group rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-800 fade-in';

        const isVideo = item.format === 'mp4' || item.format === 'webm' || item.format === 'mkv' || item.type === 'video';
        const isImage = !isVideo;

        // Prefer Laravel-served thumbnail_url
        const safeThumb = (item.thumbnail_url && /^https?:\/\//i.test(item.thumbnail_url))
            ? item.thumbnail_url
            : (item.thumbnail && /^https?:\/\//i.test(item.thumbnail) ? item.thumbnail : '');

        // For image media, the download_url is the actual image
        const displayImageUrl = (isImage && item.download_url) ? item.download_url : safeThumb;

        const fallbackSvg = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500"%3E%3Crect fill="%23374151" width="400" height="500"/%3E%3Ctext x="50%25" y="50%25" fill="%239CA3AF" text-anchor="middle" dy=".3em" font-family="system-ui" font-size="14"%3EMedia ' + (index + 1) + '%3C/text%3E%3C/svg%3E';
        const mediaUrl = item.download_url || '#';

        card.innerHTML = `
            <div class="aspect-[4/5] relative">
                ${isVideo && mediaUrl !== '#' ? `
                    <video 
                        class="w-full h-full object-cover"
                        src="${escapeHtml(mediaUrl)}"
                        playsinline
                        muted
                        loop
                        preload="metadata"
                        poster="${escapeHtml(safeThumb || '')}"
                        onmouseenter="this.play()"
                        onmouseleave="this.pause()"
                    ></video>
                ` : `
                    <img 
                        src="${escapeHtml(displayImageUrl || fallbackSvg)}" 
                        alt="Media ${index + 1}" 
                        class="w-full h-full object-cover"
                        onerror="this.src='${fallbackSvg}'"
                        loading="lazy"
                    >
                `}

                <div class="absolute top-3 left-3">
                    <span class="px-2 py-1 rounded-lg bg-black/60 text-white text-xs font-medium">
                        ${isVideo ? '🎬 Video' : '📷 Photo'}
                    </span>
                </div>
                <div class="absolute top-3 right-3">
                    <span class="px-2 py-1 rounded-lg bg-violet-600 text-white text-xs font-medium">
                        ${escapeHtml(item.quality || (isVideo ? 'HD' : 'Original'))}
                    </span>
                </div>
            </div>
            <div class="p-4">
                <a 
                    href="${escapeHtml(item.download_url || '#')}" 
                    class="w-full py-3 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium transition-colors flex items-center justify-center space-x-2 block text-center"
                    download="${escapeHtml(item.filename || '')}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span>Download ${escapeHtml((item.format || 'file').toUpperCase())}</span>
                </a>
            </div>
        `;

        return card;
    }

    // ============================================
    // HELPER FUNCTIONS
    // ============================================
    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }

    function formatMediaCount(count) {
        if (count === 1) return '1 item';
        return count + ' items';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ============================================
    // API FETCH
    // ============================================
    async function fetchInstagramContent(url) {
        const response = await fetch(CONFIG.fetchEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken
            },
            body: JSON.stringify({ url: url })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to fetch content');
        }

        return data;
    }

    // ============================================
    // FORM SUBMISSION HANDLER
    // ============================================
    async function handleSubmit(event) {
        event.preventDefault();

        const url = elements.urlInput ? elements.urlInput.value.trim() : '';

        hideMessages();

        // Client-side validation
        if (!url) {
            showError('Please enter an Instagram URL');
            return;
        }

        if (!isValidInstagramUrl(url)) {
            showError('Please enter a valid Instagram URL (post, reel, video, story, or carousel)');
            return;
        }

        setLoading(true);

        try {
            const data = await fetchInstagramContent(url);

            if (data.success) {
                showSuccess('Content fetched successfully!');
                renderPreview(data);
            } else {
                showError(data.error || 'Failed to fetch content');
            }
        } catch (error) {
            console.error('Fetch error:', error);

            // Handle specific error types
            const msg = (error.message || '').toLowerCase();

            if (msg.includes('cookies_missing') || msg.includes('cookies file') || msg.includes('cookies not configured') || msg.includes('no instagram cookies')) {
                showError('Instagram cookies not configured. Please contact administrator.');
            } else if (msg.includes('login_required') || msg.includes('login required')) {
                showError('Login required. Cookies may be expired. Please try again later.');
            } else if (msg.includes('private_content') || msg.includes('private')) {
                showError('This content is from a private account and cannot be downloaded.');
            } else if (msg.includes('rate_limited') || msg.includes('rate') || msg.includes('too many')) {
                showError('Rate limited by Instagram. Please try again in a few minutes.');
            } else if (msg.includes('not_found') || msg.includes('not found') || msg.includes('removed')) {
                showError('This post was not found or has been removed.');
            } else if (msg.includes('no_formats') || msg.includes('no video formats') || msg.includes('no downloadable')) {
                showError('No downloadable content found. The post may be restricted.');
            } else if (msg.includes('all cookies failed') || msg.includes('cookies exhausted')) {
                showError('All cookies have been exhausted. Please try again later or contact administrator.');
            } else {
                showError(error.message || 'An unexpected error occurred. Please try again.');
            }
        } finally {
            setLoading(false);
        }
    }

    // ============================================
    // FAQ ACCORDION
    // ============================================
    function initFaqAccordion() {
        document.querySelectorAll('.faq-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const content = button.nextElementSibling;
                const icon = button.querySelector('.faq-icon');

                // Close all other FAQs
                document.querySelectorAll('.faq-content').forEach(c => {
                    if (c !== content) {
                        c.classList.add('hidden');
                        const otherIcon = c.previousElementSibling?.querySelector('.faq-icon');
                        if (otherIcon) {
                            otherIcon.style.transform = 'rotate(0deg)';
                        }
                    }
                });

                // Toggle current FAQ
                if (content) {
                    content.classList.toggle('hidden');
                    if (icon) {
                        icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                    }
                }
            });
        });
    }

    // ============================================
    // INPUT HANDLERS
    // ============================================
    function initInputHandlers() {
        if (!elements.urlInput) return;

        // Clear error on input
        elements.urlInput.addEventListener('input', () => {
            hideMessages();
        });

        // Handle paste event for quick validation feedback
        elements.urlInput.addEventListener('paste', () => {
            setTimeout(() => {
                const url = elements.urlInput.value.trim();
                if (url && !isValidInstagramUrl(url)) {
                    showError('This doesn\'t look like a valid Instagram URL');
                }
            }, 100);
        });

        // Handle Enter key
        elements.urlInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (elements.form) {
                    elements.form.dispatchEvent(new Event('submit'));
                }
            }
        });
    }

    // ============================================
    // INITIALIZATION
    // ============================================
    function init() {
        if (!elements.form) {
            console.warn('Download form not found on this page');
            return;
        }

        // Form submission
        elements.form.addEventListener('submit', handleSubmit);

        // Initialize other components
        initFaqAccordion();
        initInputHandlers();

        console.log('Instagram Downloader initialized - IGReelDownloader.net');
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();