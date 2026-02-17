<h2 class="text-xl font-bold text-gray-800 mb-6">Review Your Registration</h2>
<p class="text-gray-600 mb-6">Please review all information before submitting your registration.</p>

<!-- Category Information -->
<div class="mb-6 bg-gray-50 rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Category Information</h3>
        <button type="button" onclick="registrationForm.currentStep = 1; registrationForm.updateUI();" class="text-indigo-600 hover:text-indigo-800 text-sm">
            Edit
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <span class="text-sm text-gray-600">Category:</span>
            <p class="font-medium" id="review-category">-</p>
        </div>
        <div id="review-category-password-section" class="hidden">
            <span class="text-sm text-gray-600">Category Password:</span>
            <p class="font-medium">••••••••</p>
        </div>
        <div id="review-membership-section" class="hidden">
            <span class="text-sm text-gray-600">Membership ID:</span>
            <p class="font-medium" id="review-membership-id">-</p>
        </div>
        <div id="review-professional-section" class="hidden">
            <span class="text-sm text-gray-600">Professional/Student ID:</span>
            <p class="font-medium" id="review-professional-id">-</p>
        </div>
    </div>
</div>

<!-- Registration Type -->
<div class="mb-6 bg-gray-50 rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Registration Type</h3>
        <button type="button" onclick="registrationForm.currentStep = 2; registrationForm.updateUI();" class="text-indigo-600 hover:text-indigo-800 text-sm">
            Edit
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <span class="text-sm text-gray-600">Type:</span>
            <p class="font-medium" id="review-type">-</p>
        </div>
        <div id="review-exhibitor-section" class="hidden">
            <span class="text-sm text-gray-600">Exhibitor:</span>
            <p class="font-medium" id="review-exhibitor">-</p>
        </div>
        <div id="review-group-section" class="hidden">
            <span class="text-sm text-gray-600">Group:</span>
            <p class="font-medium" id="review-group">-</p>
        </div>
    </div>
</div>

<!-- Personal Information -->
<div class="mb-6 bg-gray-50 rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
        <button type="button" onclick="registrationForm.currentStep = 3; registrationForm.updateUI();" class="text-indigo-600 hover:text-indigo-800 text-sm">
            Edit
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <span class="text-sm text-gray-600">Name:</span>
            <p class="font-medium" id="review-name">-</p>
        </div>
        <div>
            <span class="text-sm text-gray-600">Email:</span>
            <p class="font-medium" id="review-email">-</p>
        </div>
        <div>
            <span class="text-sm text-gray-600">Phone:</span>
            <p class="font-medium" id="review-phone">-</p>
        </div>
        <div id="review-mobile-section">
            <span class="text-sm text-gray-600">Mobile:</span>
            <p class="font-medium" id="review-mobile">-</p>
        </div>
        <div id="review-job-title-section">
            <span class="text-sm text-gray-600">Job Title:</span>
            <p class="font-medium" id="review-job-title">-</p>
        </div>
        <div id="review-department-section">
            <span class="text-sm text-gray-600">Department:</span>
            <p class="font-medium" id="review-department">-</p>
        </div>
    </div>
</div>

<!-- Company Information -->
<div class="mb-6 bg-gray-50 rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Company Information</h3>
        <button type="button" onclick="registrationForm.currentStep = 4; registrationForm.updateUI();" class="text-indigo-600 hover:text-indigo-800 text-sm">
            Edit
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <span class="text-sm text-gray-600">Company Name:</span>
            <p class="font-medium" id="review-company">-</p>
        </div>
        <div id="review-industry-section">
            <span class="text-sm text-gray-600">Industry:</span>
            <p class="font-medium" id="review-industry">-</p>
        </div>
        <div id="review-business-activity-section">
            <span class="text-sm text-gray-600">Business Activity:</span>
            <p class="font-medium" id="review-business-activity">-</p>
        </div>
        <div id="review-company-size-section">
            <span class="text-sm text-gray-600">Company Size:</span>
            <p class="font-medium" id="review-company-size">-</p>
        </div>
        <div id="review-website-section" class="md:col-span-2">
            <span class="text-sm text-gray-600">Website:</span>
            <p class="font-medium" id="review-website">-</p>
        </div>
        <div id="review-address-section" class="md:col-span-2">
            <span class="text-sm text-gray-600">Address:</span>
            <p class="font-medium" id="review-address">-</p>
        </div>
        <div id="review-trn-section">
            <span class="text-sm text-gray-600">Tax Registration Number:</span>
            <p class="font-medium" id="review-trn">-</p>
        </div>
    </div>
</div>

<!-- Additional Information -->
<div class="mb-6 bg-gray-50 rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Additional Information</h3>
        <button type="button" onclick="registrationForm.currentStep = 5; registrationForm.updateUI();" class="text-indigo-600 hover:text-indigo-800 text-sm">
            Edit
        </button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div id="review-dietary-section">
            <span class="text-sm text-gray-600">Dietary Requirements:</span>
            <p class="font-medium" id="review-dietary">-</p>
        </div>
        <div id="review-special-needs-section">
            <span class="text-sm text-gray-600">Special Needs:</span>
            <p class="font-medium" id="review-special-needs">-</p>
        </div>
        <div id="review-tshirt-section">
            <span class="text-sm text-gray-600">T-Shirt Size:</span>
            <p class="font-medium" id="review-tshirt">-</p>
        </div>
        <div id="review-hear-about-section">
            <span class="text-sm text-gray-600">How did you hear about us:</span>
            <p class="font-medium" id="review-hear-about">-</p>
        </div>
        <div id="review-interests-section" class="md:col-span-2">
            <span class="text-sm text-gray-600">Areas of Interest:</span>
            <p class="font-medium" id="review-interests">-</p>
        </div>
    </div>
</div>

<!-- Price Summary -->
<div class="mb-6 bg-indigo-50 border-2 border-indigo-200 rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Price Summary</h3>
    <div class="space-y-3">
        <div class="flex justify-between">
            <span class="text-gray-700">Base Price:</span>
            <span class="font-medium" id="review-base-price">-</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-700">VAT (<span id="review-vat-percent">0</span>%):</span>
            <span class="font-medium" id="review-tax">-</span>
        </div>
        <div class="border-t-2 border-indigo-200 pt-3">
            <div class="flex justify-between text-xl font-bold">
                <span class="text-gray-900">Total Amount:</span>
                <span class="text-indigo-600" id="review-total">-</span>
            </div>
        </div>
    </div>
</div>

<!-- Consent Confirmation -->
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
    <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div class="flex-1">
            <p class="text-sm text-gray-700">
                <span class="font-semibold">Marketing Consent:</span>
                <span id="review-marketing-consent">-</span>
            </p>
            <p class="text-sm text-gray-700 mt-2">
                <span class="font-semibold">Terms & Conditions:</span>
                <span id="review-terms">-</span>
            </p>
        </div>
    </div>
</div>

<script>
    // Populate review data when step 6 is shown
    document.addEventListener('DOMContentLoaded', function() {
        const originalUpdateUI = registrationForm.updateUI;
        registrationForm.updateUI = function() {
            originalUpdateUI.call(this);
            if (this.currentStep === 6) {
                populateReviewData();
            }
        };
    });

    function populateReviewData() {
        const form = document.getElementById('registration-form');
        const formData = new FormData(form);

        // Category Information
        const categorySelect = document.getElementById('registration_category_id');
        if (categorySelect && categorySelect.selectedOptions[0]) {
            document.getElementById('review-category').textContent = categorySelect.selectedOptions[0].text;
        }

        // Conditional category fields
        const categoryPassword = formData.get('category_password');
        if (categoryPassword) {
            document.getElementById('review-category-password-section').classList.remove('hidden');
        }

        const membershipId = formData.get('membership_id');
        if (membershipId) {
            document.getElementById('review-membership-section').classList.remove('hidden');
            document.getElementById('review-membership-id').textContent = membershipId;
        }

        const professionalId = formData.get('professional_student_id');
        if (professionalId) {
            document.getElementById('review-professional-section').classList.remove('hidden');
            document.getElementById('review-professional-id').textContent = professionalId;
        }

        // Registration Type
        const regType = formData.get('registration_type');
        const typeLabels = {
            'individual': 'Individual',
            'exhibitor': 'Exhibitor',
            'group': 'Group'
        };
        document.getElementById('review-type').textContent = typeLabels[regType] || '-';

        if (regType === 'exhibitor') {
            const exhibitorSelect = document.getElementById('exhibitor_id');
            if (exhibitorSelect && exhibitorSelect.selectedOptions[0]) {
                document.getElementById('review-exhibitor-section').classList.remove('hidden');
                document.getElementById('review-exhibitor').textContent = exhibitorSelect.selectedOptions[0].text;
            }
        }

        if (regType === 'group') {
            const groupSelect = document.getElementById('group_id');
            if (groupSelect && groupSelect.selectedOptions[0]) {
                document.getElementById('review-group-section').classList.remove('hidden');
                document.getElementById('review-group').textContent = groupSelect.selectedOptions[0].text;
            }
        }

        // Personal Information
        const salutation = formData.get('salutation');
        const firstName = formData.get('first_name');
        const lastName = formData.get('last_name');
        document.getElementById('review-name').textContent = `${salutation || ''} ${firstName || ''} ${lastName || ''}`.trim() || '-';
        document.getElementById('review-email').textContent = formData.get('email') || '-';
        document.getElementById('review-phone').textContent = formData.get('phone') || '-';
        
        const mobile = formData.get('mobile');
        if (mobile) {
            document.getElementById('review-mobile').textContent = mobile;
        } else {
            document.getElementById('review-mobile-section').classList.add('hidden');
        }

        const jobTitle = formData.get('job_title');
        if (jobTitle) {
            document.getElementById('review-job-title').textContent = jobTitle;
        } else {
            document.getElementById('review-job-title-section').classList.add('hidden');
        }

        const department = formData.get('department');
        if (department) {
            document.getElementById('review-department').textContent = department;
        } else {
            document.getElementById('review-department-section').classList.add('hidden');
        }

        // Company Information
        document.getElementById('review-company').textContent = formData.get('company_name') || '-';

        const industrySelect = document.getElementById('industry_id');
        if (industrySelect && industrySelect.selectedOptions[0]) {
            document.getElementById('review-industry').textContent = industrySelect.selectedOptions[0].text;
        } else {
            document.getElementById('review-industry-section').classList.add('hidden');
        }

        const businessActivitySelect = document.getElementById('business_activity_id');
        if (businessActivitySelect && businessActivitySelect.selectedOptions[0]) {
            document.getElementById('review-business-activity').textContent = businessActivitySelect.selectedOptions[0].text;
        } else {
            document.getElementById('review-business-activity-section').classList.add('hidden');
        }

        const companySize = formData.get('company_size');
        if (companySize) {
            document.getElementById('review-company-size').textContent = companySize;
        } else {
            document.getElementById('review-company-size-section').classList.add('hidden');
        }

        const website = formData.get('company_website');
        if (website) {
            document.getElementById('review-website').textContent = website;
        } else {
            document.getElementById('review-website-section').classList.add('hidden');
        }

        // Address
        const address = formData.get('address');
        const city = formData.get('city');
        const state = formData.get('state');
        const postalCode = formData.get('postal_code');
        const country = formData.get('country');
        const addressParts = [address, city, state, postalCode, country].filter(p => p);
        if (addressParts.length > 0) {
            document.getElementById('review-address').textContent = addressParts.join(', ');
        } else {
            document.getElementById('review-address-section').classList.add('hidden');
        }

        const trn = formData.get('tax_registration_number');
        if (trn) {
            document.getElementById('review-trn').textContent = trn;
        } else {
            document.getElementById('review-trn-section').classList.add('hidden');
        }

        // Additional Information
        const dietary = formData.get('dietary_requirements');
        if (dietary) {
            document.getElementById('review-dietary').textContent = dietary;
        } else {
            document.getElementById('review-dietary-section').classList.add('hidden');
        }

        const specialNeeds = formData.get('special_needs');
        if (specialNeeds) {
            document.getElementById('review-special-needs').textContent = specialNeeds;
        } else {
            document.getElementById('review-special-needs-section').classList.add('hidden');
        }

        const tshirt = formData.get('tshirt_size');
        if (tshirt) {
            document.getElementById('review-tshirt').textContent = tshirt;
        } else {
            document.getElementById('review-tshirt-section').classList.add('hidden');
        }

        const hearAbout = formData.get('how_did_you_hear');
        if (hearAbout) {
            document.getElementById('review-hear-about').textContent = hearAbout;
        } else {
            document.getElementById('review-hear-about-section').classList.add('hidden');
        }

        const interests = formData.get('areas_of_interest');
        if (interests) {
            document.getElementById('review-interests').textContent = interests;
        } else {
            document.getElementById('review-interests-section').classList.add('hidden');
        }

        // Marketing consent
        const marketingConsent = formData.get('marketing_consent');
        document.getElementById('review-marketing-consent').textContent = marketingConsent ? 'Yes, I agree to receive marketing communications' : 'No';

        // Terms
        const termsAccepted = formData.get('terms_accepted');
        document.getElementById('review-terms').textContent = termsAccepted ? 'Accepted' : 'Not Accepted';

        // Price Summary (from sidebar)
        if (registrationForm.categoryData) {
            const pricing = registrationForm.categoryData.pricing;
            document.getElementById('review-base-price').textContent = pricing.base_price.toFixed(2) + ' ' + pricing.currency;
            document.getElementById('review-vat-percent').textContent = pricing.vat_percentage;
            document.getElementById('review-tax').textContent = pricing.tax_amount.toFixed(2) + ' ' + pricing.currency;
            document.getElementById('review-total').textContent = pricing.total_amount.toFixed(2) + ' ' + pricing.currency;
        }
    }
</script>
