# Ads Management & Email Templates Guide

## Overview
This guide covers the Ads Management system and Email Templates that have been developed under the Settings menu in the admin panel.

## Ads Management System

### Access
- **Admin Menu**: Settings → Ads Management
- **URL**: `http://localhost:8001/admin/ads`

### Features

#### Ad Types
- **Banner**: Large horizontal ads
- **Sidebar**: Vertical ads in sidebars
- **Popup**: Modal/overlay ads
- **Footer**: Ads in footer sections

#### Placement Options
- Home
- Exhibitors
- Sessions
- Speakers
- Agenda
- All Pages

#### Ad Configuration
Each ad can be configured with:
- Title
- Type and Placement
- Image upload (JPG, PNG, GIF - max 2MB)
- HTML content (for text-based ads)
- Link URL and link text
- Open in new tab option
- Display order (lower numbers appear first)
- Start and end dates
- Maximum impressions limit
- Active/Inactive status

#### Tracking & Analytics
- Impressions count
- Clicks count
- CTR (Click-Through Rate) calculation
- Automatic expiration based on:
  - End date
  - Maximum impressions reached

#### Filtering
- Filter by type (banner, sidebar, popup, footer)
- Filter by placement (home, exhibitors, sessions, etc.)
- Filter by status (active, inactive)

#### Stats Dashboard
- Total ads count
- Active ads count
- Total impressions
- Total clicks

### Ad Model Features

#### Scopes
- `active()`: Returns only active ads within date range and impression limits
- `byPlacement($placement)`: Filter by placement
- `byType($type)`: Filter by type

#### Methods
- `incrementImpressions()`: Track ad views
- `incrementClicks()`: Track ad clicks
- `isExpired()`: Check if ad has expired
- `getCtrAttribute()`: Calculate click-through rate

### Files Created
- Migration: `database/migrations/2026_02_19_150358_create_ads_table.php`
- Model: `app/Models/Ad.php`
- Controller: `app/Http/Controllers/Admin/AdController.php`
- Views:
  - `resources/views/admin/ads/index.blade.php`
  - `resources/views/admin/ads/create.blade.php`
  - `resources/views/admin/ads/edit.blade.php`
  - `resources/views/admin/ads/_form.blade.php`

## Email Templates System

### Access
- **Admin Menu**: Settings → Email Templates
- **URL**: `http://localhost:8001/admin/email-campaigns/templates`

### Pre-Built Templates

The system includes 7 pre-built email templates:

#### 1. Registration Submitted
- **Slug**: `registration-submitted`
- **Category**: registration
- **Purpose**: Acknowledgment email sent immediately after registration submission
- **Use Case**: Inform users their registration is being processed

#### 2. Registration Confirmed
- **Slug**: `registration-confirmed`
- **Category**: registration
- **Purpose**: Confirmation email sent after registration is approved/verified
- **Use Case**: Provide registration number and event details

#### 3. Registration Cancelled
- **Slug**: `registration-cancelled`
- **Category**: registration
- **Purpose**: Email sent when a registration is cancelled
- **Use Case**: Confirm cancellation and offer re-registration option

#### 4. Event Invitation
- **Slug**: `event-invitation`
- **Category**: invitation
- **Purpose**: Professional event invitation with registration CTA
- **Use Case**: Invite potential attendees to register

#### 5. Registration Confirmation (Legacy)
- **Slug**: `registration-confirmation`
- **Category**: confirmation
- **Purpose**: Confirmation email with event details
- **Use Case**: Alternative confirmation template

#### 6. Event Reminder
- **Slug**: `event-reminder`
- **Category**: reminder
- **Purpose**: Reminder email to send before the event
- **Use Case**: Remind registered attendees about upcoming event

#### 7. Thank You
- **Slug**: `thank-you`
- **Category**: thank_you
- **Purpose**: Post-event thank you with feedback request
- **Use Case**: Follow up after event completion

### Template Features

#### Merge Codes
All templates support dynamic content using merge codes:
- `{{first_name}}` - Attendee first name
- `{{last_name}}` - Attendee last name
- `{{email}}` - Attendee email
- `{{company}}` - Company name
- `{{registration_number}}` - Registration number
- `{{event_name}}` - Event name
- `{{event_date}}` - Event date
- `{{event_location}}` - Event location
- `{{unsubscribe_url}}` - Unsubscribe link

#### Template Formats
Each template includes:
- HTML version (styled with inline CSS)
- Plain text version (for email clients that don't support HTML)

#### Template Design
- Responsive design
- Professional styling with gradients
- Color-coded by category:
  - Blue: Registration Submitted
  - Green: Confirmed
  - Red: Cancelled
  - Purple: Invitation
  - Orange: Reminder
  - Pink: Thank You

### Seeder

The `EmailTemplateSeeder` automatically creates all templates with:
- Pre-designed HTML layouts
- Matching text versions
- Proper categorization
- Active status by default

To run the seeder:
```bash
php artisan db:seed --class=EmailTemplateSeeder
```

### Files Modified/Created
- Seeder: `database/seeders/EmailTemplateSeeder.php` (updated)
- Model: `app/Models/EmailTemplate.php` (existing)
- Controller: `app/Http/Controllers/Admin/EmailTemplateController.php` (existing)

## Navigation Updates

### Desktop Menu (Settings Dropdown)
- Event Settings
- URLs
- Badge Designs
- Gallery
- Marketing Assets
- **Ads Management** (new)
- **Email Templates** (new)
- File Manager
- Memberships

### Mobile Menu (Settings Section)
Same items as desktop menu, displayed in vertical list format.

## Usage Examples

### Creating an Ad
1. Navigate to Settings → Ads Management
2. Click "Create New Ad"
3. Fill in:
   - Title: "Sponsor Banner"
   - Type: Banner
   - Placement: Home
   - Upload image
   - Add link URL
   - Set display order
   - Set date range (optional)
   - Set max impressions (optional)
4. Check "Active" to enable
5. Click "Create Ad"

### Using Email Templates
1. Navigate to Settings → Email Templates
2. Select a template to edit
3. Customize the content using merge codes
4. Preview the template
5. Use in email campaigns

### Tracking Ad Performance
1. Go to Ads Management
2. View stats dashboard for overview
3. Check individual ad metrics:
   - Impressions count
   - Clicks count
   - CTR percentage
4. Filter by status to see active/inactive ads

## Best Practices

### Ads Management
- Use descriptive titles for easy identification
- Set appropriate display orders (0 = highest priority)
- Use date ranges for time-sensitive campaigns
- Set max impressions to control ad frequency
- Monitor CTR to optimize ad performance
- Use appropriate image sizes for each ad type

### Email Templates
- Always test templates before sending campaigns
- Use merge codes for personalization
- Keep subject lines concise and clear
- Include unsubscribe links (required)
- Maintain consistent branding
- Test both HTML and text versions

## Future Enhancements

### Ads Management
- A/B testing for ads
- Geographic targeting
- User segment targeting
- Advanced analytics dashboard
- Bulk ad operations
- Ad scheduling by time of day

### Email Templates
- Visual template builder
- More merge code options
- Template categories management
- Template versioning
- Multi-language support
- Template preview with real data
