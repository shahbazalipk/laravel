# Technology Stack

## Framework & Language

- **PHP**: Laravel framework
- **Frontend**: Laravel Blade templating engine
- **Database**: MySQL (connects to existing SaaS database)

## Key Libraries & Tools

- **Testing**: Pest PHP with property-based testing support
- **ORM**: Eloquent ORM with global scopes
- **Authentication**: Laravel's built-in authentication system

## Configuration

Event and organization identifiers are loaded from environment variables:

```env
EVENT_ID=<event_identifier>
ORG_ID=<organization_identifier>
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas_database
DB_USERNAME=username
DB_PASSWORD=password
```

## Common Commands

### Development
```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Create new migration
php artisan make:migration create_table_name

# Create new controller
php artisan make:controller ControllerName

# Create new model
php artisan make:model ModelName
```

### Testing
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter TestClassName

# Run tests with coverage
php artisan test --coverage
```

### Database
```bash
# Seed database
php artisan db:seed

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Check database connection
php artisan db:show
```

## Testing Strategy

- **Unit Tests**: Specific examples and edge cases
- **Property-Based Tests**: Universal properties across all inputs (minimum 100 iterations)
- **Integration Tests**: End-to-end flows and authentication
- All property tests must reference their design document property using comment tags
