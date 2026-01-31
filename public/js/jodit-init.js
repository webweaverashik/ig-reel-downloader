/**
 * Jodit Editor Initialization
 * Completely free - MIT License
 * https://xdsoft.net/jodit/
 */

(function () {
      'use strict';

      // Store editor instances
      window.joditEditors = {};

      /**
       * Initialize Jodit Editor
       * @param {string} elementId - The textarea element ID
       * @param {object} options - Configuration options
       */
      window.initJoditEditor = function (elementId, options = {}) {
            const element = document.getElementById(elementId);
            if (!element) {
                  console.error('Element not found:', elementId);
                  return null;
            }

            // Check if already initialized
            if (window.joditEditors[elementId]) {
                  return window.joditEditors[elementId];
            }

            // Detect dark mode
            const isDark = document.documentElement.classList.contains('dark');

            // Default configuration
            const defaultConfig = {
                  height: 450,
                  theme: isDark ? 'dark' : 'default',
                  placeholder: options.placeholder || 'Start writing your content...',
                  showCharsCounter: true,
                  showWordsCounter: true,
                  showXPathInStatusbar: false,
                  spellcheck: true,
                  toolbarSticky: true,
                  toolbarStickyOffset: 64,
                  askBeforePasteHTML: false,
                  askBeforePasteFromWord: false,
                  defaultActionOnPaste: 'insert_clear_html',

                  // Toolbar buttons
                  buttons: [
                        'source', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'ul', 'ol', '|',
                        'font', 'fontsize', 'brush', 'paragraph', '|',
                        'image', 'video', 'table', 'link', '|',
                        'align', 'indent', 'outdent', '|',
                        'hr', 'eraser', 'copyformat', '|',
                        'symbol', 'fullsize', 'selectall', 'print', '|',
                        'undo', 'redo'
                  ],

                  // Mobile toolbar
                  buttonsMD: [
                        'bold', 'italic', '|',
                        'ul', 'ol', '|',
                        'image', 'table', 'link', '|',
                        'paragraph', '|',
                        'fullsize', 'dots'
                  ],

                  buttonsSM: [
                        'bold', 'italic', '|',
                        'ul', 'ol', '|',
                        'image', 'link', '|',
                        'dots'
                  ],

                  // Image configuration
                  uploader: {
                        insertImageAsBase64URI: true // Use base64 for simplicity
                  },

                  // Table configuration
                  table: {
                        allowCellResize: true,
                        selectionCellStyle: 'border: 1px double #8b5cf6 !important;'
                  },

                  // Colors
                  colors: [
                        '#000000', '#434343', '#666666', '#999999', '#b7b7b7', '#cccccc', '#d9d9d9', '#efefef', '#f3f3f3', '#ffffff',
                        '#980000', '#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#4a86e8', '#0000ff', '#9900ff', '#ff00ff',
                        '#e6b8af', '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#c9daf8', '#cfe2f3', '#d9d2e9', '#ead1dc',
                        '#8b5cf6', '#7c3aed', '#6d28d9', '#5b21b6', '#4c1d95', '#ec4899', '#db2777', '#be185d', '#9d174d', '#831843'
                  ],

                  // Events
                  events: {
                        afterInit: function (editor) {
                              console.log('Jodit initialized:', elementId);

                              // Sync to hidden input on change
                              if (options.syncTo) {
                                    const syncInput = document.getElementById(options.syncTo);
                                    if (syncInput) {
                                          editor.events.on('change', function () {
                                                syncInput.value = editor.value;
                                          });
                                    }
                              }

                              // Auto-save to localStorage
                              if (options.autosave) {
                                    const storageKey = 'jodit_draft_' + elementId;

                                    // Load draft
                                    const draft = localStorage.getItem(storageKey);
                                    if (draft && !editor.value.trim()) {
                                          if (confirm('A draft was found. Would you like to restore it?')) {
                                                editor.value = draft;
                                                if (options.syncTo) {
                                                      document.getElementById(options.syncTo).value = draft;
                                                }
                                          }
                                    }

                                    // Save draft on change
                                    let saveTimeout;
                                    editor.events.on('change', function () {
                                          clearTimeout(saveTimeout);
                                          saveTimeout = setTimeout(function () {
                                                localStorage.setItem(storageKey, editor.value);
                                          }, 1000);
                                    });
                              }
                        }
                  }
            };

            // Merge options
            const config = { ...defaultConfig, ...options };

            // Create editor
            const editor = Jodit.make('#' + elementId, config);
            window.joditEditors[elementId] = editor;

            return editor;
      };

      /**
       * Get editor instance
       */
      window.getJoditEditor = function (elementId) {
            return window.joditEditors[elementId] || null;
      };

      /**
       * Destroy editor instance
       */
      window.destroyJoditEditor = function (elementId) {
            if (window.joditEditors[elementId]) {
                  window.joditEditors[elementId].destruct();
                  delete window.joditEditors[elementId];
            }
      };

      /**
       * Clear draft from localStorage
       */
      window.clearJoditDraft = function (elementId) {
            localStorage.removeItem('jodit_draft_' + elementId);
      };

      /**
       * Auto-initialize editors with data attribute
       */
      function autoInitialize() {
            document.querySelectorAll('[data-jodit]').forEach(function (element) {
                  if (!element.id) {
                        element.id = 'jodit_' + Math.random().toString(36).substr(2, 9);
                  }

                  const options = {
                        syncTo: element.getAttribute('data-jodit-sync'),
                        autosave: element.hasAttribute('data-jodit-autosave'),
                        placeholder: element.getAttribute('data-jodit-placeholder') || 'Start writing...'
                  };

                  window.initJoditEditor(element.id, options);
            });
      }

      // Watch for dark mode changes
      const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                  if (mutation.attributeName === 'class') {
                        const isDark = document.documentElement.classList.contains('dark');
                        Object.keys(window.joditEditors).forEach(function (id) {
                              const editor = window.joditEditors[id];
                              if (editor && editor.container) {
                                    if (isDark) {
                                          editor.container.classList.add('jodit_theme_dark');
                                    } else {
                                          editor.container.classList.remove('jodit_theme_dark');
                                    }
                              }
                        });
                  }
            });
      });

      observer.observe(document.documentElement, { attributes: true });

      // Initialize on DOM ready
      if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', autoInitialize);
      } else {
            autoInitialize();
      }

})();