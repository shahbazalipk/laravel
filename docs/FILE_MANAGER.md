# File Manager Module

## Overview
The File Manager module provides a comprehensive file storage and management system for the Laravel Event Manager. It allows administrators to upload, organize, and manage files for their events.

## Features

### File Upload
- Support for any file type up to 50MB
- Automatic file categorization based on MIME type
- Custom display names
- Optional descriptions
- Public/private access control

### File Categories
- **Documents**: PDF, Word, Excel, PowerPoint files
- **Images**: PNG, JPG, GIF, SVG, etc.
- **Videos**: MP4, AVI, MOV, etc.
- **Audio**: MP3, WAV, OGG, etc.
- **Other**: All other file types

### File Management
- View files in a grid layout with category filtering
- Edit file metadata (name, category, description, visibility)
- Download files
- Delete files (with confirmation)
- Soft delete support for recovery

### Security
- Hash-based URLs to prevent ID enumeration
- Event/Organization scoping for data isolation
- Public/private access control
- Admin authentication required

## Database Schema

### `files` Table
- `id`: Primary key
- `event_id`: Event identifier (indexed)
- `org_id`: Organization identifier (indexed)
- `name`: Display name
- `original_name`: Original filename
- `file_path`: Storage path
- `mime_type`: File MIME type (indexed)
- `file_size`: File size in bytes
- `category`: File category (indexed)
- `description`: Optional description
- `is_public`: Public access flag
- `uploaded_by`: Admin who uploaded the file
- `created_at`, `updated_at`: Timestamps
- `deleted_at`: Soft delete timestamp

## Routes

All routes are under the `admin.files` namespace and protected by `event.admin` middleware:

- `GET /admin/files` - List all files (with optional category filter)
- `GET /admin/files/create` - Show upload form
- `POST /admin/files` - Upload new file
- `GET /admin/files/{file}/edit` - Edit file metadata
- `PUT /admin/files/{file}` - Update file metadata
- `DELETE /admin/files/{file}` - Delete file
- `GET /admin/files/{file}/download` - Download file

## Usage

### Uploading Files
1. Navigate to Settings > File Manager
2. Click "Upload File"
3. Select a file (up to 50MB)
4. Optionally provide:
   - Custom display name
   - Category (auto-detected if not specified)
   - Description
   - Public access flag
5. Click "Upload File"

### Managing Files
1. Navigate to Settings > File Manager
2. Use category filters to find specific file types
3. Click "Download" to download a file
4. Click the edit icon to update file metadata
5. Click the delete icon to remove a file (with confirmation)

### Filtering Files
Use the category filter buttons at the top of the page:
- All Files
- Documents
- Images
- Videos
- Audio
- Other

## File Storage

Files are stored in `storage/app/public/files/` with unique UUID-based filenames to prevent conflicts. The public storage is symlinked to `public/storage` for web access.

### Storage Setup
```bash
php artisan storage:link
```

## Audit Logging

All file operations are logged to the `activity_logs` table:
- File uploads (created)
- Metadata updates (updated)
- File deletions (deleted)

Each log entry includes:
- Admin who performed the action
- Timestamp
- File details (name, size, category)

## API

### FileService Methods

```php
// Get all files (optionally filtered by category)
$files = $fileService->getAllFiles('image');

// Upload a file
$file = $fileService->uploadFile($uploadedFile, [
    'name' => 'Custom Name',
    'category' => 'document',
    'description' => 'File description',
    'is_public' => true,
]);

// Update file metadata
$file = $fileService->updateFile($file, [
    'name' => 'Updated Name',
    'category' => 'image',
    'description' => 'Updated description',
    'is_public' => false,
]);

// Delete file
$fileService->deleteFile($file);
```

### File Model Attributes

```php
// Formatted file size
$file->file_size_formatted; // "2.5 MB"

// File extension
$file->file_extension; // "pdf"

// Download URL
$file->download_url; // "/admin/files/{hash}/download"

// Uploader relationship
$file->uploader; // Admin model
```

## Testing

Run the seeder to create sample files for testing:

```bash
php artisan db:seed --class=FileSeeder
```

This creates 5 sample files across different categories.

## Security Considerations

1. **File Size Limit**: Maximum 50MB per file (configurable in controller validation)
2. **MIME Type Validation**: Files are validated by Laravel's file validation
3. **Hash-Based URLs**: All file URLs use hashes instead of database IDs
4. **Event Scoping**: Files are automatically scoped to the current event/organization
5. **Authentication**: All routes require admin authentication
6. **Soft Deletes**: Deleted files can be recovered if needed

## Future Enhancements

Potential improvements for future versions:
- Bulk file upload
- File preview for images and PDFs
- File search functionality
- File versioning
- File sharing with expiration dates
- Storage quota management
- Cloud storage integration (S3, etc.)
- Image optimization and thumbnail generation
