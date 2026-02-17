# Sponsors & Partners Module Setup

## Completed Files

### Database
✅ `database/migrations/2026_02_16_130718_create_sponsors_table.php`
✅ `database/migrations/2026_02_16_130726_create_partners_table.php`
✅ Migrations run successfully

### Models
✅ `app/Models/Sponsor.php`
✅ `app/Models/Partner.php`
✅ `app/Traits/HasSponsorshipFeatures.php` (shared trait)

### Services
✅ `app/Services/SponsorService.php`
✅ `app/Services/PartnerService.php`

### Controllers
✅ `app/Http/Controllers/Admin/SponsorController.php`
✅ `app/Http/Controllers/Admin/PartnerController.php`

### Views
✅ `resources/views/admin/sponsors/index.blade.php`

## Remaining Files Needed

### Routes (add to routes/web.php)
```php
// Sponsors
Route::resource('sponsors', SponsorController::class);
Route::post('sponsors/{sponsor}/toggle', [SponsorController::class, 'toggleActive'])
    ->name('sponsors.toggle');

// Partners
Route::resource('partners', PartnerController::class);
Route::post('partners/{partner}/toggle', [PartnerController::class, 'toggleActive'])
    ->name('partners.toggle');
```

### Views to Create

1. **resources/views/admin/sponsors/create.blade.php**
2. **resources/views/admin/sponsors/edit.blade.php**
3. **resources/views/admin/partners/index.blade.php** (copy from sponsors/index.blade.php and replace "sponsor" with "partner")
4. **resources/views/admin/partners/create.blade.php** (copy from sponsors/create.blade.php)
5. **resources/views/admin/partners/edit.blade.php** (copy from sponsors/edit.blade.php)

### Navigation (update resources/views/admin/layout.blade.php)

Add under Parameters dropdown:
```blade
<a href="{{ route('admin.sponsors.index') }}" class="block px-3 py-2 text-sm rounded hover:bg-indigo-800">Sponsors</a>
<a href="{{ route('admin.partners.index') }}" class="block px-3 py-2 text-sm rounded hover:bg-indigo-800">Partners</a>
```

## Form Fields Structure

### Visibility Checkboxes
- Active
- Visible on e-badge
- Visible on Exhibitor Portal
- Visible on Group Portal
- Visible Online
- Visible Onsite

### Basic Information
- Name * (required)
- Sponsorship Label * (required)
- Type * (required) - e.g., Platinum, Gold, Silver, Bronze
- Description (textarea)

### Logo Uploads
- Logo Thumbnail (image, JPG/PNG, min 400x400)
- Logo Defined Size (image, JPG/PNG, min 400x400)

### Additional Fields
- Website URL
- Contact Email
- Contact Phone
- Sort Order (integer)

## Features Implemented

1. **Full CRUD Operations** - Create, Read, Update, Delete
2. **Toggle Active Status** - Quick enable/disable
3. **Logo Management** - Upload, display, and delete logos
4. **Visibility Controls** - Multiple visibility flags for different platforms
5. **Audit Logging** - All operations logged
6. **Hash-based URLs** - Security through obscurity
7. **Event/Org Scoping** - Automatic data isolation
8. **Soft Deletes** - Recovery option
9. **Sort Order** - Custom ordering
10. **Responsive Design** - Mobile-friendly UI

## Sponsor/Partner Types

### Suggested Sponsor Types
- Platinum
- Gold
- Silver
- Bronze
- Title Sponsor
- Presenting Sponsor
- Supporting Sponsor

### Suggested Partner Types
- Strategic Partner
- Technology Partner
- Media Partner
- Community Partner
- Academic Partner
- Association Partner

## Next Steps

1. Add routes to `routes/web.php`
2. Create remaining view files (create.blade.php, edit.blade.php)
3. Update navigation in layout.blade.php
4. Create seeders for sample data (optional)
5. Test all CRUD operations
6. Test logo uploads
7. Test visibility toggles

## Sample Seeder Structure

```php
Sponsor::create([
    'name' => 'Tech Corp',
    'sponsorship_label' => 'Platinum Sponsor',
    'type' => 'Platinum',
    'description' => 'Leading technology company',
    'website_url' => 'https://techcorp.com',
    'visible_online' => true,
    'visible_onsite' => true,
    'is_active' => true,
    'sort_order' => 1,
]);
```
