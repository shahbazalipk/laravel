<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - {{ $event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Header -->
            <div class="text-center mb-8">
                @if($event->logo)
                    <img src="{{ storage_public_url($event->logo) }}" alt="{{ $event->title }}" class="h-16 mx-auto mb-4">
                @endif
                <h1 class="text-3xl font-bold text-gray-900">{{ $event->title }}</h1>
                <p class="text-gray-600 mt-2">Event Registration</p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="step-indicator flex-1 text-center" data-step="1">
                        <div class="step-circle active">1</div>
                        <div class="step-label">Category</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator flex-1 text-center" data-step="2">
                        <div class="step-circle">2</div>
                        <div class="step-label">Type</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator flex-1 text-center" data-step="3">
                        <div class="step-circle">3</div>
                        <div class="step-label">Personal</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator flex-1 text-center" data-step="4">
                        <div class="step-circle">4</div>
                        <div class="step-label">Company</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator flex-1 text-center" data-step="5">
                        <div class="step-circle">5</div>
                        <div class="step-label">Additional</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator flex-1 text-center" data-step="6">
                        <div class="step-circle">6</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>
            </div>

            <!-- Form Container -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <form id="registration-form" action="{{ route('registration.store') }}" method="POST">
                    @csrf

                    <!-- Step 1: Category Selection -->
                    <div class="step-content active" data-step="1">
                        @include('event.registration.step1-category')
                    </div>

                    <!-- Step 2: Registration Type -->
                    <div class="step-content hidden" data-step="2">
                        @include('event.registration.step2-type')
                    </div>

                    <!-- Step 3: Personal Information -->
                    <div class="step-content hidden" data-step="3">
                        @include('event.registration.step3-personal')
                    </div>

                    <!-- Step 4: Company Information -->
                    <div class="step-content hidden" data-step="4">
                        @include('event.registration.step4-company')
                    </div>

                    <!-- Step 5: Additional Information -->
                    <div class="step-content hidden" data-step="5">
                        @include('event.registration.step5-additional')
                    </div>

                    <!-- Step 6: Review -->
                    <div class="step-content hidden" data-step="6">
                        @include('event.registration.step6-review')
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-8 flex justify-between">
                        <button type="button" id="btn-prev" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition hidden">
                            Previous
                        </button>
                        <div class="flex-1"></div>
                        <button type="button" id="btn-next" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Next
                        </button>
                        <button type="submit" id="btn-submit" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition hidden">
                            Submit Registration
                        </button>
                    </div>
                </form>
            </div>

            <!-- Price Summary Sidebar (Fixed on larger screens) -->
            <div id="price-summary" class="hidden mt-6 bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Price Summary</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Category:</span>
                        <span id="summary-category" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Base Price:</span>
                        <span id="summary-base-price" class="font-medium">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">VAT (<span id="summary-vat-percent">0</span>%):</span>
                        <span id="summary-tax" class="font-medium">-</span>
                    </div>
                    <div class="border-t pt-2 mt-2">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span id="summary-total" class="text-indigo-600">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .step-indicator {
            position: relative;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .step-circle.active {
            background: #4f46e5;
            color: white;
        }
        .step-circle.completed {
            background: #10b981;
            color: white;
        }
        .step-label {
            font-size: 0.875rem;
            color: #6b7280;
        }
        .step-indicator.active .step-label {
            color: #4f46e5;
            font-weight: 600;
        }
        .step-line {
            height: 2px;
            background: #e5e7eb;
            flex: 1;
            margin: 0 8px;
            margin-top: -28px;
        }
        .step-content {
            min-height: 400px;
        }
        .hidden {
            display: none !important;
        }
    </style>

    <script>
        // Registration Form State
        const registrationForm = {
            currentStep: 1,
            totalSteps: 6,
            formData: @json($formData ?? []),
            categoryData: null,
            
            init() {
                this.setupEventListeners();
                this.restoreFormData();
                this.updateUI();
            },
            
            setupEventListeners() {
                document.getElementById('btn-next').addEventListener('click', () => this.nextStep());
                document.getElementById('btn-prev').addEventListener('click', () => this.prevStep());
                document.getElementById('registration-form').addEventListener('submit', (e) => this.handleSubmit(e));
                
                // Category selection
                const categorySelect = document.getElementById('registration_category_id');
                if (categorySelect) {
                    categorySelect.addEventListener('change', (e) => this.loadCategoryDetails(e.target.value));
                }
            },
            
            async nextStep() {
                // Validate current step
                const isValid = await this.validateStep(this.currentStep);
                if (!isValid) return;
                
                // Mark current step as completed
                this.markStepCompleted(this.currentStep);
                
                // Move to next step
                if (this.currentStep < this.totalSteps) {
                    this.currentStep++;
                    this.updateUI();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            
            prevStep() {
                if (this.currentStep > 1) {
                    this.currentStep--;
                    this.updateUI();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },
            
            updateUI() {
                // Update step indicators
                document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                    const stepNum = index + 1;
                    const circle = indicator.querySelector('.step-circle');
                    
                    indicator.classList.remove('active');
                    circle.classList.remove('active', 'completed');
                    
                    if (stepNum === this.currentStep) {
                        indicator.classList.add('active');
                        circle.classList.add('active');
                    } else if (stepNum < this.currentStep) {
                        circle.classList.add('completed');
                        circle.textContent = '✓';
                    } else {
                        circle.textContent = stepNum;
                    }
                });
                
                // Update step content visibility
                document.querySelectorAll('.step-content').forEach((content, index) => {
                    content.classList.toggle('hidden', index + 1 !== this.currentStep);
                    content.classList.toggle('active', index + 1 === this.currentStep);
                });
                
                // Update navigation buttons
                document.getElementById('btn-prev').classList.toggle('hidden', this.currentStep === 1);
                document.getElementById('btn-next').classList.toggle('hidden', this.currentStep === this.totalSteps);
                document.getElementById('btn-submit').classList.toggle('hidden', this.currentStep !== this.totalSteps);
            },
            
            async validateStep(step) {
                const formData = new FormData(document.getElementById('registration-form'));
                formData.append('step', step);
                
                try {
                    const response = await fetch('{{ route("registration.validate-step") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (!response.ok) {
                        this.displayErrors(data.errors);
                        return false;
                    }
                    
                    this.clearErrors();
                    return true;
                } catch (error) {
                    console.error('Validation error:', error);
                    alert('An error occurred. Please try again.');
                    return false;
                }
            },
            
            async loadCategoryDetails(categoryId) {
                if (!categoryId) {
                    document.getElementById('price-summary').classList.add('hidden');
                    return;
                }
                
                try {
                    const response = await fetch(`{{ url('register/category') }}/${categoryId}`);
                    const data = await response.json();
                    
                    this.categoryData = data;
                    this.updatePriceSummary(data);
                    this.updateCategoryFields(data.category);
                } catch (error) {
                    console.error('Error loading category:', error);
                }
            },
            
            updatePriceSummary(data) {
                document.getElementById('price-summary').classList.remove('hidden');
                document.getElementById('summary-category').textContent = data.category.name;
                document.getElementById('summary-base-price').textContent = data.pricing.base_price.toFixed(2) + ' ' + data.pricing.currency;
                document.getElementById('summary-vat-percent').textContent = data.pricing.vat_percentage;
                document.getElementById('summary-tax').textContent = data.pricing.tax_amount.toFixed(2) + ' ' + data.pricing.currency;
                document.getElementById('summary-total').textContent = data.pricing.total_amount.toFixed(2) + ' ' + data.pricing.currency;
            },
            
            updateCategoryFields(category) {
                // Show/hide conditional fields based on category requirements
                this.toggleField('category_password_field', category.needs_password);
                this.toggleField('membership_id_field', category.need_membership_id);
                this.toggleField('professional_student_id_field', category.need_professional_student_id);
                this.toggleField('tax_registration_number_field', category.show_trn);
            },
            
            toggleField(fieldId, show) {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.classList.toggle('hidden', !show);
                    const input = field.querySelector('input, select, textarea');
                    if (input) {
                        input.required = show;
                    }
                }
            },
            
            displayErrors(errors) {
                this.clearErrors();
                Object.keys(errors).forEach(field => {
                    const input = document.querySelector(`[name="${field}"]`);
                    if (input) {
                        input.classList.add('border-red-500');
                        const errorDiv = document.createElement('p');
                        errorDiv.className = 'mt-1 text-sm text-red-600 field-error';
                        errorDiv.textContent = errors[field][0];
                        input.parentElement.appendChild(errorDiv);
                    }
                });
            },
            
            clearErrors() {
                document.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
                document.querySelectorAll('.field-error').forEach(el => el.remove());
            },
            
            markStepCompleted(step) {
                const indicator = document.querySelector(`.step-indicator[data-step="${step}"]`);
                if (indicator) {
                    const circle = indicator.querySelector('.step-circle');
                    circle.classList.add('completed');
                }
            },
            
            restoreFormData() {
                Object.keys(this.formData).forEach(key => {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        if (input.type === 'checkbox') {
                            input.checked = this.formData[key];
                        } else if (input.type === 'radio') {
                            if (input.value === this.formData[key]) {
                                input.checked = true;
                            }
                        } else {
                            input.value = this.formData[key];
                        }
                    }
                });
                
                // Trigger category load if category is selected
                const categoryId = this.formData.registration_category_id;
                if (categoryId) {
                    this.loadCategoryDetails(categoryId);
                }
            },
            
            handleSubmit(e) {
                // Form will submit normally
                // Show loading state
                const submitBtn = document.getElementById('btn-submit');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
            }
        };
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            registrationForm.init();
        });
    </script>
</body>
</html>
