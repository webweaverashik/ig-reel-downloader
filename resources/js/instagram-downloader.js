/**
 * Instagram Downloader - Pure JavaScript
 * Phase 1: Core Downloader Functionality
 * 
 * Features:
 * - Instagram URL validation
 * - Async fetch with progress handling
 * - Preview rendering (Reels, Videos, Photos, Carousel)
 * - Download trigger logic
 * - Error handling
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

      function getContentTypeFromUrl(url) {
            if (/\/reel\/|\/reels\//.test(url)) return 'reel';
            if (/\/stories\//.test(url)) return 'story';
            if (/\/tv\//.test(url)) return 'video';
            return 'post';
      }

      // ============================================
      // UI STATE MANAGEMENT
      // ============================================
      function showError(message) {
            elements.errorText.textContent = message;
            elements.errorMessage.classList.remove('hidden');
            elements.successMessage.classList.add('hidden');
            elements.previewSection.classList.add('hidden');
            elements.loadingSection.classList.add('hidden');
      }

      function showSuccess(message) {
            elements.successText.textContent = message;
            elements.successMessage.classList.remove('hidden');
            elements.errorMessage.classList.add('hidden');
      }

      function hideMessages() {
            elements.errorMessage.classList.add('hidden');
            elements.successMessage.classList.add('hidden');
      }

      function setLoading(loading) {
            if (loading) {
                  elements.btnText.textContent = 'Processing...';
                  elements.btnLoader.classList.remove('hidden');
                  elements.btnIcon.classList.add('hidden');
                  elements.downloadBtn.disabled = true;
                  elements.urlInput.disabled = true;
                  elements.loadingSection.classList.remove('hidden');
                  elements.previewSection.classList.add('hidden');
            } else {
                  elements.btnText.textContent = 'Download';
                  elements.btnLoader.classList.add('hidden');
                  elements.btnIcon.classList.remove('hidden');
                  elements.downloadBtn.disabled = false;
                  elements.urlInput.disabled = false;
                  elements.loadingSection.classList.add('hidden');
            }
      }

      // ============================================
      // PREVIEW RENDERING
      // ============================================
      function renderPreview(data) {
            // Update profile section
            if (data.thumbnail) {
                  elements.profileImage.src = data.thumbnail;
                  elements.profileImage.classList.remove('hidden');
                  elements.profileInitial.classList.add('hidden');
            } else {
                  elements.profileInitial.textContent = (data.username || 'U').charAt(0).toUpperCase();
                  elements.profileInitial.classList.remove('hidden');
                  elements.profileImage.classList.add('hidden');
            }

            // Update metadata
            elements.username.textContent = '@' + (data.username || 'instagram_user');
            elements.contentType.textContent = capitalizeFirst(data.type || 'Post');
            elements.mediaCount.textContent = formatMediaCount(data.items?.length || 1);
            elements.caption.textContent = data.caption || '';

            // Render media grid
            renderMediaGrid(data.items || []);

            // Update download all button
            if (data.download_all_url) {
                  elements.downloadAllBtn.href = data.download_all_url;
            }

            // Show preview section
            elements.previewSection.classList.remove('hidden');

            // Scroll to preview
            setTimeout(() => {
                  elements.previewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
      }

      function renderMediaGrid(items) {
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

            const isVideo = item.format === 'mp4' || item.format === 'webm' || item.type === 'video';
            const thumbnailUrl = item.thumbnail || 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 500"%3E%3Crect fill="%23374151" width="400" height="500"/%3E%3Ctext x="50%25" y="50%25" fill="%239CA3AF" text-anchor="middle" dy=".3em" font-family="system-ui" font-size="14"%3EMedia ' + (index + 1) + '%3C/text%3E%3C/svg%3E';

            card.innerHTML = `
            <div class="aspect-[4/5] relative">
                <img 
                    src="${escapeHtml(thumbnailUrl)}" 
                    alt="Media ${index + 1}" 
                    class="w-full h-full object-cover"
                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 500%22%3E%3Crect fill=%22%23374151%22 width=%22400%22 height=%22500%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%239CA3AF%22 text-anchor=%22middle%22 dy=%22.3em%22 font-family=%22system-ui%22 font-size=%2214%22%3EMedia ${index + 1}%3C/text%3E%3C/svg%3E'"
                >
                ${isVideo ? `
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-16 h-16 rounded-full bg-black/50 flex items-center justify-center">
                            <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>
                ` : ''}
                <div class="absolute top-3 left-3">
                    <span class="px-2 py-1 rounded-lg bg-black/60 text-white text-xs font-medium">
                        ${isVideo ? '🎬 Video' : '📷 Photo'}
                    </span>
                </div>
                <div class="absolute top-3 right-3">
                    <span class="px-2 py-1 rounded-lg bg-violet-600 text-white text-xs font-medium">
                        ${escapeHtml(item.quality || 'HD')}
                    </span>
                </div>
            </div>
            <div class="p-4">
                <a 
                    href="${escapeHtml(item.download_url || '#')}" 
                    class="w-full py-3 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium transition-colors flex items-center justify-center space-x-2 block text-center"
                    download
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

            const url = elements.urlInput.value.trim();

            hideMessages();

            // Client-side validation
            if (!url) {
                  showError('Please enter an Instagram URL');
                  return;
            }

            if (!isValidInstagramUrl(url)) {
                  showError('Please enter a valid Instagram URL (post, reel, video, or story)');
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
                  if (error.message.includes('cookies')) {
                        showError('Instagram cookies not configured. Please contact administrator.');
                  } else if (error.message.includes('private')) {
                        showError('This content is from a private account and cannot be downloaded.');
                  } else if (error.message.includes('login')) {
                        showError('Login required. Please ensure cookies are properly configured.');
                  } else if (error.message.includes('rate')) {
                        showError('Rate limited by Instagram. Please try again in a few minutes.');
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
                                    const otherIcon = c.previousElementSibling.querySelector('.faq-icon');
                                    if (otherIcon) {
                                          otherIcon.style.transform = 'rotate(0deg)';
                                    }
                              }
                        });

                        // Toggle current FAQ
                        content.classList.toggle('hidden');
                        if (icon) {
                              icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                        }
                  });
            });
      }

      // ============================================
      // INPUT HANDLERS
      // ============================================
      function initInputHandlers() {
            // Clear error on input
            elements.urlInput.addEventListener('input', () => {
                  hideMessages();
            });

            // Handle paste event for quick validation feedback
            elements.urlInput.addEventListener('paste', (e) => {
                  setTimeout(() => {
                        const url = elements.urlInput.value.trim();
                        if (url && !isValidInstagramUrl(url)) {
                              showError('This doesn\'t look like a valid Instagram URL');
                        }
                  }, 100);
            });
      }

      // ============================================
      // INITIALIZATION
      // ============================================
      function init() {
            if (!elements.form) {
                  console.error('Download form not found');
                  return;
            }

            // Form submission
            elements.form.addEventListener('submit', handleSubmit);

            // Initialize other components
            initFaqAccordion();
            initInputHandlers();

            console.log('Instagram Downloader initialized');
      }

      // Initialize when DOM is ready
      if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
      } else {
            init();
      }
})();