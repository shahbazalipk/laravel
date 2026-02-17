/**
 * Campaign Creation Wizard
 * Multi-step form navigation with real-time recipient count and CSV upload
 */

class CampaignWizard {
    constructor(options = {}) {
        this.options = {
            totalSteps: options.totalSteps || 5,
            recipientCountEndpoint: options.recipientCountEndpoint || '/event/admin/email-campaigns/recipient-count',
            csvUploadEndpoint: options.csvUploadEndpoint || '/event/admin/email-campaigns/upload-recipients',
            ...options
        };
        
        this.currentStep = 1;
        this.recipientCount = 0;
        this.csvData = null;
        this.columnMapping = {};
        
        this.init();
    }
    
    init() {
        this.initStepNavigation();
        this.initTemplateSelection();
        this.initRecipientSource();
        this.initCSVUpload();
        this.initRecipientCount();
    }
    
    /**
     * Initialize step navigation
     */
    initStepNavigation() {
        const self = this;
        
        // Next button
        const nextBtn = document.getElementById('next-btn');
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                if (self.validateStep(self.currentStep)) {
                    self.currentStep++;
                    self.showStep(self.currentStep);
                }
            });
        }
        
        // Previous button
        const prevBtn = document.getElementById('prev-btn');
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                self.currentStep--;
                self.showStep(self.currentStep);
            });
        }
        
        // Initialize first step
        this.showStep(this.currentStep);
    }
    
    /**
     * Show specific step
     */
    showStep(step) {
        // Hide all steps
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        
        // Show current step
        const stepElement = document.getElementById(`step-${step}`);
        if (stepElement) {
            stepElement.classList.remove('hidden');
        }
        
        // Update progress indicators
        this.updateProgressIndicators(step);
        
        // Update buttons
        this.updateNavigationButtons(step);
        
        // Update review summary on last step
        if (step === this.options.totalSteps) {
            this.updateReviewSummary();
        }
        
        // Update recipient count when on recipients step
        if (step === 3) {
            this.updateRecipientCount();
        }
    }
    
    /**
     * Update progress indicators
     */
    updateProgressIndicators(step) {
        for (let i = 1; i <= this.options.totalSteps; i++) {
            const indicator = document.getElementById(`step-${i}-indicator`);
            if (!indicator) continue;
            
            const circle = indicator.querySelector('div:first-child');
            const text = indicator.querySelectorAll('div')[1];
            const line = document.getElementById(`line-${i}`);
            
            if (i < step) {
                // Completed steps
                circle.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold';
                text.querySelector('div:first-child').className = 'text-sm font-medium text-gray-900';
                text.querySelector('div:last-child').className = 'text-xs text-gray-500';
                if (line) line.className = 'flex-shrink-0 w-16 h-0.5 bg-green-600';
            } else if (i === step) {
                // Current step
                circle.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-indigo-600 text-white font-semibold';
                text.querySelector('div:first-child').className = 'text-sm font-medium text-gray-900';
                text.querySelector('div:last-child').className = 'text-xs text-gray-500';
                if (line) line.className = 'flex-shrink-0 w-16 h-0.5 bg-gray-300';
            } else {
                // Future steps
                circle.className = 'flex items-center justify-center w-10 h-10 rounded-full bg-gray-300 text-gray-600 font-semibold';
                text.querySelector('div:first-child').className = 'text-sm font-medium text-gray-500';
                text.querySelector('div:last-child').className = 'text-xs text-gray-400';
                if (line) line.className = 'flex-shrink-0 w-16 h-0.5 bg-gray-300';
            }
        }
    }
    
    /**
     * Update navigation buttons
     */
    updateNavigationButtons(step) {
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const submitBtn = document.getElementById('submit-btn');
        
        if (prevBtn) {
            prevBtn.classList.toggle('hidden', step === 1);
        }
        
        if (nextBtn) {
            nextBtn.classList.toggle('hidden', step === this.options.totalSteps);
        }
        
        if (submitBtn) {
            submitBtn.classList.toggle('hidden', step !== this.options.totalSteps);
        }
    }
    
    /**
     * Validate current step
     */
    validateStep(step) {
        if (step === 1) {
            const template = document.querySelector('input[name="email_template_id"]:checked');
            if (!template) {
                alert('Please select an email template');
                return false;
            }
        } else if (step === 2) {
            const name = document.getElementById('name')?.value.trim();
            const senderName = document.getElementById('sender_name')?.value.trim();
            const senderEmail = document.getElementById('sender_email')?.value.trim();
            
            if (!name || !senderName || !senderEmail) {
                alert('Please fill in all required fields');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(senderEmail)) {
                alert('Please enter a valid sender email address');
                return false;
            }
        } else if (step === 3) {
            const recipientSource = document.querySelector('input[name="recipient_source"]:checked');
            if (!recipientSource) {
                alert('Please select a recipient source');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Initialize template selection
     */
    initTemplateSelection() {
        document.querySelectorAll('.template-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.template-card-inner').forEach(card => {
                    card.classList.remove('border-indigo-500', 'bg-indigo-50');
                    card.classList.add('border-gray-300');
                });
                document.querySelectorAll('.check-icon').forEach(icon => icon.classList.add('hidden'));
                
                if (this.checked) {
                    const card = this.closest('.template-card').querySelector('.template-card-inner');
                    card.classList.remove('border-gray-300');
                    card.classList.add('border-indigo-500', 'bg-indigo-50');
                    card.querySelector('.check-icon').classList.remove('hidden');
                }
            });
            
            // Trigger change for pre-selected template
            if (radio.checked) {
                radio.dispatchEvent(new Event('change'));
            }
        });
    }
    
    /**
     * Initialize recipient source selection
     */
    initRecipientSource() {
        const self = this;
        
        document.querySelectorAll('input[name="recipient_source"]').forEach(radio => {
            radio.addEventListener('change', function() {
                self.updateRecipientCount();
            });
        });
    }
    
    /**
     * Initialize CSV upload with drag-and-drop
     */
    initCSVUpload() {
        const self = this;
        const dropzone = document.getElementById('csv-dropzone');
        const fileInput = document.getElementById('csv-file-input');
        
        if (!dropzone || !fileInput) return;
        
        // Click to upload
        dropzone.addEventListener('click', function() {
            fileInput.click();
        });
        
        // File input change
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                self.handleCSVFile(e.target.files[0]);
            }
        });
        
        // Drag and drop
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.add('border-indigo-500', 'bg-indigo-50');
        });
        
        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('border-indigo-500', 'bg-indigo-50');
        });
        
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('border-indigo-500', 'bg-indigo-50');
            
            if (e.dataTransfer.files.length > 0) {
                self.handleCSVFile(e.dataTransfer.files[0]);
            }
        });
    }
    
    /**
     * Handle CSV file upload
     */
    handleCSVFile(file) {
        if (!file.name.endsWith('.csv')) {
            alert('Please upload a CSV file');
            return;
        }
        
        const reader = new FileReader();
        const self = this;
        
        reader.onload = function(e) {
            const content = e.target.result;
            self.parseCSV(content);
        };
        
        reader.onerror = function() {
            alert('Failed to read file');
        };
        
        reader.readAsText(file);
    }
    
    /**
     * Parse CSV content
     */
    parseCSV(content) {
        const lines = content.split('\n').filter(line => line.trim());
        if (lines.length < 2) {
            alert('CSV file must have at least a header row and one data row');
            return;
        }
        
        // Parse header
        const headers = lines[0].split(',').map(h => h.trim().replace(/^"|"$/g, ''));
        
        // Parse data rows (first 10 for preview)
        const rows = [];
        for (let i = 1; i < Math.min(11, lines.length); i++) {
            const values = lines[i].split(',').map(v => v.trim().replace(/^"|"$/g, ''));
            const row = {};
            headers.forEach((header, index) => {
                row[header] = values[index] || '';
            });
            rows.push(row);
        }
        
        this.csvData = {
            headers: headers,
            rows: rows,
            totalRows: lines.length - 1
        };
        
        this.showCSVPreview();
        this.showColumnMapping();
    }
    
    /**
     * Show CSV preview
     */
    showCSVPreview() {
        const previewContainer = document.getElementById('csv-preview');
        if (!previewContainer || !this.csvData) return;
        
        let html = `
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-800 mb-2">Preview (first 10 rows of ${this.csvData.totalRows})</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
        `;
        
        this.csvData.headers.forEach(header => {
            html += `<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">${header}</th>`;
        });
        
        html += `
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
        `;
        
        this.csvData.rows.forEach(row => {
            html += '<tr>';
            this.csvData.headers.forEach(header => {
                html += `<td class="px-4 py-2 whitespace-nowrap">${row[header]}</td>`;
            });
            html += '</tr>';
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        previewContainer.innerHTML = html;
        previewContainer.classList.remove('hidden');
    }
    
    /**
     * Show column mapping interface
     */
    showColumnMapping() {
        const mappingContainer = document.getElementById('column-mapping');
        if (!mappingContainer || !this.csvData) return;
        
        const requiredFields = [
            { key: 'email', label: 'Email Address', required: true },
            { key: 'first_name', label: 'First Name', required: false },
            { key: 'last_name', label: 'Last Name', required: false },
            { key: 'company', label: 'Company', required: false }
        ];
        
        let html = `
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-800 mb-2">Map CSV Columns</h4>
                <p class="text-xs text-gray-600">Match your CSV columns to the required fields</p>
            </div>
            <div class="space-y-4">
        `;
        
        requiredFields.forEach(field => {
            html += `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        ${field.label} ${field.required ? '<span class="text-red-500">*</span>' : ''}
                    </label>
                    <select name="mapping_${field.key}" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                            ${field.required ? 'required' : ''}>
                        <option value="">-- Select Column --</option>
            `;
            
            this.csvData.headers.forEach(header => {
                const selected = header.toLowerCase().includes(field.key) ? 'selected' : '';
                html += `<option value="${header}" ${selected}>${header}</option>`;
            });
            
            html += `
                    </select>
                </div>
            `;
        });
        
        html += `
            </div>
        `;
        
        mappingContainer.innerHTML = html;
        mappingContainer.classList.remove('hidden');
        
        // Update recipient count
        this.recipientCount = this.csvData.totalRows;
        this.updateRecipientCountDisplay();
    }
    
    /**
     * Initialize real-time recipient count
     */
    initRecipientCount() {
        const self = this;
        
        // Watch for changes in recipient filters
        const filterInputs = document.querySelectorAll('[name^="filter_"]');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                self.updateRecipientCount();
            });
        });
    }
    
    /**
     * Update recipient count from server
     */
    updateRecipientCount() {
        const recipientSource = document.querySelector('input[name="recipient_source"]:checked');
        if (!recipientSource) return;
        
        if (recipientSource.value === 'csv') {
            // CSV count is already known
            this.updateRecipientCountDisplay();
            return;
        }
        
        if (recipientSource.value === 'registrations') {
            // Fetch count from server
            this.fetchRecipientCount();
        }
    }
    
    /**
     * Fetch recipient count from server
     */
    async fetchRecipientCount() {
        // Skip if endpoint not configured (MVP mode)
        if (!this.options.recipientCountEndpoint) {
            this.recipientCount = 0;
            this.updateRecipientCountDisplay();
            return;
        }
        
        const filters = this.getRecipientFilters();
        
        try {
            const response = await fetch(this.options.recipientCountEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ filters })
            });
            
            if (response.ok) {
                const data = await response.json();
                this.recipientCount = data.count || 0;
                this.updateRecipientCountDisplay();
            }
        } catch (error) {
            console.error('Failed to fetch recipient count:', error);
        }
    }
    
    /**
     * Get recipient filters from form
     */
    getRecipientFilters() {
        const filters = {};
        
        document.querySelectorAll('[name^="filter_"]').forEach(input => {
            const key = input.name.replace('filter_', '');
            if (input.type === 'checkbox') {
                if (input.checked) {
                    filters[key] = input.value;
                }
            } else if (input.value) {
                filters[key] = input.value;
            }
        });
        
        return filters;
    }
    
    /**
     * Update recipient count display
     */
    updateRecipientCountDisplay() {
        const countElements = document.querySelectorAll('.recipient-count');
        countElements.forEach(el => {
            el.textContent = this.recipientCount.toLocaleString();
        });
        
        // Update review summary
        const reviewRecipients = document.getElementById('review-recipients');
        if (reviewRecipients) {
            const source = document.querySelector('input[name="recipient_source"]:checked');
            if (source) {
                const sourceLabel = source.closest('label').querySelector('.font-medium').textContent;
                reviewRecipients.textContent = `${sourceLabel} (${this.recipientCount.toLocaleString()} recipients)`;
            }
        }
    }
    
    /**
     * Update review summary
     */
    updateReviewSummary() {
        // Template
        const selectedTemplate = document.querySelector('input[name="email_template_id"]:checked');
        if (selectedTemplate) {
            const templateCard = selectedTemplate.closest('.template-card');
            const templateName = templateCard.querySelector('h3').textContent;
            const reviewTemplate = document.getElementById('review-template');
            if (reviewTemplate) {
                reviewTemplate.textContent = templateName;
            }
        }
        
        // Campaign name
        const name = document.getElementById('name')?.value || '-';
        const reviewName = document.getElementById('review-name');
        if (reviewName) {
            reviewName.textContent = name;
        }
        
        // Sender
        const senderName = document.getElementById('sender_name')?.value;
        const senderEmail = document.getElementById('sender_email')?.value;
        const reviewSender = document.getElementById('review-sender');
        if (reviewSender) {
            reviewSender.textContent = senderName && senderEmail 
                ? `${senderName} <${senderEmail}>` 
                : '-';
        }
        
        // Recipients
        this.updateRecipientCountDisplay();
        
        // Schedule
        const sendTiming = document.querySelector('input[name="send_timing"]:checked');
        const reviewSchedule = document.getElementById('review-schedule');
        if (sendTiming && reviewSchedule) {
            if (sendTiming.value === 'draft') {
                reviewSchedule.textContent = 'Save as Draft';
            } else if (sendTiming.value === 'scheduled') {
                const scheduledAt = document.getElementById('scheduled_at')?.value;
                if (scheduledAt) {
                    const date = new Date(scheduledAt);
                    reviewSchedule.textContent = date.toLocaleString();
                } else {
                    reviewSchedule.textContent = 'Scheduled (date not set)';
                }
            }
        }
    }
}

// Global instance
let campaignWizard = null;
