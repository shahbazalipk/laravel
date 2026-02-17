# Registration System Design

## Overview
A multi-step registration process that dynamically adapts based on the selected registration category, exhibitor/group selection, and category-specific requirements.

## Registration Flow

### Step 1: Category Selection
**Purpose**: Select registration category and calculate pricing

**Fields**:
- Registration Category (dropdown) - Required
- Display category details:
  - Name & Description
  - Price (base price)
  - VAT Percentage (from category or event settings)
  - Tax Calculation (inclusive/exclusive based on event settings)
  - Valid dates
  - Capacity remaining (if applicable)

**Price Calculation Logic**:
```php
if (event->tax_inclusive) {
    // Price already includes tax
    $displayPrice = $category->price;
    $basePrice = $category->price / (1 + ($category->vat_percentage / 100));
    $taxAmount = $category->price - $basePrice;
} else {
    // Tax is added on top
    $basePrice = $category->price;
    $taxAmount = $category->price * ($category->vat_percentage / 100);
    $displayPrice = $basePrice + $taxAmount;
}
```

**Dynamic Validations**:
- Check if category requires password → Show password field
- Check if category requires membership → Show membership ID field
- Check if category requires professional/student ID → Show ID field
- Check valid_from and valid_to dates
- Check capacity limits

### Step 2: Registration Type Selection
**Purpose**: Determine if registering as individual, exhibitor, or group

**Fields**:
- Registration Type (radio buttons):
  - Individual Registration
  - Exhibitor Registration (if exhibitors exist)
  - Group Registration (if groups exist)

**Conditional Display**:
- If "Exhibitor Registration" selected → Show exhibitor dropdown
- If "Group Registration" selected → Show group dropdown

### Step 3: Personal Information
**Purpose**: Collect attendee personal details

**Fields**:
- Salutation (dropdown: Mr., Mrs., Ms., Dr., Prof.)
- First Name - Required
- Last Name - Required
- Email - Required (with verification if event settings require)
- Phone - Required
- Mobile Phone
- Job Title
- Department

**Conditional Fields** (based on category):
- Professional/Student ID (if `need_professional_student_id` = true)
- Membership ID (if `need_membership_id` = true)
  - Validate against membership table
  - Show error message if not found or invalid
- Category Password (if `needs_password` = true)
  - Validate against category password

### Step 4: Company Information
**Purpose**: Collect company/organization details

**Fields**:
- Company Name - Required
- Industry (dropdown from industries table) - Required
- Business Activity (dropdown from business_activities table)
- Company Size (dropdown: 1-10, 11-50, 51-200, 201-500, 501-1000, 1000+)
- Company Website
- Company Address
- City
- State/Province
- Postal Code
- Country (dropdown)
- Tax Registration Number (TRN) - Conditional (if category `show_trn` = true)

### Step 5: Additional Information
**Purpose**: Collect event-specific and optional information

**Fields**:
- Dietary Requirements (textarea)
- Special Needs/Accessibility Requirements (textarea)
- T-Shirt Size (dropdown: XS, S, M, L, XL, XXL, XXXL)
- How did you hear about us? (dropdown)
- Areas of Interest (checkboxes - from category types)
- Marketing Consent (checkbox)
- Terms & Conditions Agreement (checkbox) - Required

### Step 6: Review & Payment
**Purpose**: Review all information and proceed to payment

**Display**:
- Summary of all entered information
- Price breakdown:
  - Base Price
  - Tax Amount (VAT)
  - Total Amount
- Edit buttons for each section
- Payment method selection
- Submit button

## Database Schema

### registrations Table
```sql
CREATE TABLE registrations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL,
    org_id BIGINT NOT NULL,
    
    -- Category & Type
    registration_category_id BIGINT NOT NULL,
    registration_status_id BIGINT,
    registration_type ENUM('individual', 'exhibitor', 'group') DEFAULT 'individual',
    exhibitor_id BIGINT NULL,
    group_id BIGINT NULL,
    
    -- Personal Information
    salutation VARCHAR(10),
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    mobile_phone VARCHAR(50),
    job_title VARCHAR(255),
    department VARCHAR(255),
    
    -- Category-specific fields
    professional_student_id VARCHAR(255),
    membership_id VARCHAR(255),
    membership_validated BOOLEAN DEFAULT FALSE,
    
    -- Company Information
    company_name VARCHAR(255) NOT NULL,
    industry_id BIGINT,
    business_activity_id BIGINT,
    company_size VARCHAR(50),
    company_website VARCHAR(500),
    company_address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100),
    tax_registration_number VARCHAR(100),
    
    -- Additional Information
    dietary_requirements TEXT,
    special_needs TEXT,
    tshirt_size VARCHAR(10),
    how_did_you_hear VARCHAR(255),
    areas_of_interest JSON,
    marketing_consent BOOLEAN DEFAULT FALSE,
    terms_accepted BOOLEAN DEFAULT FALSE,
    terms_accepted_at TIMESTAMP,
    
    -- Pricing
    base_price DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10),
    
    -- Payment
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_reference VARCHAR(255),
    payment_date TIMESTAMP NULL,
    
    -- Badge & QR
    badge_number VARCHAR(50) UNIQUE,
    qr_code TEXT,
    badge_printed BOOLEAN DEFAULT FALSE,
    badge_printed_at TIMESTAMP NULL,
    
    -- Check-in
    checked_in BOOLEAN DEFAULT FALSE,
    checked_in_at TIMESTAMP NULL,
    checked_in_by VARCHAR(255),
    
    -- Email Verification
    email_verified BOOLEAN DEFAULT FALSE,
    email_verified_at TIMESTAMP NULL,
    email_verification_token VARCHAR(255),
    
    -- Code Verification (if required)
    verification_code VARCHAR(10),
    code_verified BOOLEAN DEFAULT FALSE,
    code_verified_at TIMESTAMP NULL,
    
    -- System
    registration_number VARCHAR(50) UNIQUE,
    registration_source VARCHAR(50) DEFAULT 'web',
    ip_address VARCHAR(45),
    user_agent TEXT,
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_event_org (event_id, org_id),
    INDEX idx_category (registration_category_id),
    INDEX idx_email (email),
    INDEX idx_status (registration_status_id),
    INDEX idx_payment (payment_status),
    INDEX idx_exhibitor (exhibitor_id),
    INDEX idx_group (group_id),
    INDEX idx_badge (badge_number),
    INDEX idx_registration_number (registration_number),
    
    -- Foreign Keys
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (registration_category_id) REFERENCES registration_categories(id),
    FOREIGN KEY (registration_status_id) REFERENCES registration_statuses(id),
    FOREIGN KEY (exhibitor_id) REFERENCES exhibitors(id),
    FOREIGN KEY (group_id) REFERENCES event_groups(id),
    FOREIGN KEY (industry_id) REFERENCES industries(id),
    FOREIGN KEY (business_activity_id) REFERENCES business_activities(id)
);
```

## Frontend Implementation

### Multi-Step Form Structure
```blade
<!-- Step Indicator -->
<div class="step-indicator">
    <div class="step active">1. Category</div>
    <div class="step">2. Type</div>
    <div class="step">3. Personal</div>
    <div class="step">4. Company</div>
    <div class="step">5. Additional</div>
    <div class="step">6. Review</div>
</div>

<!-- Form Steps -->
<form id="registration-form">
    <div class="step-content active" data-step="1">
        <!-- Category Selection -->
    </div>
    
    <div class="step-content" data-step="2">
        <!-- Registration Type -->
    </div>
    
    <!-- ... more steps ... -->
    
    <!-- Navigation Buttons -->
    <div class="form-navigation">
        <button type="button" class="btn-prev">Previous</button>
        <button type="button" class="btn-next">Next</button>
        <button type="submit" class="btn-submit" style="display:none;">Submit</button>
    </div>
</form>
```

### JavaScript Features
1. **Step Navigation**: Next/Previous with validation
2. **Dynamic Field Display**: Show/hide based on category requirements
3. **Real-time Price Calculation**: Update total as user selects options
4. **Form Validation**: Client-side validation before moving to next step
5. **Progress Saving**: Save form data to session/localStorage
6. **AJAX Category Loading**: Load category details dynamically

## Backend Implementation

### Controllers
- `RegistrationController` (Public)
  - `showRegistrationForm()` - Display multi-step form
  - `getCategoryDetails()` - AJAX endpoint for category info
  - `validateStep()` - AJAX endpoint for step validation
  - `store()` - Process registration submission
  - `confirmEmail()` - Email verification endpoint
  - `verifyCode()` - Code verification endpoint

- `Admin\RegistrationController` (Admin)
  - `index()` - List all registrations
  - `show()` - View registration details
  - `edit()` - Edit registration
  - `update()` - Update registration
  - `destroy()` - Delete registration
  - `checkIn()` - Mark as checked in
  - `printBadge()` - Generate badge
  - `export()` - Export registrations
  - `sendEmail()` - Resend confirmation email

### Services
- `RegistrationService`
  - `calculatePrice()` - Calculate pricing with tax
  - `validateCategory()` - Check category requirements
  - `validateMembership()` - Verify membership ID
  - `generateBadgeNumber()` - Create unique badge number
  - `generateQRCode()` - Generate QR code for badge
  - `sendConfirmationEmail()` - Send email with details
  - `sendVerificationEmail()` - Send email verification
  - `generateVerificationCode()` - Create verification code

### Validation Rules
```php
// Step 1: Category
'registration_category_id' => 'required|exists:registration_categories,id',
'category_password' => 'required_if:category.needs_password,true',

// Step 2: Type
'registration_type' => 'required|in:individual,exhibitor,group',
'exhibitor_id' => 'required_if:registration_type,exhibitor|exists:exhibitors,id',
'group_id' => 'required_if:registration_type,group|exists:event_groups,id',

// Step 3: Personal
'salutation' => 'nullable|string|max:10',
'first_name' => 'required|string|max:255',
'last_name' => 'required|string|max:255',
'email' => 'required|email|unique:registrations,email',
'phone' => 'required|string|max:50',
'professional_student_id' => 'required_if:category.need_professional_student_id,true',
'membership_id' => 'required_if:category.need_membership_id,true',

// Step 4: Company
'company_name' => 'required|string|max:255',
'industry_id' => 'required|exists:industries,id',
'business_activity_id' => 'nullable|exists:business_activities,id',
'tax_registration_number' => 'required_if:category.show_trn,true',

// Step 5: Additional
'terms_accepted' => 'required|accepted',
```

## User Experience Enhancements

1. **Auto-save Progress**: Save form data every 30 seconds
2. **Field Pre-population**: If exhibitor/group selected, pre-fill company info
3. **Smart Validation**: Validate fields as user types
4. **Price Preview**: Show running total in sidebar
5. **Mobile Responsive**: Optimize for mobile registration
6. **Accessibility**: WCAG compliant form elements
7. **Multi-language**: Support for multiple languages
8. **Confirmation Page**: Show success message with registration details
9. **Email Confirmation**: Send detailed confirmation email
10. **Calendar Integration**: Add event to calendar option

## Admin Features

1. **Registration Dashboard**: Overview of registrations by status
2. **Advanced Filters**: Filter by category, status, date, type
3. **Bulk Actions**: Bulk check-in, email, export
4. **Badge Printing**: Print individual or bulk badges
5. **QR Code Scanning**: Mobile app for check-in
6. **Payment Tracking**: Track payment status
7. **Email Management**: Resend confirmations, custom emails
8. **Reports**: Registration reports, revenue reports
9. **Capacity Management**: Track category capacity
10. **Waitlist**: Manage waitlist for full categories

## Security Considerations

1. **CSRF Protection**: All forms protected
2. **Rate Limiting**: Prevent spam registrations
3. **Email Verification**: Optional email verification
4. **Code Verification**: Optional SMS/email code
5. **Password Protection**: Category-level passwords
6. **Membership Validation**: Verify membership status
7. **Data Encryption**: Sensitive data encrypted
8. **Audit Logging**: Track all registration changes
9. **IP Tracking**: Record IP addresses
10. **Duplicate Prevention**: Check for duplicate emails

## Next Steps

1. Create registrations migration
2. Create Registration model with relationships
3. Create RegistrationService with business logic
4. Create public registration form (multi-step)
5. Create admin registration management
6. Implement email notifications
7. Implement badge generation
8. Create QR code system
9. Build check-in functionality
10. Create reporting system
