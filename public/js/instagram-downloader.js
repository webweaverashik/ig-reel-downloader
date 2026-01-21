/**
 * ig reel downloader - Instagram Downloader JavaScript
 * Phase 1 Implementation
 */

(function () {
      'use strict';

      // ============================================
      // Dark/Light Mode Toggle
      // ============================================
      const themeToggle = document.getElementById('themeToggle');
      const html = document.documentElement;

      function loadTheme() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                  html.classList.remove('dark');
                  if (themeToggle) themeToggle.checked = true;
            } else {
                  html.classList.add('dark');
                  if (themeToggle) themeToggle.checked = false;
            }
      }

      if (themeToggle) {
            themeToggle.addEventListener('change', () => {
                  if (themeToggle.checked) {
                        html.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                  } else {
                        html.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                  }
            });
      }

      loadTheme();

      // ============================================
      // Mobile Menu Toggle
      // ============================================
      const mobileMenuBtn = document.getElementById('mobileMenuBtn');
      const mobileMenu = document.getElementById('mobileMenu');

      if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                  mobileMenu.classList.toggle('hidden');
            });
      }

      // ============================================
      // FAQ Accordion
      // ============================================
      document.querySelectorAll('.faq-toggle').forEach(button => {
            button.addEventListener('click', () => {
                  const content = button.nextElementSibling;
                  const icon = button.querySelector('svg');

                  content.classList.toggle('hidden');
                  icon.style.transform = content.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
            });
      });

      // ============================================
      // Instagram URL Validation
      // ============================================
      function isValidInstagramUrl(url) {
            const patterns = [
                  /^https?:\/\/(www\.)?instagram\.com\/p\/[\w-]+\/?/,
                  /^https?:\/\/(www\.)?instagram\.com\/reel\/[\w-]+\/?/,
                  /^https?:\/\/(www\.)?instagram\.com\/reels\/[\w-]+\/?/,
                  /^https?:\/\/(www\.)?instagram\.com\/tv\/[\w-]+\/?/,
                  /^https?:\/\/(www\.)?instagram\.com\/[\w.]+\/reel\/[\w-]+\/?/
            ];
            return patterns.some(pattern => pattern.test(url));
      }

      function getMediaType(url) {
            if (url.includes('/reel/') || url.includes('/reels/')) return 'reel';
            if (url.includes('/tv/')) return 'video';
            if (url.includes('/p/')) return 'post';
            return 'unknown';
      }

      // ============================================
      // DOM Elements
      // ============================================
      const downloadForm = document.getElementById('downloadForm');
      const instagramUrlInput = document.getElementById('instagramUrl');
      const downloadBtn = document.getElementById('downloadBtn');
      const btnText = document.getElementById('btnText');
      const btnLoader = document.getElementById('btnLoader');
      const errorMessage = document.getElementById('errorMessage');
      const errorText = document.getElementById('errorText');
      const loadingSection = document.getElementById('loadingSection');
      const previewSection = document.getElementById('previewSection');

      // ============================================
      // Helper Functions
      // ============================================
      function showError(message) {
            if (errorText && errorMessage) {
                  errorText.textContent = message;
                  errorMessage.classList.remove('hidden');
                  setTimeout(() => {
                        errorMessage.classList.add('hidden');
                  }, 5000);
            }
      }

      function setLoading(loading) {
            if (loading) {
                  btnText.classList.add('hidden');
                  btnLoader.classList.remove('hidden');
                  btnLoader.classList.add('flex');
                  downloadBtn.disabled = true;
                  loadingSection.classList.remove('hidden');
                  previewSection.classList.add('hidden');
            } else {
                  btnText.classList.remove('hidden');
                  btnLoader.classList.add('hidden');
                  btnLoader.classList.remove('flex');
                  downloadBtn.disabled = false;
                  loadingSection.classList.add('hidden');
            }
      }

      function renderPreview(data) {
            document.getElementById('previewThumbnail').src = data.thumbnail;
            document.getElementById('previewUsername').textContent = '@' + data.username;
            document.getElementById('previewUserInitial').textContent = data.username.charAt(0).toUpperCase();
            document.getElementById('previewCaption').textContent = data.caption || 'No caption available';

            const typeBadge = document.getElementById('mediaTypeBadge');
            typeBadge.textContent = data.type.toUpperCase();

            const downloadOptions = document.getElementById('downloadOptions');
            downloadOptions.innerHTML = '';

            data.formats.forEach(format => {
                  const btn = document.createElement('a');
                  btn.href = format.url;
                  btn.target = '_blank';
                  btn.rel = 'noopener noreferrer';
                  btn.className = 'inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-white transition-all duration-300 transform hover:scale-105 bg-gradient-to-r from-primary to-secondary hover:shadow-lg hover:shadow-primary/30';
                  btn.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                ${format.quality} ${format.format.toUpperCase()}
            `;
                  downloadOptions.appendChild(btn);
            });

            previewSection.classList.remove('hidden');
            previewSection.classList.add('fade-in');
      }

      // ============================================
      // Form Submission Handler
      // ============================================
      if (downloadForm) {
            downloadForm.addEventListener('submit', async (e) => {
                  e.preventDefault();

                  const url = instagramUrlInput.value.trim();

                  if (!url) {
                        showError('Please enter an Instagram URL');
                        return;
                  }

                  if (!isValidInstagramUrl(url)) {
                        showError('Please enter a valid Instagram URL (reel, video, or photo)');
                        return;
                  }

                  setLoading(true);
                  errorMessage.classList.add('hidden');

                  try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                        const response = await fetch('/api/instagram/fetch', {
                              method: 'POST',
                              headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken || ''
                              },
                              body: JSON.stringify({ url: url })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                              throw new Error(data.message || 'Failed to fetch media');
                        }

                        renderPreview(data);
                  } catch (error) {
                        showError(error.message || 'An error occurred. Please try again.');
                  } finally {
                        setLoading(false);
                  }
            });
      }

      // ============================================
      // Clear preview when URL changes
      // ============================================
      if (instagramUrlInput) {
            instagramUrlInput.addEventListener('input', () => {
                  if (previewSection) previewSection.classList.add('hidden');
                  if (errorMessage) errorMessage.classList.add('hidden');
            });
      }

})();