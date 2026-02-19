# Marketing Assets Management System

## Overview
A comprehensive marketing assets management system that allows event administrators to create, manage, and distribute promotional materials to attendees for easy sharing and promotion.

## Features

### Admin Features
1. **Asset Management**
   - Create multiple types of marketing assets
   - Upload images/posters
   - Add pre-written captions and text
   - Manage hashtag collections
   - Set social media platform optimization
   - Track downloads and shares

2. **Asset Types**
   - Posters
   - Social Media Posts
   - Hashtag Collections
   - Captions/Text
   - Story Templates
   - Banners
   - Email Signatures

3. **Organization**
   - Category by event phase (pre-event, during-event, post-event)
   - Featured assets
   - Display order control
   - Active/inactive status

4. **Content Management**
   - Image upload (up to 10MB)
   - Caption text (up to 2000 characters)
   - Multiple hashtags
   - Social platform tags
   - Descriptions

### Attendee Features
1. **Marketing Hub**
   - Browse all available marketing materials
   - Filter by type and category
   - View featured assets
   - Download high-quality images
   - Copy captions and hashtags with one click
   - Track usage statistics

2. **Easy Sharing**
   - One-click copy for captions
   - One-click copy for hashtags
   - Download images directly
   - Share tracking
   - Platform-specific materials

## Database Structure

### marketing_assets Table
- Basic info: title, description, type, category
- Content: caption_text, hashtags, image_path
- Metadata: dimensions, social_platforms
- Stats: download_count, share_count
- Settings: order, is_featured, is_active

## Routes

### Admin Routes
- `GET /admin/marketing-assets` - List all assets
- `GET /admin/marketing-assets/create` - Create form
- `POST /admin/marketing-assets` - Store new asset
- `GET /admin/marketing-assets/{id}/edit` - Edit form
- `PUT /admin/marketing-assets/{id}` - Update asset
- `DELETE /admin/marketing-assets/{id}` - Delete asset
- `POST /admin/marketing-assets/{id}/duplicate` - Duplicate asset

### Attendee Routes
- `GET /attendee/marketing-hub` - Browse materials
- `GET /attendee/marketing-hub/{id}/download` - Download asset
- `POST /attendee/marketing-hub/{id}/share` - Track share

## Usage

### For Administrators

**Creating Marketing Assets:**
1. Go to Admin → Marketing Assets
2. Click "Create Asset"
3. Fill in details:
   - Title and description
   - Select asset type
   - Upload image (optional)
   - Add caption text
   - Add hashtags (comma-separated)
   - Select social platforms
   - Set featured/active status
4. Save

**Managing Assets:**
- Edit: Update any asset details
- Duplicate: Create copy for variations
- Delete: Remove asset
- Track: View download and share counts
- Filter: By type or category

### For Attendees

**Using Marketing Materials:**
1. Go to "Marketing Hub" from navigation
2. Browse available materials
3. Click "View Details" on any asset
4. Actions available:
   - Download image
   - Copy caption text
   - Copy hashtags
   - Share (tracks usage)

**Sharing on Social Media:**
1. Download the image
2. Copy the caption
3. Copy the hashtags
4. Post on your preferred platform
5. System tracks your share

## File Storage
- Marketing assets: `storage/app/public/marketing-assets/`
- Supported formats: JPEG, PNG, JPG, GIF
- Max size: 10MB per file

## Best Practices

### For Admins
1. Create assets for different event phases
2. Use featured status for priority materials
3. Provide multiple size options
4. Include platform-specific versions
5. Update hashtags regularly
6. Monitor download/share stats

### Content Guidelines
1. **Posters**: High-resolution, event branding
2. **Social Posts**: Platform-optimized dimensions
3. **Hashtags**: Mix of branded and trending
4. **Captions**: Multiple length options
5. **Stories**: Vertical format, engaging

## Statistics Tracking
- Download count per asset
- Share count per asset
- Popular asset types
- Usage trends
- Platform preferences

## Future Enhancements
- Analytics dashboard
- A/B testing for captions
- Scheduled asset releases
- User-generated content submission
- Social media direct posting
- QR code generation
- Video asset support
- Template customization
- Bulk operations
- Export reports
- Email distribution
- Mobile app integration

## Integration Points
- Event Wall: Share assets directly
- Attendee Profiles: Personal sharing stats
- Email Campaigns: Include assets
- Registration: Welcome kit materials
- Post-event: Thank you materials
