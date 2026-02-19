# Exhibitor Jobs & Products Management Guide

## Overview
This feature allows exhibitors to showcase job openings and products/services through the admin panel. Attendees can view these on the frontend portal.

## Admin Features

### Accessing Jobs & Products
1. Navigate to **Exhibitors** in the admin panel
2. Click on any exhibitor to view their details
3. Scroll down to see **Job Openings** and **Products & Services** sections

### Managing Job Openings

#### Adding a Job
1. Click **+ Add Job** button in the Job Openings section
2. Fill in the job details:
   - **Job Title** (required)
   - **Job Type**: Full-time, Part-time, Contract, Internship
   - **Experience Level**: Entry, Mid, Senior
   - **Location**: Job location
   - **Salary Range**: e.g., "$50k - $70k"
   - **Description** (required): Detailed job description
   - **Requirements**: Job requirements and qualifications
   - **Application Email**: Where candidates should apply
   - **Application Deadline**: Last date to apply
   - **Active**: Toggle to show/hide the job
3. Click **Save Job**

#### Job Information Displayed
- Job title and type
- Experience level and location
- Salary range
- Full description and requirements
- Application email and deadline
- View count and application tracking

#### Editing/Deleting Jobs
- Click **Edit** to modify job details
- Click **Delete** to remove a job (requires confirmation)

### Managing Products & Services

#### Adding a Product
1. Click **+ Add Product** button in the Products & Services section
2. Fill in the product details:
   - **Product Name** (required)
   - **Category**: Product category
   - **Description** (required): Detailed product description
   - **Product Image**: Upload product photo (JPG, PNG, max 2MB)
   - **Price**: Numeric price value
   - **Price Text**: Custom price text (e.g., "Starting from $99")
   - **Features**: Key product features
   - **Featured**: Mark as featured product
   - **New Product**: Mark as new
   - **Active**: Toggle to show/hide the product
3. Click **Save Product**

#### Product Information Displayed
- Product name and image
- Category and pricing
- Description and features
- Featured/New badges
- View count and inquiry tracking

#### Editing/Deleting Products
- Click **Edit** to modify product details
- Click **Delete** to remove a product (requires confirmation)

## Attendee Frontend View

### Viewing Jobs
When attendees visit an exhibitor's detail page, they will see:
- All active job openings
- Job type, experience level, location badges
- Salary range (if provided)
- Job description and requirements
- Application deadline
- **Apply Now** button (links to application email)

### Viewing Products
Attendees can browse:
- Product images and names
- Category and pricing information
- Product descriptions and features
- Featured and New badges
- **Inquire Now** button (sends email to exhibitor)

### Dedicated Jobs & Products Pages
Attendees can access dedicated pages from the navigation menu:

#### Jobs Page (`/attendee/jobs`)
- Browse all job openings from all exhibitors
- Filter by:
  - Job Type (Full-time, Part-time, Contract, Internship)
  - Experience Level (Entry, Mid, Senior)
  - Search by title, description, or location
- View job details including:
  - Company logo and name
  - Job type, experience level, location
  - Salary range and deadline
  - Full description and requirements
  - View count and application count
- Apply directly via email

#### Products Page (`/attendee/products`)
- Browse all products and services from all exhibitors
- Filter by:
  - Category
  - Featured products
  - New products
  - Search by name, description, or category
- View product details including:
  - Product images
  - Company name
  - Category and pricing
  - Description and key features
  - View count and inquiry count
- Inquire directly via email

## Technical Details

### Database Tables
- `exhibitor_jobs`: Stores job openings
- `exhibitor_products`: Stores products/services

### Key Fields

#### Jobs Table
- `title`, `description`, `location`
- `job_type`, `experience_level`, `salary_range`
- `requirements`, `application_email`, `deadline`
- `views_count`, `applications_count`
- `is_active`

#### Products Table
- `name`, `description`, `category`
- `image`, `price`, `price_text`
- `features`, `specifications`
- `is_featured`, `is_new`, `is_active`
- `views_count`, `inquiries_count`

### Routes
- `POST /admin/exhibitor-jobs` - Create job
- `PUT /admin/exhibitor-jobs/{id}` - Update job
- `DELETE /admin/exhibitor-jobs/{id}` - Delete job
- `POST /admin/exhibitor-products` - Create product
- `PUT /admin/exhibitor-products/{id}` - Update product
- `DELETE /admin/exhibitor-products/{id}` - Delete product
- `GET /attendee/jobs` - Browse all jobs (attendee view)
- `GET /attendee/products` - Browse all products (attendee view)
- `GET /attendee/exhibitors/{id}` - View exhibitor with jobs and products

### Controllers
- `App\Http\Controllers\Admin\ExhibitorJobController`
- `App\Http\Controllers\Admin\ExhibitorProductController`

### Models
- `App\Models\ExhibitorJob`
- `App\Models\ExhibitorProduct`

## Best Practices

### For Jobs
1. Write clear, detailed job descriptions
2. Include specific requirements and qualifications
3. Set realistic application deadlines
4. Provide a valid application email
5. Update job status when position is filled

### For Products
1. Use high-quality product images
2. Write compelling product descriptions
3. Highlight key features and benefits
4. Keep pricing information up to date
5. Mark special products as Featured or New

## Tips
- Jobs and products are only visible to attendees when marked as "Active"
- Use the Featured flag to highlight important products
- Track views and applications/inquiries to measure interest
- Update or remove outdated jobs and products regularly
- Provide complete contact information for inquiries

## Support
For technical issues or questions, contact the system administrator.
