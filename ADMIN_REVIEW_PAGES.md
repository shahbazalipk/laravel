# Admin Review Pages for Exhibitor Jobs and Products

## Overview
Admin pages have been created to allow the admin team to review and manage all jobs and products uploaded by exhibitors before they are published to attendees.

## Access Points

### Navigation
The admin can access these pages from the main navigation:

1. **Desktop Menu**: Exhibitors dropdown → Products / Jobs
2. **Mobile Menu**: Exhibitors section → Products / Jobs

### Direct URLs
- Products: `http://localhost:8001/admin/exhibitor-products`
- Jobs: `http://localhost:8001/admin/exhibitor-jobs`

## Features

### Products Review Page (`/admin/exhibitor-products`)

#### Filter Tabs
Three filter tabs allow quick filtering:
- **All Products**: Shows all products regardless of status (with total count badge)
- **Active**: Shows only active products visible to attendees (with count badge)
- **Inactive**: Shows only inactive products hidden from attendees (with count badge)

Each tab displays the count of items in that category and highlights when selected.

#### Stats Dashboard
- Total Products count (in current view)
- Active products count (total)
- Inactive products count (total)
- Featured products count (in current view)

#### Product Listing
Each product displays:
- Product image (or placeholder if no image)
- Product name and description (truncated)
- Featured/New badges
- Exhibitor company name (clickable link)
- Category
- Price (or price text)
- View and inquiry statistics
- Active/Inactive status toggle

#### Actions
- **Toggle Status**: Click the status badge to activate/deactivate
- **View Exhibitor**: Navigate to exhibitor detail page
- **Delete**: Remove product with confirmation

### Jobs Review Page (`/admin/exhibitor-jobs`)

#### Filter Tabs
Three filter tabs allow quick filtering:
- **All Jobs**: Shows all jobs regardless of status (with total count badge)
- **Active**: Shows only active jobs visible to attendees (with count badge)
- **Inactive**: Shows only inactive jobs hidden from attendees (with count badge)

Each tab displays the count of items in that category and highlights when selected.

#### Stats Dashboard
- Total Jobs count (in current view)
- Active jobs count (total)
- Inactive jobs count (total)
- Total Applications count (in current view)

#### Job Listing
Each job displays:
- Job title
- Exhibitor company name (clickable link)
- Job type, experience level, location, salary range badges
- Description (truncated to 200 chars)
- Requirements (truncated to 150 chars)
- View and application statistics
- Deadline date (if set)
- Active/Inactive status toggle

#### Actions
- **Toggle Status**: Click the status badge to activate/deactivate
- **View Exhibitor**: Navigate to exhibitor detail page
- **Delete**: Remove job with confirmation

## Status Management

### Active vs Inactive
- **Active**: Job/Product is visible to attendees on the frontend
- **Inactive**: Job/Product is hidden from attendees (draft/review state)

### Toggle Functionality
Admins can quickly toggle the status by clicking the status badge:
- Green badge = Active
- Gray badge = Inactive

## Filtering

### URL Parameters
The filter tabs use URL query parameters to maintain state:
- `?status=all` - Shows all items (default)
- `?status=active` - Shows only active items
- `?status=inactive` - Shows only inactive items

### Pagination
Pagination links preserve the selected filter, so navigating between pages maintains the current view.

## Related Files

### Controllers
- `app/Http/Controllers/Admin/ExhibitorProductController.php`
- `app/Http/Controllers/Admin/ExhibitorJobController.php`

### Views
- `resources/views/admin/exhibitor-products/index.blade.php`
- `resources/views/admin/exhibitor-jobs/index.blade.php`

### Routes
- `routes/web.php` (admin.exhibitor-products.*, admin.exhibitor-jobs.*)

### Models
- `app/Models/ExhibitorProduct.php`
- `app/Models/ExhibitorJob.php`

## Workflow

1. Exhibitor adds a job or product from their exhibitor detail page
2. Job/Product is created with `is_active = false` by default (or as set)
3. Admin reviews the job/product on these dedicated pages
4. Admin can filter by status to focus on items needing review
5. Admin can toggle status to activate or keep inactive
6. Only active jobs/products appear on attendee-facing pages:
   - `/attendee/jobs`
   - `/attendee/products`
   - `/attendee/exhibitors/{exhibitor}` (exhibitor detail page)
