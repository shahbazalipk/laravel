# Event Wall Feature Guide

## Overview
The Event Wall is a social networking feature that allows attendees to share their event experiences, connect with other participants, and engage through posts, comments, and likes - similar to Facebook's wall functionality.

## Features

### 1. Post Creation
- **Text Posts**: Share thoughts, experiences, and updates
- **Image Posts**: Upload up to 5 images per post (JPEG, PNG, JPG, GIF, max 5MB each)
- **Link Sharing**: Share external links with automatic title extraction
- **Hashtags**: Use #hashtags to categorize posts and make them discoverable
- **Mentions**: Tag other attendees using @mentions
- **Emojis**: Quick emoji picker with 10 popular emojis + random emoji button

### 2. Post Interactions
- **Like System**: Like posts with reaction tracking
- **Comments**: Add comments to posts with nested reply support
- **Comment Likes**: Like individual comments
- **Image Viewing**: Click images to view in full-screen modal
- **Delete**: Users can delete their own posts and comments

### 3. Content Discovery
- **Trending Hashtags**: Sidebar shows top 10 trending hashtags with post counts
- **Filter by Type**: Filter posts by All, Photos, or Links
- **Filter by Hashtag**: Click any hashtag to see related posts
- **Chronological Feed**: Posts ordered by pinned status then creation date

### 4. User Experience
- **Real-time Updates**: AJAX-based interactions without page reloads
- **Image Preview**: Preview images before posting
- **Responsive Design**: Works on desktop and mobile devices
- **User Avatars**: Displays user initials in colored circles
- **Timestamps**: Human-readable timestamps (e.g., "2 hours ago")

## Database Structure

### Tables Created
1. **event_wall_posts**
   - Stores all posts with content, images, links, hashtags, mentions
   - Tracks likes_count and comments_count
   - Supports pinning and moderation (is_approved)

2. **event_wall_comments**
   - Stores comments and nested replies
   - Supports image attachments
   - Tracks likes_count

3. **event_wall_likes**
   - Polymorphic relationship for liking posts and comments
   - Supports different reaction types (like, love, celebrate, etc.)
   - Prevents duplicate likes with unique constraint

## Routes

### Attendee Routes
- `GET /attendee/event-wall` - View event wall feed
- `POST /attendee/event-wall` - Create new post
- `DELETE /attendee/event-wall/{post}` - Delete own post
- `POST /attendee/event-wall/{post}/like` - Toggle like on post
- `POST /attendee/event-wall/{post}/comments` - Add comment to post
- `DELETE /attendee/event-wall/comments/{comment}` - Delete own comment
- `POST /attendee/event-wall/comments/{comment}/like` - Toggle like on comment

## Models

### EventWallPost
- Relationships: event, registration, comments, likes
- Scopes: active, pinned, recent
- Methods: isLikedBy($registrationId)

### EventWallComment
- Relationships: event, post, registration, parent, replies, likes
- Scopes: active
- Methods: isLikedBy($registrationId)

### EventWallLike
- Polymorphic relationship to posts and comments
- Tracks reaction type

## Usage

### For Attendees
1. Navigate to "Event Wall" from the main navigation
2. Create posts using the composer at the top
3. Add images by clicking the Photo button
4. Use emojis from the quick picker or random button
5. Add hashtags by typing # followed by text
6. Like and comment on other attendees' posts
7. Click hashtags to filter posts by topic
8. View trending hashtags in the sidebar

### For Administrators
- Posts are auto-approved by default (is_approved = true)
- Can implement moderation by setting is_approved = false
- Can pin important posts (is_pinned = true)
- Access post data through EventWallPost model

## File Storage
- Post images: `storage/app/public/event-wall/`
- Comment images: `storage/app/public/event-wall/comments/`

## Security Features
- Users can only delete their own posts and comments
- CSRF protection on all forms
- File upload validation (type, size)
- Content length limits (5000 chars for posts, 1000 for comments)
- SQL injection protection through Eloquent ORM

## Future Enhancements
- Real-time notifications for likes and comments
- Advanced link preview with Open Graph metadata
- Video upload support
- GIF integration
- Post reporting and moderation queue
- Analytics dashboard for engagement metrics
- Email notifications for mentions
- Search functionality
- Post editing capability
- Reaction types (love, celebrate, support, etc.)

## Technical Notes
- Uses Laravel's polymorphic relationships for likes
- Implements soft deletes for posts and comments
- Hashtag extraction using regex pattern matching
- Image preview using FileReader API
- AJAX requests for seamless interactions
- Pagination for post feed (10 posts per page)
