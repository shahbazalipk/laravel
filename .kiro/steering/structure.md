# Project Structure

## Directory Organization

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── EventController.php          # Public event routes
│   │   └── Admin/                       # Admin controllers
│   │       ├── DashboardController.php
│   │       ├── RegistrationController.php
│   │       ├── CategoryController.php
│   │       └── AgendaController.php
│   └── Middleware/
│       └── EventAdmin.php               # Admin authentication
├── Models/
│   ├── Registration.php
│   ├── Category.php
│   ├── AgendaItem.php
│   └── HashMapping.php
├── Services/                            # Business logic layer
│   ├── RegistrationService.php
│   ├── CategoryService.php
│   ├── AgendaService.php
│   └── HashService.php
└── Traits/
    ├── HasEventScope.php                # Automatic event/org filtering
    └── HasHashedRoutes.php              # Hash-based URL security

config/
└── event.php                            # Event configuration

resources/
└── views/
    ├── event/                           # Public views
    │   ├── landing.blade.php
    │   └── register.blade.php
    ├── admin/                           # Admin views
    │   ├── dashboard.blade.php
    │   ├── registrations/
    │   ├── categories/
    │   └── agenda/
    └── components/                      # Reusable Blade components
        ├── event-card.blade.php
        ├── category-badge.blade.php
        └── agenda-item.blade.php

database/
└── migrations/
    ├── xxxx_create_hash_mappings_table.php
    ├── xxxx_create_registrations_table.php
    ├── xxxx_create_categories_table.php
    └── xxxx_create_agenda_items_table.php

tests/
├── Unit/                                # Unit tests
│   ├── Models/
│   ├── Services/
│   ├── Traits/
│   └── Middleware/
├── Feature/                             # Integration tests
│   ├── PublicRoutesTest.php
│   ├── AdminRoutesTest.php
│   └── *ManagementTest.php
└── Property/                            # Property-based tests
    └── *PropertyTest.php
```

## Architectural Patterns

### Service Layer Pattern
Controllers delegate business logic to service classes. Keep controllers thin - they should only handle HTTP concerns (validation, responses) and delegate to services.

### Global Scopes for Data Isolation
All models use the `HasEventScope` trait to automatically filter queries by event_id and org_id. This ensures data isolation without manual filtering.

### Hash-Based Security
Models use the `HasHashedRoutes` trait to generate unique hashes for URLs instead of exposing database IDs. The `HashService` manages hash generation and resolution.

## Naming Conventions

- **Controllers**: Singular noun + "Controller" (e.g., `RegistrationController`)
- **Services**: Singular noun + "Service" (e.g., `RegistrationService`)
- **Models**: Singular noun (e.g., `Registration`)
- **Migrations**: Snake case with timestamp prefix
- **Routes**: Kebab case (e.g., `/event/admin/registrations`)
- **Views**: Kebab case (e.g., `landing.blade.php`)

## Database Conventions

All tables must include:
- `event_id` column (indexed)
- `org_id` column (indexed)
- Composite index on `['event_id', 'org_id']`
- Standard Laravel timestamps (`created_at`, `updated_at`)

## Route Organization

- **Public routes**: `/event/*` - No authentication required
- **Admin routes**: `/event/admin/*` - Protected by `event.admin` middleware
- Use route groups with prefixes and names for organization
