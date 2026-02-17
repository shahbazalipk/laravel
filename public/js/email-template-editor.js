/**
 * Email Template Editor with TinyMCE
 * Provides rich text editing, merge code picker, auto-save, and preview functionality
 */

class EmailTemplateEditor {
    constructor(options = {}) {
        this.options = {
            contentField: options.contentField || 'html_content',
            subjectField: options.subjectField || 'subject',
            autoSaveInterval: options.autoSaveInterval || 30000, // 30 seconds
            autoSaveKey: options.autoSaveKey || 'email_template_draft',
            mergeCodes: options.mergeCodes || {},
            ...options
        };
        
        this.editor = null;
        this.autoSaveTimer = null;
        this.isDirty = false;
        
        this.init();
    }
    
    init() {
        this.initTinyMCE();
        this.initAutoSave();
        this.initMergeCodePicker();
        this.initPreview();
        this.loadDraft();
    }
    
    /**
     * Initialize TinyMCE with custom configuration
     */
    initTinyMCE() {
        const self = this;
        
        tinymce.init({
            selector: `#${this.options.contentField}`,
            height: 500,
            menubar: true,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | ' +
                     'alignleft aligncenter alignright alignjustify | ' +
                     'bullist numlist outdent indent | ' +
                     'forecolor backcolor | link image | ' +
                     'mergecode | preview code | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
            
            // Custom merge code button
            setup: function(editor) {
                self.editor = editor;
                
                // Add merge code button
                editor.ui.registry.addMenuButton('mergecode', {
                    text: 'Merge Code',
                    icon: 'template',
                    fetch: function(callback) {
                        const items = self.buildMergeCodeMenu();
                        callback(items);
                    }
                });
                
                // Track changes for auto-save
                editor.on('change', function() {
                    self.isDirty = true;
                });
                
                // Track changes for dirty state
                editor.on('input', function() {
                    self.isDirty = true;
                });
            },
            
            // Image upload handler (if needed)
            images_upload_handler: function(blobInfo, success, failure) {
                // Convert to base64 for now (in production, upload to server)
                const reader = new FileReader();
                reader.onload = function() {
                    success(reader.result);
                };
                reader.onerror = function() {
                    failure('Image upload failed');
                };
                reader.readAsDataURL(blobInfo.blob());
            }
        });
    }
    
    /**
     * Build merge code menu items for TinyMCE
     */
    buildMergeCodeMenu() {
        const items = [];
        
        Object.keys(this.options.mergeCodes).forEach(category => {
            const codes = this.options.mergeCodes[category];
            const submenuItems = [];
            
            Object.keys(codes).forEach(code => {
                submenuItems.push({
                    type: 'menuitem',
                    text: `{{${code}}} - ${codes[code]}`,
                    onAction: () => {
                        this.insertMergeCode(code);
                    }
                });
            });
            
            items.push({
                type: 'nestedmenuitem',
                text: category.charAt(0).toUpperCase() + category.slice(1),
                getSubmenuItems: () => submenuItems
            });
        });
        
        return items;
    }
    
    /**
     * Insert merge code at cursor position
     */
    insertMergeCode(code) {
        if (this.editor) {
            this.editor.insertContent(`{{${code}}}`);
            this.isDirty = true;
        }
    }
    
    /**
     * Initialize auto-save functionality
     */
    initAutoSave() {
        const self = this;
        
        // Auto-save timer
        this.autoSaveTimer = setInterval(() => {
            if (self.isDirty) {
                self.saveDraft();
            }
        }, this.options.autoSaveInterval);
        
        // Save on page unload
        window.addEventListener('beforeunload', function(e) {
            if (self.isDirty) {
                self.saveDraft();
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
        
        // Clear draft on form submit
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                self.clearDraft();
            });
        }
    }
    
    /**
     * Save draft to localStorage
     */
    saveDraft() {
        if (!this.editor) return;
        
        const draft = {
            name: document.getElementById('name')?.value || '',
            category: document.getElementById('category')?.value || '',
            subject: document.getElementById(this.options.subjectField)?.value || '',
            html_content: this.editor.getContent(),
            text_content: document.getElementById('text_content')?.value || '',
            description: document.getElementById('description')?.value || '',
            timestamp: new Date().toISOString()
        };
        
        try {
            localStorage.setItem(this.options.autoSaveKey, JSON.stringify(draft));
            this.isDirty = false;
            this.showAutoSaveNotification('Draft saved');
        } catch (e) {
            console.error('Failed to save draft:', e);
        }
    }
    
    /**
     * Load draft from localStorage
     */
    loadDraft() {
        try {
            const draftJson = localStorage.getItem(this.options.autoSaveKey);
            if (!draftJson) return;
            
            const draft = JSON.parse(draftJson);
            const draftAge = new Date() - new Date(draft.timestamp);
            
            // Only load drafts less than 24 hours old
            if (draftAge > 24 * 60 * 60 * 1000) {
                this.clearDraft();
                return;
            }
            
            // Check if form is empty (new template)
            const nameField = document.getElementById('name');
            if (nameField && !nameField.value) {
                // Show restore prompt
                if (confirm('A draft was found. Would you like to restore it?')) {
                    this.restoreDraft(draft);
                }
            }
        } catch (e) {
            console.error('Failed to load draft:', e);
        }
    }
    
    /**
     * Restore draft data to form
     */
    restoreDraft(draft) {
        if (draft.name) document.getElementById('name').value = draft.name;
        if (draft.category) document.getElementById('category').value = draft.category;
        if (draft.subject) document.getElementById(this.options.subjectField).value = draft.subject;
        if (draft.text_content) document.getElementById('text_content').value = draft.text_content;
        if (draft.description) document.getElementById('description').value = draft.description;
        
        // Set TinyMCE content when ready
        if (this.editor) {
            this.editor.setContent(draft.html_content || '');
        } else {
            // Wait for editor to initialize
            const checkEditor = setInterval(() => {
                if (this.editor) {
                    this.editor.setContent(draft.html_content || '');
                    clearInterval(checkEditor);
                }
            }, 100);
        }
        
        this.showAutoSaveNotification('Draft restored');
    }
    
    /**
     * Clear draft from localStorage
     */
    clearDraft() {
        try {
            localStorage.removeItem(this.options.autoSaveKey);
            this.isDirty = false;
        } catch (e) {
            console.error('Failed to clear draft:', e);
        }
    }
    
    /**
     * Show auto-save notification
     */
    showAutoSaveNotification(message) {
        // Create or update notification element
        let notification = document.getElementById('autosave-notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'autosave-notification';
            notification.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg transition-opacity duration-300 z-50';
            document.body.appendChild(notification);
        }
        
        notification.textContent = message;
        notification.style.opacity = '1';
        
        // Fade out after 2 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
        }, 2000);
    }
    
    /**
     * Initialize merge code picker for non-TinyMCE fields
     */
    initMergeCodePicker() {
        const self = this;
        
        // Subject field merge code button
        const subjectButton = document.querySelector('[onclick*="toggleMergeCodePicker"]');
        if (subjectButton) {
            subjectButton.onclick = function(e) {
                e.preventDefault();
                self.showMergeCodeModal('subject');
            };
        }
    }
    
    /**
     * Show merge code modal
     */
    showMergeCodeModal(targetField) {
        this.currentTargetField = targetField;
        const modal = document.getElementById('mergeCodeModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    
    /**
     * Initialize preview functionality
     */
    initPreview() {
        const self = this;
        const previewButton = document.querySelector('[onclick*="previewTemplate"]');
        
        if (previewButton) {
            previewButton.onclick = function(e) {
                e.preventDefault();
                self.showPreview();
            };
        }
    }
    
    /**
     * Show template preview with sample data
     */
    showPreview() {
        if (!this.editor) {
            alert('Editor not initialized');
            return;
        }
        
        const htmlContent = this.editor.getContent();
        const subject = document.getElementById(this.options.subjectField)?.value || '';
        
        if (!htmlContent) {
            alert('Please enter HTML content to preview');
            return;
        }
        
        // Sample data for preview
        const sampleData = {
            'first_name': 'John',
            'last_name': 'Doe',
            'email': 'john@example.com',
            'company': 'Example Corp',
            'registration_number': 'REG-12345',
            'event_name': 'Sample Event 2024',
            'event_date': 'March 15, 2024',
            'event_location': 'Convention Center',
            'unsubscribe_url': '#unsubscribe'
        };
        
        // Replace merge codes with sample data
        let preview = htmlContent;
        let previewSubject = subject;
        
        Object.keys(sampleData).forEach(key => {
            const regex = new RegExp('{{\\s*' + key + '\\s*}}', 'gi');
            preview = preview.replace(regex, sampleData[key]);
            previewSubject = previewSubject.replace(regex, sampleData[key]);
        });
        
        // Show preview modal
        const previewContent = document.getElementById('previewContent');
        if (previewContent) {
            previewContent.innerHTML = `
                <div class="mb-4">
                    <div class="text-sm font-semibold text-gray-700 mb-1">Subject:</div>
                    <div class="text-sm text-gray-900 bg-white p-3 rounded border">${this.escapeHtml(previewSubject)}</div>
                </div>
                <div class="mb-4">
                    <div class="flex space-x-2 mb-2">
                        <button onclick="emailTemplateEditor.switchPreviewMode('desktop')" 
                                id="preview-desktop-btn"
                                class="px-3 py-1 text-sm bg-indigo-600 text-white rounded">
                            Desktop
                        </button>
                        <button onclick="emailTemplateEditor.switchPreviewMode('mobile')" 
                                id="preview-mobile-btn"
                                class="px-3 py-1 text-sm bg-gray-300 text-gray-700 rounded">
                            Mobile
                        </button>
                    </div>
                </div>
                <div class="bg-white rounded-lg border">
                    <iframe id="preview-iframe" 
                            srcdoc="${this.escapeHtml(preview)}" 
                            class="w-full h-96 border-0 rounded transition-all duration-300"
                            style="max-width: 100%;"></iframe>
                </div>
            `;
        }
        
        const modal = document.getElementById('previewModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    
    /**
     * Switch preview mode between desktop and mobile
     */
    switchPreviewMode(mode) {
        const iframe = document.getElementById('preview-iframe');
        const desktopBtn = document.getElementById('preview-desktop-btn');
        const mobileBtn = document.getElementById('preview-mobile-btn');
        
        if (mode === 'mobile') {
            iframe.style.maxWidth = '375px';
            iframe.style.margin = '0 auto';
            desktopBtn.className = 'px-3 py-1 text-sm bg-gray-300 text-gray-700 rounded';
            mobileBtn.className = 'px-3 py-1 text-sm bg-indigo-600 text-white rounded';
        } else {
            iframe.style.maxWidth = '100%';
            iframe.style.margin = '0';
            desktopBtn.className = 'px-3 py-1 text-sm bg-indigo-600 text-white rounded';
            mobileBtn.className = 'px-3 py-1 text-sm bg-gray-300 text-gray-700 rounded';
        }
    }
    
    /**
     * Escape HTML for safe display
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Cleanup on destroy
     */
    destroy() {
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
        }
        if (this.editor) {
            tinymce.remove(this.editor);
        }
    }
}

// Global instance for access from inline handlers
let emailTemplateEditor = null;
