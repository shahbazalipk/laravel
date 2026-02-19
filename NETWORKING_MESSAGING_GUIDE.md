# Networking & Messaging System Guide

## Overview
A complete professional networking and real-time messaging system that allows attendees to connect with each other, manage their network, and communicate through private messages.

## Features

### 1. Connection Management
- **Send Connection Requests**: Send requests to other attendees with optional message
- **Accept/Reject Requests**: Manage incoming connection requests
- **View Connections**: See all accepted connections in one place
- **Remove Connections**: Ability to remove connections
- **Connection Status**: Track pending, accepted, and rejected requests

### 2. Messaging System
- **Real-time Chat**: Send and receive messages with connected attendees
- **Message History**: View complete conversation history
- **Unread Indicators**: See unread message counts
- **Auto-refresh**: Messages update every 3 seconds
- **File Attachments**: Support for images and documents (up to 5MB)
- **Read Receipts**: Track when messages are read

### 3. User Interface
- **Connections Page**: Three tabs (Connections, Requests, Sent)
- **Messages List**: Inbox view with last message preview
- **Chat Interface**: Full-screen chat with message history
- **Profile Integration**: Connect button on attendee profiles
- **Navigation**: Easy access from main menu

## Database Structure

### Tables Created

1. **attendee_connections**
   - Manages connection requests and relationships
   - Tracks status (pending, accepted, rejected, blocked)
   - Stores optional message with request
   - Records acceptance/rejection timestamps

2. **attendee_messages**
   - Stores all messages between connected users
   - Supports file attachments
   - Tracks read status and timestamps
   - Soft delete for sender/receiver

## Routes

### Connection Routes
- `GET /attendee/connections` - View connections page
- `POST /attendee/connections/send` - Send connection request
- `POST /attendee/connections/{id}/accept` - Accept request
- `POST /attendee/connections/{id}/reject` - Reject request
- `DELETE /attendee/connections/{id}/cancel` - Cancel sent request
- `DELETE /attendee/connections/{id}` - Remove connection

### Messaging Routes
- `GET /attendee/messages` - Messages inbox
- `GET /attendee/messages/{attendee}` - Chat with specific attendee
- `POST /attendee/messages/{attendee}/send` - Send message
- `GET /attendee/messages/{attendee}/get` - Get messages (AJAX)
- `GET /attendee/messages-unread-count` - Get unread count

## Models

### AttendeeConnection
- Relationships: sender, receiver
- Scopes: pending, accepted, rejected, blocked
- Static methods: areConnected(), getConnectionStatus()

### AttendeeMessage
- Relationships: sender, receiver
- Scopes: unread, between
- Methods: markAsRead()

## Usage

### For Attendees

**Connecting with Others:**
1. Browse attendees list
2. Click on an attendee profile
3. Click "Connect" button
4. Optionally add a message with request
5. Wait for acceptance

**Managing Connections:**
1. Go to "Connections" from navigation
2. View three tabs:
   - Connections: All accepted connections
   - Requests: Incoming requests to accept/reject
   - Sent: Outgoing pending requests

**Messaging:**
1. Go to "Messages" from navigation
2. Click on a connection to open chat
3. Type message and press Send
4. Messages auto-refresh every 3 seconds
5. See unread indicators on inbox

### Connection States

1. **No Connection**: Show "Connect" button
2. **Pending (Sent)**: Show "Request Pending" button (can cancel)
3. **Pending (Received)**: Show "Accept" and "Reject" buttons
4. **Accepted**: Show "Send Message" button
5. **Rejected**: No button shown
6. **Blocked**: No interaction allowed

## Security Features

- Users can only message connected attendees
- Connection requests require mutual acceptance
- Users can only delete their own messages
- CSRF protection on all forms
- File upload validation (type, size)
- Message length limits (2000 characters)
- Unique constraint prevents duplicate connections

## Technical Implementation

### Real-time Updates
- JavaScript polling every 3 seconds for new messages
- AJAX-based message sending without page reload
- Auto-scroll to latest message
- Unread count updates

### Connection Logic
- Bidirectional relationship (sender/receiver)
- Status tracking (pending → accepted/rejected)
- Prevents duplicate requests
- Prevents self-connection

### Message Threading
- Messages ordered chronologically
- Grouped by conversation
- Last message preview in inbox
- Unread count per conversation

## File Storage
- Message attachments: `storage/app/public/messages/`
- Supported types: JPEG, PNG, JPG, GIF, PDF, DOC, DOCX
- Max size: 5MB per file

## Future Enhancements
- WebSocket integration for true real-time messaging
- Typing indicators
- Message reactions (like, love, etc.)
- Group messaging
- Voice/video calls
- Message search functionality
- Block/report users
- Online status indicators
- Push notifications
- Message encryption
- File preview in chat
- Emoji picker
- Message editing/deletion
- Conversation archiving

## Performance Considerations
- Indexed database queries for fast lookups
- Pagination for large message histories
- Efficient connection status checks
- Optimized unread count queries
- Lazy loading of message attachments

## Privacy Settings (Future)
- Control who can send connection requests
- Hide profile from search
- Block specific users
- Message privacy settings
- Connection visibility settings
