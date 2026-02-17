# Laravel Event Manager

A comprehensive event management platform built with Laravel, featuring multi-tenant architecture, email campaigns, and complete registration management.

## Features

### Core Event Management
- **Event Registration System** - Multi-step registration forms with validation
- **Admin Dashboard** - Comprehensive management interface
- **Multi-tenant Architecture** - Event and organization scoping with data isolation
- **Hash-based Security** - Secure URLs without exposing database IDs

### Email Campaign System
- **Template Builder** - Rich email templates with merge codes
- **Campaign Management** - Draft, schedule, and send campaigns
- **Multiple Providers** - Support for SMTP, Mailchimp, and Infobip
- **Recipient Management** - CSV upload and segmentation
- **Analytics** - Track opens, clicks, and deliveries
- **Unsubscribe Management** - Automated unsubscribe handling

### Registration Management
- **Category System** - Multiple registration types and categories
- **Status Tracking** - Pending, confirmed, checked-in, cancelled
- **Check-in System** - QR code scanning and manual check-in
- **Badge Printing** - Customizable badge templates
- **Export Functionality** - CSV/Excel export with filters

### Agenda & Sessions
- **Track Management** - Multiple tracks with color coding
- **Session Scheduling** - Time slots and locations
- **Speaker Management** - Speaker profiles and bios
- **Lecture System** - Detailed session information

### Exhibitor Management
- **Exhibitor Profiles** - Company information and booth details
- **Tags & Categories** - Product types and industry classifications
- **Booth Types** - Different booth sizes and configurations
- **Featured Exhibitors** - Highlight premium exhibitors

### Additional Features
- **Sponsor & Partner Management** - Tiered sponsorship levels
- **File Manager** - Upload and organize event files
- **Group Management** - VIP groups and special access
- **Membership System** - Membership codes and validation

## Technology Stack

- **Framework**: Laravel 11.x
- **Frontend**: Blade Templates + Tailwind CSS
- **Database**: MySQL
- **Queue**: Database driver (configurable)
- **Testing**: Pest PHP with property-based testing
- **Email**: Multiple provider support

## Installation

### Requirements
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & NPM

### Setup

1. Clone the repository
```bash
git clone <repository-url>
cd laravel-event-manager
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Update `.env` with your database and event configuration
```env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

EVENT_ID=18
ORG_ID=5
```

5. Run migrations and seeders
```bash
php artisan migrate
php artisan db:seed
```

6. Start the development server
```bash
php artisan serve
```

7. Access the application
- Public: http://localhost:8000
- Admin: http://localhost:8000/admin/login
  - Email: admin@event.com
  - Password: password

## Project Structure

```
app/
├── Console/Commands/      # Artisan commands
├── Contracts/            # Interfaces
├── Http/
│   ├── Controllers/
│   │   ├── Admin/       # Admin controllers
│   │   └── ...          # Public controllers
│   └── Middleware/      # Custom middleware
├── Jobs/                # Queue jobs
├── Models/              # Eloquent models
├── Services/            # Business logic layer
└── Traits/              # Reusable traits

database/
├── migrations/          # Database migrations
└── seeders/            # Database seeders

resources/
└── views/
    ├── admin/          # Admin panel views
    ├── event/          # Public event views
    └── email/          # Email templates

tests/
└── Feature/            # Feature tests
```

## Key Concepts

### Multi-tenant Architecture
All data is automatically scoped by `event_id` and `org_id` using global scopes. This ensures complete data isolation between events and organizations.

### Service Layer Pattern
Business logic is encapsulated in service classes, keeping controllers thin and focused on HTTP concerns.

### Hash-based URLs
Database IDs are never exposed in URLs. Instead, unique hashes are generated for secure, non-enumerable routes.

## Configuration

### Event Configuration
Configure your event in `config/event.php` or via environment variables:
- `EVENT_ID` - Your event identifier
- `ORG_ID` - Your organization identifier

### Email Providers
Configure email providers in the admin panel:
- SMTP (standard email)
- Mailchimp (marketing campaigns)
- Infobip (transactional email)

## Testing

Run the test suite:
```bash
php artisan test
```

Run specific test file:
```bash
php artisan test --filter EmailCampaignTest
```

## Documentation

Detailed documentation is available in the `docs/` directory:
- [Registration System](docs/REGISTRATION_DESIGN.md)
- [Agenda System](docs/AGENDA_SYSTEM_DESIGN.md)
- [Email Campaigns](.kiro/specs/email-campaigns/design.md)
- [Event Settings](docs/EVENT_SETTINGS_COMPLETE.md)
- [Exhibitor Management](docs/EXHIBITOR_CRUD_COMPLETE.md)

## Security

- Hash-based URL security
- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection in Blade templates
- Authentication middleware for admin routes
- Event/organization data scoping

## License

This project is proprietary software.

## Support

For support, please contact the development team.
